# Native Rebuild Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the ported WordPress frontend with the approved hand-owned design (mockup v2): one token-driven stylesheet + small vanilla JS + Hugo layouts rendering 6 statically-generated role-preset pages from `data/`, then delete Elementor/RyanCV/jQuery.

**Architecture:** `mockups/v2.html` is the approved look/feel source — CSS/JS/content packs are EXTRACTED from it, not re-invented. Hugo renders `/` + `/for/<role>/` from one parameterized layout + `data/presets.yaml`. Content stays in `data/*.yaml` (content-lane contract). Switchover deletes the entire vendor layer in the same PR.

**Tech Stack:** Hugo 0.163.3 extended · hand CSS (tokens) · vanilla JS · IBM Plex woff2 self-hosted (fonttools subset) · existing verify scripts (`scripts/verify/check_404.py`).

## Global Constraints

- Worktree: `/home/mohamed/Projects/skylight74-native` (branch `native-rebuild`). Never touch the main tree.
- **No `Co-Authored-By: Claude` trailer on any commit.**
- Spec: `docs/superpowers/specs/2026-07-12-native-rebuild-design.md`. Mockup wins look/feel; spec wins architecture.
- Content: ONLY attested values from `data/*.yaml` + the mockup's content packs. Never invent facts. New-claim = STOP, ask Mohamed.
- Perf budget: `static/css/site.css` + `static/js/site.js` combined **<40KB** (uncompressed); fonts ≤ ~120KB total.
- Canvas pattern MANDATORY: offscreen prerender · per-frame = 1 drawImage + ≤~120 glow dots · colors read once per render · zero-size guards (`if(!W||!H)return`) · `visibilitychange` pause · rAF-coalesced pointermove.
- All motion behind `@media (prefers-reduced-motion: reduce)`.
- No visible scrollbar (`scrollbar-width:none` + `::-webkit-scrollbar{display:none}`); scrollspy is the position cue.
- Preset keys (exact): `core devsecops platform aisec research lead`. Skin keys: `oxo mono ink graphite latex2`. LEAD uses skin `ink` + `serif: true`.
- Formspree endpoint exact: `https://formspree.io/f/mwvdzgqv`. No phone anywhere.
- Local servers: hugo dev on 1313; mockup reference stays on 8084 (don't kill it).
- Every task ends with its gate green; red gate = STOP, fix or report — never commit red.
- Lighthouse note: no Chrome on this machine → pre-merge perf gate = byte budget + manual; **PSI (PageSpeed Insights API) ≥95 verified post-deploy** on the live URL (Task 10).

### Shared gate commands

```bash
cd /home/mohamed/Projects/skylight74-native
hugo --quiet 2>&1 | tail -3                    # must succeed, no warnings about missing layouts
python3 -m http.server 1414 --bind 127.0.0.1 --directory public & SRV=$!
sleep 1
python3 scripts/verify/check_404.py http://127.0.0.1:1414 /blog/ /search/ /for/devsecops/ /for/platform/ /for/ai-security/ /for/research/ /for/lead/ ; echo "404 exit $?"
kill $SRV
```

Byte budget check:
```bash
wc -c static/css/site.css static/js/site.js | tail -1   # total < 40960
```

---

### Task 1: Self-hosted IBM Plex woff2

**Files:**
- Create: `static/fonts/{plex-mono-400,plex-mono-500,plex-mono-600,plex-sans-400,plex-sans-600,plex-serif-600,plex-serif-500i}.woff2`
- Create: `assets-notes/fonts.md` (provenance + license note)

**Interfaces:**
- Produces: exact font file names above; consumed by Task 2's `@font-face` block verbatim.

- [ ] **Step 1: Verify tooling + sources**

```bash
python3 -c "import fontTools; print(fontTools.version)" || pip install --user fonttools brotli
ls /usr/share/fonts/TTF/IBMPlexMono-{Regular,Medium,SemiBold}.ttf /usr/share/fonts/TTF/IBMPlexSans-{Regular,SemiBold}.ttf /usr/share/fonts/TTF/IBMPlexSerif-{SemiBold,MediumItalic}.ttf
```
Expected: fontTools version prints (install if missing); all 7 TTFs exist. If a specific weight file is missing, list `fc-list | grep -i plex` and substitute the nearest weight, noting it in `assets-notes/fonts.md`.

- [ ] **Step 2: Subset to woff2 (latin + latin-ext)**

```bash
cd /home/mohamed/Projects/skylight74-native && mkdir -p static/fonts
sub(){ python3 -m fontTools.subset "$1" --output-file="static/fonts/$2.woff2" --flavor=woff2 \
  --unicodes="U+0000-00FF,U+0100-017F,U+0180-024F,U+2013,U+2014,U+2018-201F,U+2022,U+2026,U+00D7,U+2192,U+2190,U+25CF,U+00B7" \
  --layout-features="kern,liga" --no-hinting; }
sub /usr/share/fonts/TTF/IBMPlexMono-Regular.ttf   plex-mono-400
sub /usr/share/fonts/TTF/IBMPlexMono-Medium.ttf    plex-mono-500
sub /usr/share/fonts/TTF/IBMPlexMono-SemiBold.ttf  plex-mono-600
sub /usr/share/fonts/TTF/IBMPlexSans-Regular.ttf   plex-sans-400
sub /usr/share/fonts/TTF/IBMPlexSans-SemiBold.ttf  plex-sans-600
sub /usr/share/fonts/TTF/IBMPlexSerif-SemiBold.ttf plex-serif-600
sub /usr/share/fonts/TTF/IBMPlexSerif-MediumItalic.ttf plex-serif-500i
ls -l static/fonts/ && du -ch static/fonts/*.woff2 | tail -1
```
Expected: 7 files, total ≤ ~120KB (typically ~12-18KB each). Turkish chars (İ ş ğ ç ö ü) covered by latin-ext — verify: `python3 -m fontTools.ttx -q -t cmap -o - static/fonts/plex-sans-400.woff2 | grep -c 0x131` ≥1 (dotless i present).

- [ ] **Step 3: Provenance note**

Write `assets-notes/fonts.md`: source paths, subset command, OFL-1.1 license statement (IBM Plex is SIL OFL), date.

- [ ] **Step 4: Commit**

```bash
git add static/fonts assets-notes/fonts.md
git commit -m "assets: self-host IBM Plex woff2 subsets (mono 400/500/600, sans 400/600, serif 600/500i)"
```

---

### Task 2: Extract `site.css` + `site.js` from mockup v2

**Files:**
- Create: `static/css/site.css` (sections, in order: `/* @font-face */ /* tokens */ /* skins */ /* action-semantics */ /* base */ /* strip */ /* sections */ /* components */ /* editorial-voice */ /* print */ /* reduced-motion */`)
- Create: `static/js/site.js`
- Create: `mockups/harness.html` (throwaway test page; NOT part of the site)

**Interfaces:**
- Consumes: Task 1 font filenames.
- Produces: `site.css` class contract = exactly the mockup's classes (`.strip .monogram .who .cv`, `.card .in .sec-label`, `.pic .avail .prompt .kicker .typedline .cursor .sum .kv .cta .btn .socials .hero-item`, `.stats .stat .num .lbl`, `.row .when .role .org .early`, `.projs .proj .yr`, `.skill-group .skill-label .tags .tag .hot`, `.pub .honors .honor .leadship`, `.svcs .svc .no`, `.post .d .pipe`, `.contact-grid .field .cinfo .live .foot .ok`, `.reveal .vis`); `site.js` exposes `initSite(cfg)` where `cfg={roles:[...], reduced:bool}`.

- [ ] **Step 1: Extract the mockup style block**

```bash
cd /home/mohamed/Projects/skylight74-native
python3 - <<'PY'
import re
s=open('mockups/v2.html').read()
css=re.search(r'<style>\n(.*?)\n</style>',s,flags=re.S).group(1)
open('/tmp/mockup.css','w').write(css)
print(len(css),"bytes of mockup CSS extracted")
PY
```

- [ ] **Step 2: Build `static/css/site.css` from the extraction**

Assemble in this exact order, editing as noted:
1. **@font-face block (NEW — write verbatim):**
```css
/* IBM Plex — self-hosted subsets (OFL-1.1) */
@font-face{font-family:"IBM Plex Mono";font-weight:400;font-display:swap;src:url("/fonts/plex-mono-400.woff2") format("woff2")}
@font-face{font-family:"IBM Plex Mono";font-weight:500;font-display:swap;src:url("/fonts/plex-mono-500.woff2") format("woff2")}
@font-face{font-family:"IBM Plex Mono";font-weight:600 700;font-display:swap;src:url("/fonts/plex-mono-600.woff2") format("woff2")}
@font-face{font-family:"IBM Plex Sans";font-weight:400;font-display:swap;src:url("/fonts/plex-sans-400.woff2") format("woff2")}
@font-face{font-family:"IBM Plex Sans";font-weight:600;font-display:swap;src:url("/fonts/plex-sans-600.woff2") format("woff2")}
@font-face{font-family:"IBM Plex Serif";font-weight:500 600;font-display:swap;src:url("/fonts/plex-serif-600.woff2") format("woff2")}
@font-face{font-family:"IBM Plex Serif";font-weight:500;font-style:italic;font-display:swap;src:url("/fonts/plex-serif-500i.woff2") format("woff2")}
```
2. From `/tmp/mockup.css` copy: `:root` tokens; the FIVE production skin blocks ONLY (`[data-skin="oxo"]` incl. its dual-accent/action rules, `mono`, `ink`, `graphite`, `latex2`) — DELETE dead-skin blocks (`charcoal steel paper gruv nord olive graphite2 graphite3 latex latex3` and the old per-skin×role accent-matrix lines that reference deleted skins); the generic ACTION-SEMANTICS block; base/body/strip/cards/sections/components blocks; the LEAD + research editorial-voice blocks; skills per-role order rules; stat per-role order rules.
3. **Preset accent bindings — keep ONLY (verbatim from mockup):**
```css
html[data-role="core"][data-skin="oxo"],html[data-skin="oxo"]{/* teal defaults already in skin block */}
html[data-skin="ink"][data-role="platform"]{--accent:#2458ff;--accent-dim:#2458ff1e}
html[data-skin="ink"][data-role="lead"]{--accent:#c22f4b;--accent-dim:#c22f4b24;--head:var(--serif)}
```
(oxo/graphite/latex2/mono presets use their skin-block accents as-is.)
4. **Role-preset switcher styles: DELETE** (`.roles` block) — production has no switcher.
5. **Print block (NEW — write verbatim):**
```css
@media print{
  html{scroll-snap-type:none}
  :root,html[data-skin]{--bg:#fff;--bg-2:#fff;--surface:#fff;--surface-2:#f4f4f4;--hairline:#999;
    --fg:#111;--fg-dim:#444;--fg-faint:#4441;--accent:#111;--accent-dim:#1111;--act:#111;--act-dim:#1111;--act-glow:transparent;--accent-ink:#fff}
  #grid,.strip .cv,.themes,.cursor,.avail .dot{display:none!important}
  .card{min-height:auto;padding:24pt 0;page-break-inside:avoid}
  .strip{position:static;backdrop-filter:none}
  a[href^="http"]::after{content:" (" attr(href) ")";font-size:9pt;color:#444}
}
```
6. Reduced-motion + scrollbar-hidden rules (already in mockup CSS — keep).

- [ ] **Step 3: Build `static/js/site.js`**

Extract from the mockup `<script>` and REWORK — production pages are static per-preset (no role switching). Write this structure (function bodies = copy from mockup, minus all `applyRole`/`roleSkin`/`kickers/sums/openTo/stat*`/`labelMap` maps and the switcher listeners):

```js
/* site.js — canvas grid, typed roles, scrollspy, reveal, count-up, contact form.
   All config comes from data-* attributes / JSON script tag rendered by Hugo. */
(function(){
"use strict";
const rm = matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---- typed roles (list injected by template) ---- */
function initTyped(){
  const el=document.getElementById('typed'); if(!el) return;
  const cfg=document.getElementById('site-cfg');
  const roles=cfg?JSON.parse(cfg.textContent).roles:[];
  if(!roles.length) return;
  const staticLine=el.dataset.static;               // editorial presets: fixed tagline
  if(staticLine){el.textContent=staticLine;return}
  if(rm){el.textContent=roles[0];return}
  let r=+(el.dataset.pin||0),i=0,del=false;
  (function tick(){const w=roles[r];el.textContent=w.slice(0,i);
    if(!del&&i<w.length){i++;setTimeout(tick,55)}
    else if(!del){del=true;setTimeout(tick,1500)}
    else if(i>0){i--;setTimeout(tick,26)}
    else{del=false;r=(r+1)%roles.length;setTimeout(tick,280)}})();
}

/* ---- canvas dot grid (MANDATORY perf pattern — copy body verbatim from mockup:
   renderStatic offscreen + draw composite + glow radius 180 + guards + visibilitychange) ---- */

/* ---- scrollspy + crumb + reveal (copy from mockup, unchanged) ---- */

/* ---- stat count-up (copy from mockup, unchanged) ---- */

/* ---- contact form (port from old static/js/contact-form.js: fetch POST,
   inline success/error, double-submit guard) ---- */

document.addEventListener('DOMContentLoaded',()=>{initTyped();/* initCanvas();initSpy();initCount();initForm(); */});
})();
```
Replace the commented init calls with the real extracted functions. NO other libraries.

- [ ] **Step 4: Harness test**

Write `mockups/harness.html`: minimal page linking `../static/css/site.css` + `../static/js/site.js`, `<html data-skin="oxo" data-role="core">`, containing one `.strip`, one `.card` with `.stats` (one `.stat .num[data-count="98.11"][data-post="%"]`), `#typed` + `<script id="site-cfg" type="application/json">{"roles":["A","B"]}</script>`, canvas `#grid`, a `.reveal` block. Serve mockups on 8084 (already running) and screenshot:
```bash
# firefox shot (absolute out path), expect: styled strip/card, stat text, no console errors
```
Then byte gate: `wc -c static/css/site.css static/js/site.js | tail -1` < 40960.

- [ ] **Step 5: Commit**

```bash
git add static/css/site.css static/js/site.js mockups/harness.html
git commit -m "feat: token stylesheet + vanilla site.js extracted from approved mockup"
```

---

### Task 3: Preset content packs — `data/presets.yaml` + profile fields

**Files:**
- Create: `data/presets.yaml`
- Modify: `data/profile.yaml` (append `open_to`, `roles`, `summary_html` — do NOT touch existing keys)

**Interfaces:**
- Produces: schema below; consumed by Task 4's layouts via `site.Data.presets.presets.<key>` and `site.Data.presets.stats.<id>`.

- [ ] **Step 1: Write `data/presets.yaml` (verbatim — values are the approved mockup packs)**

```yaml
# Role-preset content packs. Values approved in mockup v2 (2026-07-12 spec).
# Content lane may tune wording here; keys/structure belong to the rebuild.
stats:
  eps:     { num: "~400k/s", label: "security events — Go detection engine, 80+ sources", count: "400", pre: "~", post: "k/s" }
  acc:     { num: "98.11%",  label: "IDS accuracy — first-author, AINA 2024 (Springer)", count: "98.11", post: "%" }
  mvp:     { num: "3 mo",    label: "SIEM/SOAR MVP shipped vs 6–12 mo company norm", count: "3", post: " mo" }
  team:    { num: "6 eng",   label: "DevSecOps team formed, led, and trained", count: "6", post: " eng" }
  users:   { num: "270k",    label: "user platform rebuilt as 10 microservices on AWS", count: "270", post: "k" }
  papers:  { num: "2",       label: "peer-reviewed papers — AINA (Springer) · MedPower, TÜBİTAK-funded", count: "2" }
  trained: { num: "4+",      label: "engineers & ASELSAN partners trained", count: "4", post: "+" }
presets:
  core:
    url: "/"
    skin: oxo
    voice: terminal
    kicker: "Security & Infrastructure Engineering"
    summary_html: 'I work where security meets systems: formed and led a <b>six-engineer DevSecOps team</b>, sole-built a <b>real-time Go detection engine</b>, and first-authored a peer-reviewed intrusion-detection paper (<b>AINA 2024, Springer</b>).'
    open_to: "DevSecOps · Platform/Backend (Go) · Security · Tech Lead"
    stats: [eps, acc, mvp, team]
    typed_pin: 0
  devsecops:
    url: "/for/devsecops/"
    skin: mono
    voice: terminal
    kicker: "DevSecOps — pipelines, detection, response"
    summary_html: 'DevSecOps engineer: formed and led a <b>six-engineer team</b> that shipped a SIEM/SOAR platform in <b>3 months</b>; builds secure <b>CI/CD, Kubernetes, and Terraform</b> pipelines; sole-built a real-time Go detection engine at <b>~400k events/sec</b> (SAST/DAST/SCA · ELK/Wazuh/Sentinel · AWS).'
    open_to: "DevSecOps · Security Engineering · Platform/Cloud"
    stats: [mvp, acc, eps, team]
    typed_pin: 0
  platform:
    url: "/for/platform/"
    skin: ink
    voice: terminal
    kicker: "Platform & Backend — Go, streaming, scale"
    summary_html: 'Backend engineer for <b>high-throughput Go systems</b>: sole-built a Kafka → ClickHouse stream engine sustaining <b>~400k events/sec</b> (Redis, OpenTelemetry), rebuilt a <b>270k-user platform into 10 microservices</b> on AWS — event-driven architecture, gRPC, distributed systems.'
    open_to: "Platform / Backend (Go) · SRE · DevSecOps"
    stats: [eps, users, acc, team]
    typed_pin: 2
  aisec:
    url: "/for/ai-security/"
    skin: graphite
    voice: terminal
    kicker: "AI × Security — detection models in production"
    summary_html: 'Detection engineer bridging <b>security and applied ML</b>: 98.11% IDS accuracy (<b>one-class SVM, AINA 2024</b>), the OSSArch SIEM/SOAR stack, and a <b>~400k events/sec Go engine</b> with detection-as-code anomaly rules — MLOps on <b>GPU Kubernetes</b>.'
    open_to: "AI Security / Detection Engineering · Security-ML · DevSecOps"
    stats: [acc, eps, mvp, team]
    typed_pin: 5
  research:
    url: "/for/research/"
    skin: latex2
    voice: editorial
    kicker: "Security-ML Research — detection, published"
    summary_html: 'ML research engineer, first author at <b>AINA 2024 (Springer)</b> — 98.11% intrusion-detection accuracy (<b>one-class SVM; LSTM sequence models in PyTorch</b>); built the end-to-end <b>GPU training pipeline on Kubernetes</b> for the DIONA IDS (TÜBİTAK #120E537, <b>Koç Sistem pilot</b>) and a container-specific vulnerability dataset.'
    open_to: "Security-ML Research · Detection Engineering · Applied ML R&D"
    stats: [acc, eps, mvp, papers]
    typed_pin: 5
  lead:
    url: "/for/lead/"
    skin: ink
    voice: editorial
    serif: true
    photo: "/media/lead-photo.jpg"
    tagline: "Engineering leader — security, platform, delivery."
    kicker: "Engineering Leadership — teams that ship"
    summary_html: 'I build teams that ship: <b>formed and led six engineers</b> to deliver an MVP SIEM/SOAR in <b>3 months vs the 6–12 month norm</b>, <b>trained 4 engineers and ASELSAN partners</b>, taught at university, and led a <b>200+ member organization</b>.'
    open_to: "Tech Lead / Engineering Lead · DevSecOps · Platform · Security"
    stats: [team, mvp, trained, eps]
```

- [ ] **Step 2: Append to `data/profile.yaml`**

```yaml
# --- added by rebuild lane 2026-07-12 (content lane owns values hereafter) ---
open_to: "DevSecOps · Platform/Backend (Go) · Security · Tech Lead"
roles:
  - "DevSecOps Engineer"
  - "Security Engineer"
  - "Backend Developer (Go)"
  - "Site Reliability Engineer"
  - "Tech Lead"
  - "Security-ML Researcher"
availability: "Available — freelance / full-time"
location_line: "Ankara — Istanbul / relocation / remote"
languages_line: "Arabic (native) · English (C1) · Turkish (B2)"
```

- [ ] **Step 3: Copy LEAD photo into media**

```bash
cp mockups/lead-photo.jpg static/media/lead-photo.jpg
```

- [ ] **Step 4: Gate + commit**

```bash
hugo --quiet 2>&1 | tail -2   # parses (data files are valid YAML)
git add data/presets.yaml data/profile.yaml static/media/lead-photo.jpg
git commit -m "data: role-preset content packs + profile hero fields (approved mockup values)"
```

---

### Task 4: Layout skeleton — baseof, head/SEO, strip, hero, preset pages

**Files:**
- Create: `layouts/_default/baseof.html` (REPLACES old one — git will show modify), `layouts/partials/new/head.html`, `layouts/partials/new/strip.html`, `layouts/partials/new/hero.html`, `layouts/index.html` (replace), `layouts/for/single.html`, `content/for/{devsecops,platform,ai-security,research,lead}.md`
- Note: build new partials under `layouts/partials/new/` so the old site keeps building until Task 8 switchover.

**Interfaces:**
- Consumes: `site.Data.presets` (Task 3), `site.Data.profile/contact`, `site.css`/`site.js` (Task 2).
- Produces: `.Param "preset"` resolution pattern — every page resolves `$p := index site.Data.presets.presets (.Params.preset | default "core")`; partials receive a dict `(dict "page" . "p" $p)`. Section partials (Tasks 5-6) are named `layouts/partials/new/section-<name>.html` and included by `layouts/index.html` and `layouts/for/single.html` identically.

- [ ] **Step 1: `content/for/*.md` (5 files, front matter only)**

```markdown
---
title: "Mohamed Aly Amin — DevSecOps"
preset: "devsecops"
url: "/for/devsecops/"
build: { list: never }
---
```
(один per preset: platform / ai-security→`preset: "aisec"` / research / lead — set `title` suffix to the preset kicker's first clause and `url` per presets.yaml.)

- [ ] **Step 2: `layouts/partials/new/head.html`**

```html
{{ $p := .p }}{{ $pg := .page }}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ if $pg.IsHome }}Mohamed Aly Amin — Security & Infrastructure Engineer{{ else }}{{ $pg.Title }}{{ end }}</title>
<meta name="description" content="{{ $p.summary_html | plainify | truncate 155 }}">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="canonical" href="{{ site.BaseURL }}">
<meta property="og:type" content="profile">
<meta property="og:title" content="Mohamed Aly Amin — {{ $p.kicker }}">
<meta property="og:description" content="{{ $p.summary_html | plainify | truncate 200 }}">
<meta property="og:image" content="{{ site.BaseURL }}media/profile-pic.jpg">
<meta property="og:url" content="{{ $pg.Permalink }}">
<meta name="twitter:card" content="summary">
<link rel="alternate" type="application/rss+xml" title="Mohamed Aly Amin » Feed" href="/index.xml">
<link rel="stylesheet" href="/css/site.css">
<script defer src="/js/site.js"></script>
{{ if $pg.IsHome }}<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Person","name":"Mohamed Aly Amin",
"jobTitle":"Security & Infrastructure Engineer","url":"{{ site.BaseURL }}",
"email":"mailto:{{ site.Data.contact.email }}","address":{"@type":"PostalAddress","addressLocality":"Ankara","addressCountry":"TR"},
"alumniOf":"Middle East Technical University","sameAs":["https://www.linkedin.com/in/mohamedalyamin","https://github.com/skylight74"]}
</script>{{ end }}
```

- [ ] **Step 3: `layouts/_default/baseof.html`**

```html
<!DOCTYPE html>
<html lang="en-US" data-skin="{{ (index site.Data.presets.presets (.Params.preset | default "core")).skin }}" data-role="{{ .Params.preset | default "core" }}">
<head>{{ partial "new/head.html" (dict "page" . "p" (index site.Data.presets.presets (.Params.preset | default "core"))) }}</head>
<body>
<canvas id="grid" aria-hidden="true"></canvas>
{{ partial "new/strip.html" (dict "page" . "p" (index site.Data.presets.presets (.Params.preset | default "core"))) }}
{{ block "main" . }}{{ end }}
</body>
</html>
```

- [ ] **Step 4: `layouts/partials/new/strip.html`** — copy the mockup `<header class="strip">` markup, replacing: monogram stays `M` placeholder; nav anchors (`#hero #impact #experience #projects #skills #research #contact` + `/blog/`); `↓ CV` → `<a class="cv" href="/media/Mohamed_Aly_Amin_CV.pdf" download>↓ CV</a>`; NO `.roles`/`.themes` switchers.

- [ ] **Step 5: `layouts/partials/new/hero.html`** — copy mockup hero section, substituting data:
photo `{{ $p.photo | default "/media/profile-pic.jpg" }}`; availability `{{ site.Data.profile.availability }}`; prompt shown only `{{ if eq $p.voice "terminal" }}`; kicker `{{ $p.kicker }}`; name from `site.Data.profile.name`; typed line: `<span class="t" id="typed" data-pin="{{ $p.typed_pin }}"{{ with $p.tagline }} data-static="{{ . }}"{{ end }}></span>` + cursor (terminal only); summary `{{ $p.summary_html | safeHTML }}`; kv rows from profile `open_to override {{ $p.open_to }}`, `location_line`, `languages_line`; CTA CV+Contact; socials LinkedIn/GitHub (from profile.socials, filter icons `linkedin|github`) then Telegram; site-cfg JSON: `<script id="site-cfg" type="application/json">{{ dict "roles" site.Data.profile.roles | jsonify | safeJS }}</script>`.

- [ ] **Step 6: `layouts/index.html` + `layouts/for/single.html`** — both:

```html
{{ define "main" }}
{{ $p := index site.Data.presets.presets (.Params.preset | default "core") }}
{{ $ctx := dict "page" . "p" $p }}
<section class="card" id="hero">{{ partial "new/hero.html" $ctx }}</section>
<!-- Tasks 5-6 append: impact experience projects skills research services writing contact -->
{{ end }}
```

- [ ] **Step 7: Gate** — `hugo --quiet`; `ls public/index.html public/for/devsecops/index.html public/for/lead/index.html`; grep checks: `grep -o 'data-skin="[a-z0-9]*"' public/for/lead/index.html` = `ink`; `grep -c 'Engineering Leadership' public/for/lead/index.html` ≥1; shared 404 gate SKIPPED until sections exist (nav anchors dangle) — instead `python3 scripts/verify/check_404.py http://127.0.0.1:1414` home only. Screenshot home hero vs mockup hero side-by-side (firefox, absolute paths) — visual parity spot-check.

- [ ] **Step 8: Commit** — `git add layouts content/for data static; git commit -m "feat: layout skeleton — baseof/head/strip/hero + 6 preset pages"`

---

### Task 5: Section partials A — impact, experience, projects, skills

**Files:**
- Create: `layouts/partials/new/section-impact.html`, `section-experience.html`, `section-projects.html`, `section-skills.html`
- Modify: `layouts/index.html` + `layouts/for/single.html` (append the four `<section>` includes with ids `impact experience projects skills` and the mockup's `sec-label` texts)

**Interfaces:**
- Consumes: `$ctx`; `site.Data.{presets,resume,projects,open_source,skills}`.
- Produces: section ids used by scrollspy/nav.

- [ ] **Step 1: impact** — stats loop (THE preset mechanism):

```html
{{ $p := .p }}
<div class="in reveal">
  <div class="sec-label"><b>01</b>impact --measured</div>
  <h2>Numbers that survived review.</h2>
  <div class="stats">
    {{ range $p.stats }}{{ $s := index site.Data.presets.stats . }}
    <div class="stat"><div class="num"{{ with $s.count }} data-count="{{ . }}"{{ end }}{{ with $s.pre }} data-pre="{{ . }}"{{ end }}{{ with $s.post }} data-post="{{ . }}"{{ end }}>{{ $s.num }}</div><div class="lbl">{{ $s.label }}</div></div>
    {{ end }}
  </div>
</div>
```
(Per-preset CSS `order` rules become unnecessary — DELETE them from site.css; the loop renders in pack order. Note this cleanup in the commit.)

- [ ] **Step 2: experience** — range `site.Data.resume.experience`: `.when` = `period` (+`<span class="now">` when contains "Present") + qualifier line derived from title suffix "(CONTRACT)"/"(PART-TIME…)"/"(PROJECT-BASED)" → render small; `.role` = title with those suffixes stripped for display; `.org` = company; body = `body_html | safeHTML` (bullets arrive as `<ul><li>`). Education: after experience rows, a `sec-label`-style subheading "Education" + same row pattern over `site.Data.resume.education` (role=title, org=place).

- [ ] **Step 3: projects** — range `site.Data.projects.projects` newest-first as stored: card = `.yr`=date, `h3`=name, `p`=body, links: `{{ with .url }}<a href="{{ . }}">github ↗</a>{{ end }}{{ with .docs }} · <a href="{{ . }}">docs ↗</a>{{ end }}`. Then open-source strip: label "Open source", two `.post` rows over `site.Data.open_source.contributions` (`.d`="merged"/"PR" — derive: first entry "merged", second "PR"; simpler: add `tag:` field? NO invention — render `.d` as "PR" and let the merged state live in `detail` text; adjust: `.d` = "PR", detail already says "merged" for #105).
- [ ] **Step 4: skills** — range `site.Data.skills.categories`: group label = `.label` (prefix `»` terminal-voice only); tags = `split .details ", "` BUT parentheses-aware: use the mockup's hand-curated tag lists instead — copy the mockup's skills section markup VERBATIM into the partial (7 groups incl. Certifications/Languages, `.hot` picks, `sg-*` classes), since data `details` strings don't split cleanly and the mockup curation was approved. Add HTML comment: `<!-- tags hand-curated from data/skills.yaml (approved mockup); keep in sync -->`.
- [ ] **Step 5: Gate** — build; `check_404` home; grep per preset: `grep -c 'class="stat"' public/index.html` = 4; `grep -o 'data-count="[0-9.]*"' public/for/research/index.html | head -4` shows `98.11` first; experience rows `grep -c 'class="row"' public/index.html` = 10 (8 exp + 2 edu). Screenshot compare vs mockup sections.
- [ ] **Step 6: Commit** — `"feat: data-driven impact/experience/projects/skills sections"`

---

### Task 6: Section partials B — research, services, writing, contact

**Files:**
- Create: `layouts/partials/new/section-research.html`, `section-services.html`, `section-writing.html`, `section-contact.html`
- Modify: both mains (append sections `research services writing contact`)

**Interfaces:** consumes `site.Data.{publications,honors,volunteering,services}`, blog pages, `site.Data.contact`.

- [ ] **Step 1: research** — pubs: range `site.Data.publications.publications` (`.v`=venue split "(" first part + year, `.t`=title, `.a`=authors joined " · " emphasizing "Mohamed Aly Amin", link `{{ with .url }}<a href="{{.}}">springer ↗</a>{{ end }}`, note line `{{ with .note }}<div class="a">{{ . }}</div>{{ end }}`). Leadership block (`.honor.leadship`) from `site.Data.volunteering.volunteering` first entry: y=period condensed "2016 — 2019", t=`{{ .role }} — {{ .organization }}`, d=first highlight; render leadership FIRST in markup (matches mockup). Honors grid: range `site.Data.honors.honors` (y=trailing year parsed from title? titles carry years — display `.title` as `.t` and `.detail` as `.d`, y column = extract 4-digit year via `findRE`). Section label: terminal voice `05 research --published`, editorial voice `Research, Honors & Leadership` — voice switch: `{{ if eq $p.voice "editorial" }}`.
- [ ] **Step 2: services** — 5 cards range `site.Data.services.services` with `printf "%02d" (add $i 1)` numbering.
- [ ] **Step 3: writing** — 3 latest: `range first 3 (where site.RegularPages "Section" "blog")` (`.d`=`.Date.Format "2006-01-02"`, link title, summary plainify truncate 90) + pipe line verbatim: `// pipeline: the 400k events/sec engine write-up — in progress`.
- [ ] **Step 4: contact** — mockup contact section w/: form action Formspree exact + `_gotcha` + `_subject` hidden inputs; info column email/base/status/elsewhere from `site.Data.contact` + profile (NO phone); footer line `1 stylesheet · 0 frameworks · plex mono/sans/serif` + `last updated {{ now.Format "2006-01-02" }}`.
- [ ] **Step 5: Gate** — full shared gate NOW (all anchors exist): check_404 with all paths exit 0; grep Formspree action present ×6 pages; `grep -c '552 554' public/ -r` = 0 (phone absent).
- [ ] **Step 6: Commit** — `"feat: research/services/writing/contact sections; full page complete"`

---

### Task 7: Blog, search, 404 restyle

**Files:**
- Create: `layouts/_default/list.html`, `layouts/_default/single.html`, `layouts/_default/search.html` (replacing old ones — same paths)
- Keep: `layouts/404.html` UNCHANGED (approved as-is)

**Interfaces:** consumes strip/baseof (blog pages get `data-skin="oxo" data-role="core"` via default preset), `search-index.json` output (existing config).

- [ ] **Step 1: list.html** — baseof main: `.card`-style section, sec-label `writing --all`, rows = `.post` pattern per page (date/title/summary), pagination `lnk` links (mockup row style).
- [ ] **Step 2: single.html** — title + date/categories line + `.single-post` prose block styled in site.css (ADD a small `.prose` block to site.css: max-width 68ch, Plex Sans, headings mono-labeled, code blocks surface-2 + mono, links accent underline; ~30 lines — write it in this task, keep inside byte budget).
- [ ] **Step 3: search.html** — port existing search JS (DOM-API version, XSS-safe) into the new shell; results reuse `.post` rows.
- [ ] **Step 4: Gate** — build; check_404 incl `/blog/ /search/`; open post page screenshot; `grep -c 'wpcf7\|elementor' public/blog/index.html` = 0.
- [ ] **Step 5: Commit** — `"feat: blog/search restyled on token system (404 kept)"`

---

### Task 8: Switchover — delete the vendor layer + old layouts

**Files:**
- Delete: `static/vendor/` (entire), `static/theme/` (entire), `layouts/partials/{head,body-open,sidebar-nav,card-profile,sidebar-widgets,section-about,section-resume,section-contacts,tail-scripts}.html`, `static/js/contact-form.js` (ported into site.js), `layouts/index.searchindex.json` KEEP (search needs it — verify it doesn't reference old partials).
- Move: `layouts/partials/new/*` → `layouts/partials/*` (drop the `new/` namespace), update includes.

**Interfaces:** none new — this is the payoff task.

- [ ] **Step 1: Pre-delete inventory** — `du -sh static/vendor static/theme` (record freed bytes for the PR).
- [ ] **Step 2: Delete + namespace move** (git rm / git mv; update `partial "new/x"` → `partial "x"` in all layouts).
- [ ] **Step 3: THE gate** — build; full check_404 exit 0; **wp/vendor grep**: `grep -rniE 'wp-content|wp-includes|elementor|ryancv|jquery|/vendor/|/theme/' public/ layouts/ ; echo "exit $? (expect 1)"`; byte budget re-check; `du -sh public/` (record).
- [ ] **Step 4: Commit** — `"feat!: switch to native frontend — remove Elementor, RyanCV theme, jQuery, vendor layer (~XXX KB deleted)"`

---

### Task 9: A11y + polish pass

**Files:** modify `static/css/site.css`, partials as needed.

- [ ] **Step 1: Keyboard walk** — tab through home: strip nav → CV → hero CTAs → socials → form. Fix any missing `:focus-visible` visibility (mockup has rings; verify on all 5 skins' contrast).
- [ ] **Step 2: Contrast audit** — for each skin, check computed fg-dim on bg ≥ 4.5:1 (script: python WCAG ratio calc on token hexes — embed 15-line checker; adjust `--fg-dim` per failing skin, note changes).
- [ ] **Step 3: aria** — nav `aria-current` via scrollspy JS (add to site.js), form labels `for=` bound, canvas `aria-hidden` ✓, typed line `aria-live="off"` (decorative).
- [ ] **Step 4: reduced-motion verify** — set `ui.prefersReducedMotion` in a firefox profile, screenshot: no canvas glow, no typing (static first role), sections visible (no reveal-hidden).
- [ ] **Step 5: Commit** — `"a11y: focus-visible on all skins, contrast fixes, aria-current nav, reduced-motion verified"`

---

### Task 10: Evidence, PR, deploy, live gates

- [ ] **Step 1: Evidence bundle** — screenshots: home + 5 `/for/*` pages (desktop 1440 + mobile 390, firefox absolute paths), blog list/post, print preview (`firefox --headless --print-to-pdf`? if unsupported, skip pdf, note); byte budget output; check_404 output; wp-grep output; freed-bytes number.
- [ ] **Step 2: CHECKPOINT — Mohamed:** visual sign-off on the built site (serve `public/` on 1414, he clicks all 6 pages + blog) · **live Formspree test** (he submits one message; verify arrival) · **qutebrowser RAM check** (he opens the page, watches memory while moving cursor ~30s — must stay flat).
- [ ] **Step 3: Push + PR** — `git push -u origin native-rebuild`; `gh pr create --base main --head native-rebuild --title "Native rebuild: hand-owned frontend, 6 role-preset pages, vendor layer deleted"` with evidence body + `🤖 Generated with [Claude Code](https://claude.com/claude-code)` footer (no co-author trailer). Existing Pages workflow deploys on merge — no workflow changes needed.
- [ ] **Step 4: Mohamed merges.** Post-merge: watch run green; live smoke: `curl` home title contains "Mohamed Aly Amin"; `/for/lead/` 200; `/css/site.css` 200; wp-grep on live home = 0; **PSI API ≥95**: `curl -s "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=https://mohamedalyamin.com/&category=performance&category=accessibility&category=best-practices&category=seo" | python3 -c "import json,sys;d=json.load(sys.stdin);print({k:round(v['score']*100) for k,v in d['lighthouseResult']['categories'].items()})"` — all ≥95 (allow perf ≥90 on first pass; below that = investigate).
- [ ] **Step 5: Update `.superpowers/REBUILD-STATE.md`** — mark shipped; queue sub-projects #3/#4/#5 + Copilot blog post + real logo swap.

---

## Self-review (authoring time)

- **Spec coverage:** §2.1 structure→T4-6; §2.2 presets→T3+T4 (static pages, no switcher ✓); §2.3 tokens/fonts/motion/canvas→T1-T2; §2.4 data contract→T3, contact/form→T6, blog→T7; §2.5 a11y/SEO/print/perf→T2(print) T4(SEO/JSON-LD) T9(a11y) T10(PSI); §3 deletion→T8; §4 verification→gates + T10 checkpoint. Lighthouse-no-Chrome reality → PSI post-deploy, stated in constraints.
- **Placeholders:** none — presets.yaml embedded verbatim; extraction tasks name exact sources (mockup blocks) + exact deletions; skills tags = copy-verbatim-from-mockup decision documented.
- **Consistency:** preset keys/urls match spec table; partial namespace `new/` → flattened at T8; stat mechanism = pack-ordered loop (CSS order rules deleted, noted in T5); `aisec` content-file name `ai-security.md` with `preset: "aisec"` (url/key divergence intentional, stated).

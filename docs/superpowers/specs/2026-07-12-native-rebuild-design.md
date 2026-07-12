# Native Rebuild — Design Spec

- **Date:** 2026-07-12
- **Status:** Approved by Mohamed (design phase ran 2026-07-09 → 07-12: goals interview, 10+ live mockups, skin selection rounds, per-role content packs, section-by-section audit; final sign-off "approved" on mockup v2 @ `38c057d`)
- **Branch/worktree:** `native-rebuild` at `/home/mohamed/Projects/skylight74-native` (isolated; main repo tree stays on `main`)
- **Reference artifact:** `mockups/v2.html` — the approved living mockup. Where this spec and the mockup disagree, the mockup wins for *look/feel*, this spec wins for *data wiring and architecture*.

## 1. Goals (priority order — Mohamed's own)

1. **Fast load** (real prior pain; old stack ~600KB assets)
2. **Maintainable, hand-owned code** (no Elementor/RyanCV/jQuery opacity)
3. **Editable visuals** (change look via tokens, not archaeology)
4. **Easy content** (experience/blog updates = edit YAML/Markdown only)
5. **Keep a design he likes — but own it** ("faithful-but-owned" was superseded during design: final look = new, approved via mockups)

Non-goals: pixel-parity with the old site (dead requirement); comments/authoring/AI-chat (queued sub-projects #3/#4/#5); i18n.

## 2. Final design (as approved in mockup v2)

### 2.1 Structure — one scrolling page + blog
Single page, 9 scroll-snap card sections (`scroll-snap-type: y proximity`, sections `min-height:100svh`), sticky top strip, scrollspy nav, **no visible scrollbar** (`scrollbar-width:none`; scrollspy + section numbers are the position cue):

`01 hero · 02 impact · 03 experience · 04 projects(+open-source) · 05 skills · 06 research/honors/leadership · 07 services · 08 writing · 09 contact`

Separate pages: `/blog/`, `/blog/<post>/`, `/search/`, `404` (existing BSOD/terminal 404 stays). Print stylesheet: the scroll page prints as a clean document.

### 2.2 Role presets — 6 pages, one design system
Each preset = full token-skin + content emphasis. Built as **statically generated pages** (`/` + `/for/<role>/`), NOT client-side switching (the mockup's buttons were a design tool; production = real URLs for tailored application links; strip shows nav only, no preset switcher on the live site).

| Preset | URL | Skin | Info accent | Act accent | Voice |
|---|---|---|---|---|---|
| CORE | `/` | **oxo** (OLED #000, Carbon greys #161616/#262626) | teal `#3ddbd9` | pink `#ee5396` + neon glow | terminal |
| SEC | `/for/devsecops/` | **mono** (white/black, hairline #141414) | red `#e02418` | same (single-accent) | terminal |
| GO | `/for/platform/` | **ink** (cool white #f7f8fa) | blue `#2458ff` | same | terminal |
| AI-SEC | `/for/ai-security/` | **graphite** (#191b1d) | viridis `#35b779` | yellow `#fde725` | terminal |
| RSRCH | `/for/research/` | **latex2** (cream #faf7f0, serif headings) | hyperref blue `#1a4fa0` | same | **editorial** |
| LEAD | `/for/lead/` | **ink + serif** | crimson `#c22f4b` | same | **editorial** |

Per-preset content pack (all values live in mockup v2 and derive from `~/Projects/CV/roles/*.yaml` lane files — copy verbatim from the mockup's `kickers/sums/openTo/stat3/stat4/statMvp/roleSkin` maps and CSS order rules):
- **kicker** (hero eyebrow line), **hero summary** (lane summary adapted), **open-to line**, **typed-roles pin** (AI-SEC/RSRCH pin "Security-ML Researcher"; LEAD = static serif tagline "Engineering leader — security, platform, delivery.")
- **stat strip**: 4 cells, per-preset order + substitutions (GO: `270k users / 10 microservices` replaces 3-mo cell; RSRCH: `2 peer-reviewed papers` replaces team cell; LEAD: `4+ engineers & ASELSAN partners trained` replaces accuracy cell)
- **skills group order** (lane `skills_order`: GO=Backend first; AI-SEC=Sec,ML; RSRCH=ML,Languages)
- **voice**: editorial presets (RSRCH, LEAD) hide the `$` prompt, drop `--flag` section-label syntax (plain "Impact/Experience/…"), serif `--head`; LEAD additionally: leadership block promoted first in Research, B&W stage photo (`lead-photo.jpg` grade recipe: crop 2400²+1350+0, grayscale, level 6%/94%), editorial photo frame, serif stat numerals, `.imp-note` hidden
- **Action semantics (all skins):** info accent reads; `--act` acts — ONLY on: primary buttons (CV/Send + neon glow on oxo), hover-intent (links/nav/ghost warm to act), liveness (availability pulse, cursor, `::selection`, "Present" dates, "Available", footer dot, pipeline `●`), emphasis rims (`.tag.hot`), form focus, `:focus-visible` rings.

### 2.3 Design system
- **Tokens**: every color/spacing = CSS custom property on `:root`/`[data-skin]`; 8px spacing scale; skins are token blocks only.
- **Type**: IBM Plex Mono (data layer: nav/labels/dates/tags/stats/buttons/prompts) + IBM Plex Sans (prose) + IBM Plex Serif (editorial presets' `--head`). **Self-hosted woff2**, subset latin+latin-ext, ~4-6 files, `font-display: swap`. No Google Fonts, no FontAwesome CDN (icons = inline SVG).
- **Monogram**: solid accent block placeholder — Mohamed supplies real logo asset later (drop-in swap).
- **Motion** (all behind `prefers-reduced-motion`): hero stagger, section fade-up (IntersectionObserver), scrollspy + `~/crumb` update, stat count-up, vanilla typed roles (~20 lines), availability pulse, hover ticks.
- **Canvas dot-grid background** (signature flourish): MANDATORY perf pattern — static grid pre-rendered to offscreen canvas once per size/skin; per-frame = 1 `drawImage` + glow dots within 180px of cursor only; colors read once per render; zero-size guards; `visibilitychange` pause; rAF-coalesced pointermove. **Verify RAM/CPU on qutebrowser (QtWebEngine) explicitly** — prior bug (style-recalc per dot) burned RAM.

### 2.4 Content architecture (the content/design contract)
- CONTENT (content-lane/Mohamed owns): `data/*.yaml` — existing `profile, contact, resume(8 exp + education), skills(7), honors(3), publications(2), projects(5), services(5), open_source(2), volunteering(1)` — plus NEW fields this build adds and then hands to content lane: `profile.open_to`, `profile.roles[]` (typed rotator list incl. Tech Lead), `profile.summary` variants + per-preset packs in `data/presets.yaml` (kicker/sum/open-to/stat config per role — single file so content lane can tune positioning without touching layouts).
- DESIGN (this build owns): `layouts/**`, `static/css/site.css` (ONE hand-written stylesheet), `static/js/site.js` (one small vanilla file), `static/fonts/**`.
- Hugo generation: `/for/<role>/` pages from a single layout parameterized by preset key (content pages `content/for/<role>.md` with `preset: <key>` front matter, or equivalent) — one template, six outputs.
- Contact form: keep Formspree endpoint `https://formspree.io/f/mwvdzgqv`, honeypot `_gotcha`, `_subject`, existing `contact-form.js` behavior (inline success/error, double-submit guard) — port into `site.js`. **No phone on the site.**
- Blog/search/404: keep existing pages, restyle to token system (404 already approved as-is; keep).

### 2.5 Cross-cutting quality bar
- **A11y**: visible `:focus-visible` rings, keyboard-navigable nav, aria on interactive widgets, contrast ≥4.5:1 per skin (verify oxo dims + latex2 blues), `prefers-reduced-motion` honored everywhere (incl. canvas + typed + count-up).
- **SEO/social**: per-page `<title>`/meta description; OG+Twitter cards; JSON-LD Person on `/`; canonical; `/for/*` pages `noindex,follow` OPTIONAL — decision: **index them** (they're honest tailored views, not doorways) but canonical→`/` to avoid dup-content penalties.
- **Perf budget**: CSS+JS **<40KB** combined (excl. fonts/images); fonts ≤ ~120KB total woff2; Lighthouse ≥95 all categories; no render-blocking third-party.
- **Print**: `@media print` — light tokens, no canvas/motion, sections flow (no snap), URL footnotes for links.

## 3. Deletion list (switchover — the payoff)
After the new build passes gates, in the SAME PR: delete `static/vendor/elementor/**`, `static/theme/**` (RyanCV CSS/JS incl. typed.js/magnific/isotope/swiper/validate/calendario/ryan-scripts/navigation), `static/vendor/wpjs/**` (jQuery, migrate, jquery-ui, block-library), `static/vendor/ryancv-widgets/**`, `static/vendor/contact-form-7/**`, old `layouts/partials/{head,body-open,sidebar-nav,card-profile,sidebar-widgets,section-*,tail-scripts}.html`, old `layouts/_default/{list,single,search}.html` replaced by restyled versions. Old media stays (`/media/**`). Zero `wp-*`/vendor references in the built site (grep gate).

## 4. Verification (definition of done)
1. **Visual**: Mohamed clicks all 6 preset pages + blog/search/404 — sign-off (the mockup is the reference).
2. **Behavior**: nav scrollspy, snap scroll, typed roles, count-up, canvas, form submit (live Formspree test — coordinated, one email), blog/search work, all links resolve (`check_404.py` exit 0 incl. `/for/*`).
3. **Perf**: Lighthouse ≥95 ×4 categories on `/` and one `/for/*`; total CSS+JS <40KB verified; **qutebrowser RAM/CPU check with Mohamed**.
4. **A11y spot**: keyboard-only pass, axe-core clean on `/`.
5. **Deploy**: existing Pages workflow builds; PR to `main` carries evidence; Mohamed merges; live-site smoke (title, `/for/lead/` 200, assets 200, wp-grep 0).

## 5. Out of scope (queued sub-projects)
#3 blog authoring workflow · #4 comments · **#5 AI portfolio chat / "website as prompting experience"** (Cloudflare Worker proxy, strict RAG over `data/`, drawer not gate) · real logo asset (Mohamed) · GitHub profile fixup (off-site, Mohamed) · Copilot blog post (writing queue).

## 6. Working rules
Worktree `skylight74-native` only; commits without Claude trailer; content facts NEVER invented — sources: `data/*.yaml` (attested) + mockup v2 packs; any new claim needs Mohamed's attestation; subagent-driven build with model routing (haiku mechanical / sonnet judgment / high-effort reviews); verify-don't-assert at every gate.

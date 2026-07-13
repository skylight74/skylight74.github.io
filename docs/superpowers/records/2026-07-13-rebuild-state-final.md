# Native Rebuild — session checkpoint (2026-07-09)

Resume file for the "rebuild design off the WordPress load-bearing layer" sub-project.
Memory: ~/.claude/projects/-home-mohamed-Projects-skylight74-github-io/memory/project-native-rebuild.md

## Session settings
- Model Opus 4.8 (1M ctx), effort max, **CAVEMAN MODE active** (terse chat; normal in specs/commits).
- Commits on this repo: **NO `Co-Authored-By: Claude` trailer** (Mohamed's standing rule).

## STATUS: brainstorming (superpowers), design §1 presented, AWAITING Mohamed's approval.

## What's done
- Port: DONE, merged, live (mohamedalyamin.com).
- Cleanup sub-project (dead WP cruft): DONE, merged (PR #7), live. C1/C2/C3/C5 removed; C4 (block-library.css) KEPT (styles search-icon #32373c); favicon added (charcoal M).
- Branch cleanup: main / content-sync-cv / native-rebuild only. Tags: checkpoint-wordpress-export, checkpoint-pre-coauthor-strip. main=496fc3c.

## Approved for the rebuild
- Goal: fast + maintainable + editable visuals + easy content + own the design (keep the look he likes).
- Fidelity: "faithful but owned" (NOT pixel-identical). Verify = visual sign-off + load-time + behavior.
- Approach A: clean-slate parallel rebuild. New layouts + one static/css/site.css + tiny vanilla JS; content in data/*.yaml; drop Elementor/RyanCV/jQuery; ~600KB → <40KB; self-host Space Grotesk.
- Split: content(data/ + content/blog)=fork ; design(layouts/ + static/css + static/js)=me.

## Fork coordination (MUST honor)
- PIN commit **5b73c0a** (content-sync-cv) = content source-of-truth. Migrate its title/roles/about/services into an expanded data/ schema (currently in layouts).
- Rebuild kills the 2 sanitize leftovers (section-about "The Legend", tail-scripts elementor JSON).
- Fork edits only data/*.yaml + content/blog going forward.

## NEXT STEPS on resume (in order)
1. Fold fork coordination into the design (pin 5b73c0a; data/ schema must add: page title, typed roles[], about prose + services, in addition to profile/resume/contact).
2. Resume brainstorm: get §1 approval; then §2 (section-by-section build order + verification method: chrome-MCP console/network/computed-style + Firefox/ImageMagick for spot pixels + Mohamed visual review + Lighthouse/load-time); §3 (perf targets — concrete: <40KB CSS+JS, Lighthouse >95, self-host fonts); §4 (switchover: replace layouts, delete Elementor/RyanCV/jQuery in one commit after all sections match; merge strategy vs content fork).
3. Write spec → docs/superpowers/specs/2026-07-09-native-rebuild-design.md → self-review → Mohamed review gate → invoke writing-plans → subagent-driven-development (haiku mechanical, sonnet judgment, reviewers sonnet high-effort; model-route per CLAUDE.md).

## Verification tooling (proven this session)
- chrome-devtools MCP → qutebrowser CDP on 127.0.0.1:9222. WORKS: navigate/evaluate_script/list_console_messages/list_network_requests. FAILS: screenshots (bg tab 0-viewport), new_page, resize. Use an existing DDG tab (select_page then navigate); don't hijack YouTube.
- Pixels: Firefox headless --screenshot (ABSOLUTE out paths required) + ImageMagick `compare -metric AE`. Profile CSS: hide .preloader, freeze animations, `video{visibility:hidden}` for determinism.
- Serve worktree build on a dedicated port (was 8083): `cd public && python3 -m http.server`. Rebuild with `hugo` (0.163.3 ext). Note: `rm -rf public` kills a server whose cwd is public/ — restart after.
- Baseline for THIS rebuild differs: not pixel-gate. Capture current-look reference screenshots for side-by-side visual comparison, not AE-zero.

## Scoping data (weights to beat)
theme/style.css 175KB · elementor frontend-lite 108KB · block-library 89KB(kept) · jquery 89KB+migrate 11KB · magnific-popup 42KB · elementor frontend.js 40KB · typed.js 15KB · ryan-scripts 15KB. Home HTML 192KB, 498 elementor-* classes.

## Decisions 2026-07-11 (PoC v2 round)
- Blueprint skin verdict: "cool but unrecognisable" — lesson: conventional structure, distinctive skin.
- Structure: scroll-snap card sections (card feel, zero click-wall) — pending Mohamed's judgment in PoC v2.
- Role presets: one design system; /for/devsecops|platform|lead = token preset + hero line + stat order. Site "/" default.
- 3js: NO (over-eng). Optional canvas dot-grid flourish only.
- EXPERIENCE CURATION: site mirrors base-cv = 4 core entries (Freelance/Dolusoft/METU/Interprobe) + one muted line "Earlier: business development & IT ops (Boraq-Group, Apply Center · 2018–2020)". Overrides fork's 6-entry list (Mohamed 2026-07-11). Apply data-side during rebuild.
- open_to values: DevSecOps · Platform/Backend (Go) · Security · Tech Lead (add field to profile.yaml in rebuild).
- Chairman @ METU ISA: was dropped by fork's base-cv sync (5b73c0a). Mohamed wants it → Leadership block inside Research section ("Research, Honors & Leadership"): Chairman 2016-2018, 200+ members, 5+ events, refugee conference (facts from master-cv.yaml:176-187).
- Sardis/Castrum: correctly folded into freelance umbrella (base-cv curation + de-crypto positioning). Not site entries.
- Certs: all in-progress → never badges; skills text line only. Flag to content lane: master-cv (AZ-500/CISSP/OSCP) vs skills.yaml (AWS-SAA/CKA) inconsistency.
- Projects section: 3 existing + OSSArch + Container Security VA + Claude Copilot (framed "archived — retired when vendor tooling caught up"). 6 cards total. Data additions = content values from master-cv, wired during rebuild.
- SKIN FINALISTS (2026-07-11): OXO (oxocarbon OLED, default in mockup; accents base16-authentic: teal #3ddbd9 core / green #42be65 sec / blue #78a9ff go / purple #be95ff lead; pink #ee5396 available alt) · INK (cool white, blue #2458ff core, crimson lead — best lead fit) · MONO (brutal white/black, SINGLE red #e02418 all roles). Eliminated: charcoal, steel, paper, gruv, nord, olive.
- Mohamed reviewing content with fork concurrently; resume.yaml now 8 entries incl Castrum (Dec23-May24) + Sardis (23-24) contracts — his own edits, supersede 4-core curation. Castrum client-credit vs entry: resolved by his edit (separate entries).
- SUB-PROJECT #5 queued (2026-07-11): AI portfolio chat. NOT an entry gate — classic site default; floating terminal drawer "$ ask my portfolio anything" + optional /ai page. Arch: Cloudflare Worker proxy (key server-side, rate-limited, origin-locked) → Haiku-class model, STRICT RAG over data/*.yaml+blog, refuses off-scope (calibration discipline). After rebuild/authoring/comments.
- Role→skin coverage FINAL: CORE=OXO dual(teal/pink+neon) · SEC=MONO(red brutal) · GO=INK(#2458ff single) · LEAD=INK serif(crimson, editorial voice pack). Skin selector removed.
- PRESETS FINAL (2026-07-11, from CV/roles lane analysis): CORE(oxo teal+pink dual) / SEC(mono red) / GO(ink #2458ff) / RSRCH(oxo purple #be95ff — AINA/DIONA/K8s-dataset lead, 2-papers stat) / LEAD(ink serif crimson, editorial voice, B&W stage photo). Covers all 9 CV lanes; solutions folded/skipped (low-earned), islamic-fintech = CV-variant only.
- F1 dedup applied (commit 5065372). F2: projects=5 (content added OSSArch+ContainerSec); Copilot = NO card, queued as future blog post. F3: site ships phone-free (v2 already omits; live site currently shows it — dies with rebuild).
- Content review 2026-07-11 DONE: all data files verified vs master/base-cv + attestation trail; structure mods: Projects gains open-source strip; services grid 5; Research="Research, Honors & Leadership" (volunteering.yaml); MISA end Jan 2019; MIT-EF Jan 2018 (no "finalist").
- PRESET ROSTER FINAL v2 (2026-07-11): 6 presets — CORE=OXO dual · SEC=MONO red · GO=INK #2458ff · DETECT=GRAPHITE viridis(#35b779 info/#fde725 act) · RSRCH=LATEX2 hyperref-blue(#1a4fa0, serif, cream paper) · LEAD=INK serif crimson + B&W stage photo. RSRCH cluster split into DETECT(industry)/RSRCH(academic) per Mohamed. Canvas perf bug fixed (offscreen prerender). Verified live via CDP.
- RAM/perf (2026-07-11): canvas bug found+fixed in mockup (getComputedStyle per dot per frame). BUILD REQUIREMENT: offscreen-prerender pattern mandatory; verify perf on qutebrowser (QtWebEngine) specifically; visibilitychange pause; rAF coalescing. Re-test RAM with Mohamed after build.
- SUB-PROJECT #5 EVOLVED (his note): beyond chat drawer — "website as prompting experience" (visitor prompts the portfolio; AI-CV). Same guardrails (Worker proxy, strict RAG over data/, no invention). LATER — after rebuild ships.
- SUB-PROJECT #5 SEQUENCING (Mohamed 2026-07-13): START with AI portfolio chat as an "ask me anything" SECTION (not drawer-first), THEN expand (voice I/O later evolution). Kickoff = right after rebuild PR merges. Guardrails unchanged: Cloudflare Worker proxy (key server-side, rate-limited, origin-locked), strict RAG over data/*.yaml + blog, refuses off-scope, no invented facts.
- SUB-PROJECT #6 QUEUED (Mohamed 2026-07-13): AR/TR localization. Phase 1 = TR (LTR; content ~70% exists in master-cv-tr.yaml; Hugo i18n + hreflang + switcher). Phase 2 = AR (RTL mirroring audit of site.css physical left/right, IBM Plex Sans Arabic subsets, his translations = attestation-grade). Locale as PATH prefix (/tr/ /ar/), not subdomain.
- SUB-PROJECT #7 QUEUED (Mohamed 2026-07-13): role SUBDOMAINS (devsecops.mohamedalyamin.com → /for/devsecops/) via the same Cloudflare Worker layer as #5 (GH Pages = one domain/site; Worker rewrite/301, wildcard cert; paths stay canonical). Cosmetic experiment, reversible.
- SUB-PROJECT #8 QUEUED (Mohamed 2026-07-13): visitor analytics — VISITOR COUNTER + IP COLLECTOR. Rides the Cloudflare Worker layer (static Pages can't log server-side): Worker middleware logs request IP/UA/path to KV/Analytics Engine + exposes a counter. PRIVACY NOTE to resolve at build: raw-IP retention = PII under KVKK/GDPR (his own compliance line) → needs privacy notice + retention window, or hash/truncate IPs; counter itself can stay anonymous. Alternative if Worker layer delayed: GoatCounter/Plausible (counter only, no IPs).
- Pictures (new hero/lead shots): PINNED per Mohamed 2026-07-13 — revisit post-merge; current photos pass.

## 2026-07-13 MERGED + LIVE: PR #8 merged 11:30Z, Pages run 29246484269 success. Live smokes ALL GREEN (200s, title, oxo/ink skins, ambigram, wp-grep 0, phone 0, BSOD 404 works). PSI: anonymous API quota exhausted — retry tomorrow or pagespeed.web.dev manually. PENDING: Formspree live test (his send), PSI score. Post-merge queue: #3 authoring, #4 comments, #5 AMA, #6 TR/AR, #7 subdomains, #8 analytics, GitHub profile edit, pictures pinned.
- 2026-07-13: FORMSPREE LIVE TEST PASSED (his send+receive confirmed). Only PSI score pending (quota).
- 2026-07-13: PSI PASSED (his pagespeed.web.dev run, read via CDP): mobile 97/100/100/100, desktop 100/100/100/100. ALL PLAN GATES CLOSED. Rebuild = fully done.

# SDD Progress Ledger — native rebuild

Plan: docs/superpowers/plans/2026-07-12-native-rebuild.md (committed 48d2155)
Base at start: 48d2155

## Completed tasks
Task 1: complete (commits 48d2155..12991a9, review clean)
Task 2: complete (commits 12991a9..d25fae3, review clean; 26,081B of 40,960 budget)
Task 3: complete (commits d25fae3..03d3575, review clean; zero content drift)
Task 4: complete (commits 03d3575..c9e8080 incl. /for/ list-page fix, review approved after 1 fix loop)
Task 5: complete (commits c9e8080..0f11a63 incl. projects-order fix, review approved after 1 fix loop; bytes 25,607)
Task 6: complete (commits 0f11a63..e345f3c, review clean; full page complete, all 8 sections)
Task 7: complete (commits 29bb69f..04d61f2 [after cross-task fix 29bb69f], review clean; bytes 27,226; report fallback-claim corrected by controller)
Task 8: complete (commits 04d61f2..41bcaf5, review clean zero findings; 5.5MB deleted, public 15M→9.2M)
Task 9: complete (commits 41bcaf5..22f58d3, review approved; both Importants closed by controller — 37→41 tally corrected in report, blog/search h1 screenshots taken + verified [shots/t9-blog-list-h1.png, t9-search-h1.png]; bytes 27,338)

## Minor findings (for final whole-branch review triage)
- T1: assets-notes/fonts.md:27 unicode-block misattribution (Turkish chars live in Latin-1/Ext-A, not Ext-B)
- T2: harness.html card is div not section — initSpy selects section.card, so harness never exercises .card>.in reveal path (throwaway file)
- T2 note: brief prose said site.js exposes initSite(cfg); implemented as self-initializing IIFE per brief's own skeleton. Resolved: no task calls initSite; config via #site-cfg JSON. Intentional.
- T4: hero.html:15 location kv unbolded vs mockup <b>Ankara</b> (flat location_line string; follow-up = dedicated data field or split)
- T4: hero.html:1 unused `$pg := .page`
- T4: baseof.html preset lookup evaluated 3× (verbatim from plan; harmless)
- T4 note: /for/* pages absent from sitemap BY DESIGN (list:never + canonical→/); correct per reviewer analysis
- T5: section partials use untrimmed `{{ range }}` lines → blank-line artifacts in built HTML (harmless; hero.html style is tidier)
- T6: section-research.html:5 safeHTML wraps whole author join (future author w/ &/< would render unescaped; per-author range would scope trust)
- T6: contact address renders "Çankaya/Ankara" (data) vs mockup "Çankaya / Ankara" (cosmetic)
- T6: contact form labels lack for/id binding (inherited from mockup) — T9 a11y item
- T6 note: nav lacks #services/#writing BY DESIGN (matches approved mockup strip)
- T6 infra: mockup full-page screenshots need file:// not http://8084 (HTTP truncates 768px); headless captures need ui.prefersReducedMotion=1 or reveal animations render blank
- T7: .prose code uses symmetric border-radius 3px vs house cut-corner convention (defensible exception)
- T7: blog list/search use h2 not h1 — implementer-flagged for T9 a11y pass
- T7: search-result dates "7 Jul 2026" vs site "2026-07-07" (format baked in off-limits searchindex output)
- T9: site.js:15-16 data-pin parse duplicated in both branches (hoistable, ~stylistic)
- T9: aria-current="true" valid; "location" marginally more precise (defensible)
- T9: report cited site.css:207 as focus source; actual winner is :281 (cascade) — write-up imprecision only

## Cross-task fix (post-T6): T5 sections lack editorial voice-switch on sec-labels (T6 sections have it; mockup eduLabels map covers all 8 sections). FIXED 29bb69f — controller-verified against mockup eduLabels + built /for/research/ output (all 8 labels match; terminal pages unchanged).

## Mohamed checkpoint finding #1 (2026-07-12): blog pages had NO escape route — strip anchors were bare fragments (dead at /blog/*), monogram was a div. FIXED a93797d: nav anchors root-prefixed (/#x) on non-one-pager pages (bare #x + scrollspy intact on home//for/*), monogram → <a href="/">, Blog tab .on within blog section. Verified per page type. Lesson: one-page mockup components need multi-page context audit.

## Mohamed checkpoint findings #2-#3 (2026-07-13):
- WP-artifact question → full-worktree sweep: shipped code 100% clean (wp-grep 11 markers exit 1); c8f6e21 dropped 7.7MB unreferenced WP-era media (pexels video = 81% of deploy!), stale comments, dead gitignore lines. public 9.2M→1.6M. Kept: resume.yaml logo files (dormant, attested), blog prose WP mentions (his writing), docs history.
- Blog skin jump → Mohamed chose fragment continuity: fc8ec25 — role pages link /blog/#skin=X&role=Y, pre-paint whitelisted snippet on non-preset pages, site.js initSkinLinks propagates hash to blog/search-internal links. Verified: latex2 blog screenshot (shots/blog-skin-latex2.png). Bytes 27,567/40,960. Note: sec-label stays terminal-voice on fragment-skinned blog (voice = template-side) — cosmetic, accepted.

## Mohamed checkpoint finding #4 (2026-07-13): /for/ was directory-listing/404 — now a designed role-picker: 6fe2af8. layouts/for/list.html renders 6 "peek" cards, each wearing its target skin via .peek[data-skin] token cascade (skin selector lines extended html[…],.peek[…] — zero value changes). _index.md flipped render-on (list:never kept). check_404 22 refs 0 failures incl /for/. Bytes 28,888/40,960. Screenshot shots/for-picker.png.

## Mohamed checkpoint findings #5-#6 (2026-07-13): /for picker v2+v3 (37062b4, a1f4b2e) — glass flip cards (hero summary on back, reduced-motion swap fallback), BSOD-azure backdrop; strip removed on /for (baseof nostrip param) with cd/ home chip; 404 recolored navy→true BSOD azure #0078d7 (user: "bluescreen we had before" = classic azure, navy was the drift). Bytes 30,382/40,960. check_404 22 refs 0 fail. Note: 404 was never structurally lost — python dev server doesn't map 404.html; Pages does.

## Mohamed checkpoint finding #7 (2026-07-13): azure 404 guess WRONG — he wanted the exact 2-days-ago navy BSOD. Reverted via git checkout a1f4b2e^ (82d3bed); /for backdrop re-synced navy. Local wrong-path-404 fixed properly: .superpowers/sdd/serve404.py (SimpleHTTPRequestHandler.send_error override → serves 404.html like GitHub Pages) now runs on 1414. Lesson: "the one we had" = restore exact artifact from git, never reinterpret palette from memory.

## Mohamed checkpoint findings #8-#10 (2026-07-13): /for picker iterated v4→v8 (cd3868d 37062b4 595730d 50c0d51 eee4cf1 1d51561 4b728fe): oxo-black backdrop + drifting orbs (13s/17s), easter-egg copy ("You found the switchboard"), smoked-glass unified cards + brightened per-role accent vars (--pa), swatch chips added then removed (his call), flip-stutter fixed (static hitbox, inner .flip rotates), orb speed 2×. 404 azure misfire reverted to exact navy BSOD (82d3bed) + 404-aware local server (.superpowers/sdd/serve404.py). Per-role HOT SKILLS shipped 97ab8a7 (data-k slugs, 6 role-keyed sets, evidence-linked picks). Bytes 33,948/40,960. Sub-project #5 = AMA section, kickoff post-merge (REBUILD-STATE updated).

## Session 2026-07-13 (continued): delta review CLEAN + 3 fixes (8cfaa4f); per-role experience/projects/services finalized (4da2ddf a4c378c 51b803e e51bbb5); ambigram name-mark shipped w/ CC BY-SA attribution (d9a3eff); khatam strip pattern + star glyphs (72d8cf1); conference honors row + full volunteering highlights (d9a3eff); RAM report: site renderer 1.16GB = 7.5% of qutebrowser total (his RAM pain = other tabs); fixes applied 8233852 (orb blur→gradients, hidden-tab pause, /for orb dedup; backdrop-filter kept — measured delta 0). Report .superpowers/sdd/ram-report.md.

## 2026-07-13 late wave: projects deep-check (SGL docs-only, opencode dropped, DIONA site link, GH census: 11 PRs/1 merged-external, +mplab tool +rpi fork w/ kind badges, refconf LEAD card); FINAL projects review by fresh agent — CRITICAL caught: core leaked 7 cards (no filter) → pinned approved 5 (2aaf80a); rpi wording exactly-true; housing date Present. User wave: ambigram favicon; research/leadership/honors 3-way split (AINA row removed — credential not award); blog pages page-flow + own labels (snap-trap fix); mobile burger (nav was display:none!); canvas dots → 8-point stars (175d121). Services-not-everywhere = deliberate (RSRCH/LEAD cut). Pattern inspiration link = premium stock — inspiration only, no copying.

## Fact audit (2026-07-12): 174 claims — 141 ATTESTED, 32 DERIVED-OK, 0 MISMATCH, 1 UNSOURCED→FIXED 02e969b (grant #120E537 removed from DIONA per Mohamed: DIONA = graduation project, separate lineage; number stays on the two attested papers). Known-rules: 5/5 pass. Awareness footnotes (not failures): "6 eng trained" verb vs attested "4 engineers" training group; Castrum "15-20" vs services "15+" (both sourced). Report: .superpowers/sdd/fact-audit-report.md

## Mohamed decisions (2026-07-12, pre-checkpoint): (1) presets ship at approved emphasis-level; content-level differentiation (per-role section/project order, experience emphasis) = QUEUED sub-project post-merge. (2) Fresh fact audit vs CV repo ordered before sign-off — dispatched.

## Final fix wave: DONE — b0f4b78 (per-page canonical + article og:type + anchor repair), a32ffac (isPlainText comment corrected; removal rejected: 571-line escaping diff). Follow-up noted: post og:description still core bio (spec-gray). Evidence bundle: .superpowers/sdd/evidence/ 14 files (print PDF skipped, firefox compositor bug). NEXT: Mohamed checkpoint (visual sign-off / Formspree live test / RAM check) → push → PR.

## Final whole-branch review (opus, 496fc3c..22f58d3): READY WITH FIXES
- Important 1: head.html:7 hardcoded canonical→BaseURL de-indexes blog posts (plan-level defect; spec §2.5 wants per-page). Fix: conditional canonical + per-page description/og:title + og:type article for posts.
- Important 2: hugo.toml isPlainText=true rationale stale post-T8 (WP partials deleted). Fix: attempt removal gated on byte-identical public/; else correct comment only.
- Minor fix now: content/blog/hello-hugo.md links /#contacts-e (rebuild renamed section to #contact) — rebuild-broken link, repair.
- Follow-ups (not blocking): hero location unbolded; untrimmed ranges; research safeHTML author join; findRE data-shape fragility; search date format; .form-msg.err color weak; pipeline teaser staleness; footer last-updated = build date.
- Ledger triage: T6 labels + T7 h1 already fixed in T9 (verified); rest dropped or follow-up. 404 diff has 3 cosmetic rebrand lines (the-legend→mohamedalyamin) — improvement, noted.

## Controller decisions (T5, 2026-07-12)
- First T5 implementer died mid-exploration (session limit); nothing committed; fresh dispatch with distilled data facts.
- Plan's "10 rows (8 exp + 2 edu)" gate stale — data has 1 education entry → gate = 9. Gate follows data.
- Mockup v2 contains NO education markup; plan explicitly adds education rows (post-mockup decision, spec data contract). PLAN GOVERNS — education renders. Flag to Mohamed at T10 sign-off (one-partial-edit removable).
- Experience casing/qualifiers: template CANNOT derive mockup's title-casing from UPPERCASE data (R&D/TÜBİTAK/OSSArch mangle). DECISION: resume.yaml titles updated to mockup-approved display strings + new optional `qualifier:` field (project-based/contract/part-time), qualifiers removed from title/company strings. Facts identical to approved mockup v2 = attested. Orgs stay uppercase (approved look). Periods stay "-" in data; template renders em-dash. Report at Mohamed checkpoint.

## 2026-07-13 SHIPPED TO PR: branch pushed, PR #8 open (121 commits, 496fc3c..666ee51). Post-merge gates pending: Pages run, live smokes, PSI >=95, Formspree live test, REBUILD-STATE+memory close-out.

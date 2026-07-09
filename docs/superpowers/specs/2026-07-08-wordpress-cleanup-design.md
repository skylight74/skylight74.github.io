# WordPress Dead-Cruft Cleanup — Design Spec

- **Date:** 2026-07-08
- **Status:** Approved by Mohamed (scope + approach + verification depth)
- **Branch:** `wp-cleanup`, cut from `origin/main` @ `b32d3d5` (the live site)
- **Sub-project 1 of 4** in the post-port batch (others: SPA blog nav, blog authoring, comments — each gets its own spec later)

## 1. Goal

Remove **dead, unused WordPress/plugin artifacts** from the shipped Hugo site with **zero visual and zero behavioral change** — the site must stay pixel-identical and keep working exactly as it does now. Each removal is *proven* dead by verification, never assumed.

## 2. Scope

**In scope** — strip dead cruft only, up to (not into) the load-bearing layer:

Candidate groups, ordered by confidence (each is a hypothesis to be proven, not a guaranteed removal):

| # | Candidate | Current state (verified 2026-07-08) | Risk |
|---|---|---|---|
| C1 | 6× `_wpcf7*` hidden form fields (`_wpcf7`, `_wpcf7_version`, `_wpcf7_locale`, `_wpcf7_unit_tag`, `_wpcf7_container_post`, `_wpcf7_posted_data_hash`) in `section-contacts.html` | present; CF7 backend gone, POSTed to Formspree which ignores `_`-prefixed unknowns | **Low** — pure dead fields |
| C2 | `global-styles-inline-css` `<style>` block in `head.html` (88 `--wp--preset--*` vars + `has-*-color`/`wp-block-*` utility classes) | present; `has-*-color` classes used **0×** on any element | **Low** — classes unused |
| C3 | `wp-polyfill.min.js` + `regenerator-runtime.min.js` `<script>` tags (`tail-scripts.html`) | both loaded | **Medium** — must confirm no remaining script references `regeneratorRuntime`/polyfilled globals |
| C4 | `wp-block-library/style.min.css` `<link>` (`head.html`) | linked; the sidebar search box uses `wp-block-search*` classes (9×) | **Medium-High** — search box styling may depend on it; test carefully |
| C5 | `portfolio_ajax_loading_data` inline config **+** `ajax-portfolio-content.js` (loaded 3×) | config still consumed by the loaded script | **Medium** — only removable *together*, and only if the portfolio/isotope ajax feature is confirmed unused on the live site |

Notes: `var wpcf7 = …` (an earlier suspected item) is **already absent** — removed during the contact-form work; not a task here.

**Out of scope** (the load-bearing layer — this is the *design*, not cruft; a future redesign sub-project, not this one):
- Elementor (`/vendor/elementor`, `elementor-*` classes/markup, `elementorFrontendConfig`, `/theme/elementor-css/*`)
- RyanCV theme (`/theme/*`, `/vendor/ryancv-widgets`, its CSS classes)
- jQuery + jQuery-migrate + jQuery-UI-core (`/vendor/wpjs/jquery*`) — drives nav, typed.js, widgets
- WordPress structural classes that carry theme CSS: `wpcf7-*` (form styling), `menu-item*`, `page_item*`, `wp-container*`, `sidebar-wrap`, and the `wp-block-search*` markup itself (only its *extra* Gutenberg stylesheet C4 is a candidate, not the markup)

## 3. Requirements

| # | Requirement |
|---|---|
| R1 | Every page stays **pixel-identical** to the pre-cleanup baseline at desktop + mobile widths |
| R2 | **No new console errors** and **no new failed network requests (404s)** after any removal |
| R3 | All interactive behavior still works: nav card-switching, hamburger sidebar, contact form submit/validation, blog list/post, client-side search, typed.js subtitle, background video, 404 |
| R4 | A candidate that fails R1–R3 is **reverted** and recorded as "not actually dead" — no forcing |
| R5 | `main` (the live site) is untouched until Mohamed merges one reviewed PR |

## 4. Approach — "prove-it-dead" incremental removal

**Baseline (once, before any removal):** with the site built and served locally, capture via the **chrome-devtools MCP** (real Chrome — reliable, unlike the port's Firefox-headless hack): full-page screenshots of every page (home, `#resume-e`, `#contacts-e`, `/blog/`, a post, `/search/?s=…`, `/404.html`) at desktop (1440) and mobile (390) widths; the console message list; and the network request list (to diff 404s). Store as the reference.

**Per candidate (one at a time):**
1. Make the single removal.
2. `hugo` rebuild; serve locally.
3. Re-capture the same screenshots via Chrome MCP; **diff against baseline** — must be identical (allowing only the known animation-noise regions: typed.js subtitle text, background video frame).
4. Check console = no new errors; network = no new 404s.
5. Functionally exercise the affected surface in Chrome (e.g. C1 → submit the contact form; C4 → inspect the search box; C5 → confirm the portfolio feature truly isn't invoked).
6. **All pass →** commit that one removal (message names what was removed + the evidence). **Any fail →** `git checkout` the file (revert), record the candidate as load-bearing in the spec/PR, move on.

**Verification tooling:** primary = chrome-devtools MCP (screenshots, `list_console_messages`, `list_network_requests`). The port's `scripts/verify/` (dom_normalize, check_404) remain available as a secondary cross-check. **Note:** unlike the port, byte/DOM-identity is *not* the gate here — removing dead code intentionally changes the DOM; the gate is **pixel + behavior identity**.

**Where:** all removals accumulate as separate commits on `wp-cleanup`; one PR at the end lists each removal + its evidence (and any reverted candidates). Mohamed reviews and merges. New commits on this repo carry **no** `Co-Authored-By: Claude` trailer (per Mohamed's standing preference).

## 5. Architecture / files touched

Only these existing files are edited (removals): `layouts/partials/head.html` (C2, C4), `layouts/partials/section-contacts.html` (C1), `layouts/partials/tail-scripts.html` (C3, C5), and possibly deleting now-unreferenced static assets (`static/vendor/wpjs/dist/vendor/*.js` for C3, `static/vendor/wpjs/block-library/*` for C4, `static/theme/assets/js/ajax-portfolio-content.js` for C5) **only after** their `<script>`/`<link>` references are gone and verification passes. No new files. No template logic changes.

## 6. Testing / success criteria

- Each landed removal has attached evidence: baseline-vs-after screenshot pair (identical), console clean, network clean, functional check noted.
- Reverted candidates are documented with *why* they were load-bearing.
- Final state: `wp-cleanup` PR green (build + link-check CI pass), site pixel-identical to `main`, ready for Mohamed's review.
- Rollback: the PR is not merged until approved; even post-merge, `git revert` restores any removal cleanly.

## 7. Risks & mitigations

| Risk | Mitigation |
|---|---|
| A "dead" thing is actually consumed (like C5's config, or C4's search styling) | prove-it-dead gate reverts it; ordered safest-first so easy wins land before risky ones |
| Chrome-MCP screenshots catch animation noise (typed.js, video) as false diffs | known-noisy regions are excluded from the diff judgment (same list the port established) |
| Removing a `<script>`/`<link>` but leaving the orphaned static file (or vice-versa) | delete the static asset only after its reference is gone and verification passes; zero-404 check catches a dangling reference |
| Scope creep into the load-bearing layer | §2 out-of-scope list is explicit; touching it requires a separate future spec |

## 8. Not now (future sub-projects, separate specs)

SPA blog navigation; blog authoring workflow; comments on posts. Each will be brainstormed and specced on its own after this cleanup ships.

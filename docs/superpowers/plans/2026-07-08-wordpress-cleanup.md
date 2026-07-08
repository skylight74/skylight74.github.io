# WordPress Dead-Cruft Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove dead, unused WordPress/plugin artifacts from the shipped Hugo site with zero visual and zero behavioral change — each removal *proven* dead, never assumed.

**Architecture:** Prove-it-dead incremental removal on branch `wp-cleanup`. One candidate per task/commit. A subagent performs the mechanical removal + build + deterministic checks (grep, `hugo`, zero-404). The controller performs the visual/behavioral gate (chrome-devtools MCP: pixel diff vs baseline + console + network) as the between-task review. Any candidate that fails any gate is reverted and recorded as load-bearing.

**Tech Stack:** Hugo 0.163.3 extended; chrome-devtools MCP (real Chrome on 127.0.0.1:9222) for visual/console/network verification; `scripts/verify/check_404.py` (from the port) for the deterministic link gate; Python http.server to serve the build.

**Spec:** `docs/superpowers/specs/2026-07-08-wordpress-cleanup-design.md`.

## Global Constraints

- Branch: `wp-cleanup` (exists, cut from `origin/main`@`b32d3d5`). Never commit to `main`.
- **No `Co-Authored-By: Claude` trailer** on any commit (Mohamed's standing preference for this repo).
- Hugo pinned **0.163.3 extended**; never `--minify`.
- Serve build for verification: `hugo && python3 -m http.server 8082 --bind 127.0.0.1 --directory public`.
- **The gate is PIXEL + BEHAVIOR identity, NOT byte/DOM identity** — removing dead code intentionally changes the DOM. A removal passes only if: (a) every page renders pixel-identical to the Task-0 baseline (excluding known animation-noise: typed.js subtitle text, background-video frame), (b) no new console errors, (c) no new failed network requests, (d) the affected behavior still works.
- **Revert, never force:** a candidate failing any gate is `git checkout`-reverted and recorded in `verification/cleanup-notes.md` + the final PR as "load-bearing, kept."
- Delete an orphaned static asset only AFTER its `<script>`/`<link>` reference is removed and the gate passes; the zero-404 check catches a dangling reference.
- **Load-bearing — DO NOT TOUCH:** Elementor (`/vendor/elementor`, `elementor-*`, `elementorFrontendConfig`, `/theme/elementor-css/*`), RyanCV theme (`/theme/*`, `/vendor/ryancv-widgets`), jQuery stack (`/vendor/wpjs/jquery*`), and structural classes `wpcf7-*`, `menu-item*`, `page_item*`, `wp-container*`, `sidebar-wrap`, `wp-block-search` markup.
- `verification/` is gitignored; never commit it or `public/`.

### The gate (exact procedure, referenced by every candidate task)

Deterministic half (subagent runs; must pass before requesting review):
```bash
cd /home/mohamed/Projects/skylight74.github.io
rm -rf public && hugo --quiet 2>&1 | tail -3            # build must succeed
python3 -m http.server 8082 --bind 127.0.0.1 --directory public >/dev/null 2>&1 & SRV=$!
sleep 1
python3 scripts/verify/check_404.py http://127.0.0.1:8082 /blog/ /search/ ; echo "404 exit $?"  # must be 0
grep -rn '<REMOVED-MARKER>' layouts/ ; echo "residual grep exit $? (expect 1 = gone)"   # target string absent
kill $SRV 2>/dev/null
```

Visual/behavioral half (controller runs via chrome-devtools MCP, as the task review):
- Serve `public/` on 8082; for each page URL in the baseline set, `navigate_page` → `take_screenshot` (full page) at desktop (`resize_page` 1440-wide) and mobile (390-wide); compare to the Task-0 baseline image for that page/width. Identical (bar animation noise) = pass.
- `list_console_messages` → no errors absent from baseline.
- `list_network_requests` → no failed (4xx/5xx) request absent from baseline.
- Exercise the affected behavior (per task).

Baseline page set (used everywhere): `/` , `/#resume-e` , `/#contacts-e` , `/blog/` , `/blog/hello-hugo/` , `/search/?s=hugo` , `/404.html`.

## File Structure

Only existing files are edited (removals); no new source files.
```
layouts/partials/head.html             C2 (style block), C4 (link)
layouts/partials/section-contacts.html C1 (_wpcf7 hidden inputs)
layouts/partials/tail-scripts.html     C3 (polyfill scripts), C5 (portfolio ajax config + script tag)
static/vendor/wpjs/dist/vendor/*.js    C3 (delete after refs gone)
static/vendor/wpjs/block-library/*     C4 (delete after ref gone, if removed)
static/theme/assets/js/ajax-portfolio-content.js  C5 (delete after ref gone, if removed)
verification/cleanup-baseline/         Task 0 (gitignored) — baseline screenshots + console/network dumps
verification/cleanup-notes.md          running record of pass/revert per candidate (gitignored)
docs/superpowers/specs/…               spec (already committed)
```

---

### Task 0: Capture verification baseline

**Files:** Create (gitignored): `verification/cleanup-baseline/`, `verification/cleanup-notes.md`

**Interfaces:**
- Produces: the reference screenshots + console/network snapshots every later task's visual gate diffs against.

- [ ] **Step 1: Confirm chrome-devtools MCP is live**

Controller: `list_pages` (chrome-devtools MCP). If it errors (Chrome not on 127.0.0.1:9222), STOP and ask Mohamed to open Chrome with remote debugging, OR fall back to the port's Firefox capture (`scripts/verify/capture.sh`, home-captures-reliable caveat) and note the fallback in `cleanup-notes.md`.

- [ ] **Step 2: Build + serve current `main` state (no removals yet)**

```bash
cd /home/mohamed/Projects/skylight74.github.io
git rev-parse --short HEAD   # expect b32d3d5 (branch tip = origin/main, nothing removed yet)
rm -rf public && hugo --quiet
python3 -m http.server 8082 --bind 127.0.0.1 --directory public >/dev/null 2>&1 &
sleep 1
```

- [ ] **Step 3: Capture baseline for every page at desktop + mobile**

Controller, via chrome-devtools MCP, for each URL in the baseline page set:
- `resize_page` 1440×2400 → `navigate_page` `http://127.0.0.1:8082<path>` → `take_screenshot` (fullPage) → save to `verification/cleanup-baseline/<name>-1440.png`
- `resize_page` 390×2400 → re-navigate → `take_screenshot` → `verification/cleanup-baseline/<name>-390.png`
- On the home page: `list_console_messages` → save to `verification/cleanup-baseline/console-home.txt`; `list_network_requests` → save request list + statuses to `verification/cleanup-baseline/network-home.txt`

Expected: 14 PNGs (7 pages × 2 widths), all real full-page renders (not blank); console has no errors; network has no 4xx/5xx.

- [ ] **Step 4: Record + stop server**

Write `verification/cleanup-notes.md` header: date, baseline HEAD `b32d3d5`, the page set, the known-noise regions (typed.js subtitle, bg video). `kill` the http server. `git status --porcelain` must be empty (verification/ gitignored) — nothing to commit.

---

### Task C1: Remove dead `_wpcf7*` hidden form fields

**Files:** Modify `layouts/partials/section-contacts.html`

**Interfaces:** none new.

- [ ] **Step 1: Confirm the targets**

```bash
grep -n 'name="_wpcf7' layouts/partials/section-contacts.html
```
Expected: 6 hidden `<input>` lines: `_wpcf7`, `_wpcf7_version`, `_wpcf7_locale`, `_wpcf7_unit_tag`, `_wpcf7_container_post`, `_wpcf7_posted_data_hash`. All are `type="hidden"`.

- [ ] **Step 2: Remove the 6 hidden inputs**

Delete exactly those 6 `<input type="hidden" name="_wpcf7*" …>` lines. Keep the Formspree `_gotcha` honeypot and `_subject` inputs (those are NOT `_wpcf7*`), and every visible field (`your-name`, `your-email`, `your-message`) and the submit button.

- [ ] **Step 3: Deterministic gate**

Run the Global-Constraints deterministic gate with `<REMOVED-MARKER>` = `name="_wpcf7`. Expected: build ok; `check_404` exit 0; `grep` exit 1 (all 6 gone).

- [ ] **Step 4: Controller visual/behavioral gate (review)**

Controller: run the visual half. All pages pixel-identical (the fields were hidden → zero visual change). Console clean, network clean. Behavior: on `/#contacts-e`, `fill_form` name/email/message + confirm the form still has `action="https://formspree.io/f/mwvdzgqv"` and submits (do NOT actually send unless coordinating a live test). Record pass in `cleanup-notes.md`.

- [ ] **Step 5: Commit**

```bash
git add layouts/partials/section-contacts.html
git commit -m "Remove dead Contact Form 7 hidden fields (unused; Formspree ignores them)"
```

---

### Task C2: Remove dead Gutenberg `global-styles-inline-css` block

**Files:** Modify `layouts/partials/head.html`

**Interfaces:** none new.

- [ ] **Step 1: Confirm dead**

```bash
grep -n 'global-styles-inline-css' layouts/partials/head.html   # the <style id="global-styles-inline-css"> block
grep -roE 'class="[^"]*(has-[a-z-]+-color|has-[a-z-]+-background-color|wp-block-[a-z]+)' layouts/ public/ 2>/dev/null | grep -v 'wp-block-search\|wp-block-latest\|wp-block-categories\|wp-block-group' | wc -l
```
Expected: the style block exists; the second grep = 0 (no element uses the preset color/`wp-block-*` classes this block defines — excluding the search/latest-posts/categories/group block markup which is styled elsewhere, not by these preset vars).

- [ ] **Step 2: Remove the block**

Delete the entire `<style id="global-styles-inline-css" type="text/css"> … </style>` block (the one with `--wp--preset--*` custom properties and `.has-*-color`/`.has-*-background-color` rules). Leave every other `<style>`/`<link>` in `head.html` intact (especially the design-critical customizer block with `#292922`).

- [ ] **Step 3: Deterministic gate**

Global gate, `<REMOVED-MARKER>` = `global-styles-inline-css`. Build ok; 404 exit 0; grep exit 1.

- [ ] **Step 4: Controller visual/behavioral gate**

All 7 pages × 2 widths pixel-identical (classes were unused → no change). Console/network clean. Record pass.

- [ ] **Step 5: Commit**

```bash
git add layouts/partials/head.html
git commit -m "Remove unused Gutenberg global-styles color CSS (no element uses these classes)"
```

---

### Task C3: Remove old-browser WordPress polyfills

**Files:** Modify `layouts/partials/tail-scripts.html`; delete `static/vendor/wpjs/dist/vendor/wp-polyfill.min.js`, `static/vendor/wpjs/dist/vendor/regenerator-runtime.min.js`

**Interfaces:** none new.

- [ ] **Step 1: Confirm no consumer references the polyfilled globals**

```bash
grep -rn 'wp-polyfill\|regenerator-runtime' layouts/
grep -rniE 'regeneratorRuntime|wp\.polyfill|Promise\.allSettled|fetch\(' static/vendor/ryancv-widgets static/theme/assets/js 2>/dev/null | head
```
Expected: two `<script src>` tags in `tail-scripts.html`. The second grep is informational — `fetch`/modern APIs are natively supported in the Chrome/Firefox baseline, so the polyfills are inert; note any hit for the review to judge. (These polyfills exist only for very old browsers WordPress supported; the site's own JS is jQuery-era and does not need them.)

- [ ] **Step 2: Remove the two script tags**

Delete the `<script src="/vendor/wpjs/dist/vendor/wp-polyfill.min.js…">` and `<script src="/vendor/wpjs/dist/vendor/regenerator-runtime.min.js…">` lines from `tail-scripts.html`. Do NOT touch the jQuery / jquery-migrate / jquery-ui `<script>` tags (load-bearing).

- [ ] **Step 3: Deterministic gate**

Global gate, `<REMOVED-MARKER>` = `wp-polyfill\|regenerator-runtime` (grep `-E`). Build ok; **404 exit 0** (this proves nothing still requests the removed files); grep exit 1.

- [ ] **Step 4: Controller visual/behavioral gate**

All pages pixel-identical. **Console: no new errors** (a missing polyfill would surface as a ReferenceError — watch closely). Network: the two files are no longer requested; no 404. Behavior: exercise nav, form, blog, search, typed.js — all must still work. Record pass.

- [ ] **Step 5: Delete the orphaned files + commit**

```bash
git rm static/vendor/wpjs/dist/vendor/wp-polyfill.min.js static/vendor/wpjs/dist/vendor/regenerator-runtime.min.js
git add layouts/partials/tail-scripts.html
git commit -m "Remove unused WordPress old-browser polyfills (wp-polyfill, regenerator-runtime)"
```
Then re-run Step 3's gate once more (post-delete) to confirm still 404-clean.

---

### Task C4: Test-remove `wp-block-library` stylesheet (RISKY — may revert)

**Files:** Modify `layouts/partials/head.html`; conditionally delete `static/vendor/wpjs/block-library/style.min.css`

**Interfaces:** none new.

- [ ] **Step 1: Note the risk**

The sidebar search box uses `wp-block-search*` classes (9× in the built home page). `wp-block-library/style.min.css` is Gutenberg's block stylesheet and MAY provide the search box's layout/appearance. This task is a *hypothesis test*: remove, and if the search box (or any block-styled element) changes, REVERT.

- [ ] **Step 2: Remove the stylesheet link**

Delete the `<link … href="/vendor/wpjs/block-library/style.min.css…">` line from `head.html`.

- [ ] **Step 3: Deterministic gate**

Global gate, `<REMOVED-MARKER>` = `block-library/style.min.css`. Build ok; 404 exit 0; grep exit 1.

- [ ] **Step 4: Controller visual/behavioral gate — FOCUS ON THE SEARCH BOX**

Controller: capture all pages. **Pay special attention to the hamburger-sidebar search box** (open the sidebar via `click` on the menu button, screenshot the search input + button). Compare to baseline. Also check the Recent-Posts / Categories widget lists (they use `wp-block-latest-posts` / `wp-block-categories` classes).
- **If pixel-identical everywhere →** it was dead: proceed to Step 5.
- **If ANY block-styled element changed →** it was load-bearing: `git checkout layouts/partials/head.html`, record in `cleanup-notes.md` "C4 wp-block-library: KEPT — styles the search box/widgets", and SKIP Steps 5–6.

- [ ] **Step 5: Delete orphaned file (only if Step 4 passed)**

```bash
git rm -r static/vendor/wpjs/block-library
git add layouts/partials/head.html
git commit -m "Remove unused Gutenberg block-library stylesheet (verified no block element depends on it)"
```

- [ ] **Step 6: Re-run gate post-delete** — confirm 404-clean and search box still correct.

---

### Task C5: Test-remove portfolio-ajax config + script (RISKY — may revert)

**Files:** Modify `layouts/partials/tail-scripts.html`; conditionally delete `static/theme/assets/js/ajax-portfolio-content.js`

**Interfaces:** none new.

- [ ] **Step 1: Prove the portfolio/isotope ajax feature is unused on this site**

```bash
grep -n 'portfolio_ajax_loading_data\|ajax-portfolio-content' layouts/partials/tail-scripts.html
grep -rniE 'portfolio|isotope|grid-items|works|data-filter' layouts/ | grep -iv 'port ' | head
```
The config `var portfolio_ajax_loading_data = {…}` is consumed by `ajax-portfolio-content.js`. That script only does anything if the page has a portfolio/works grid it can ajax-load. This site has no portfolio section (only About/Resume/Contact/Blog). If the second grep shows no portfolio/works grid markup, the feature is inert and BOTH the config and script can go together. If a portfolio grid exists, KEEP both.

- [ ] **Step 2: Remove config + script tag together**

If Step 1 confirms no portfolio feature: delete the inline `<script … id="ajax-portfolio-content-js-extra">var portfolio_ajax_loading_data = …</script>` block AND the `<script src="…/ajax-portfolio-content.js…">` tag from `tail-scripts.html`.

- [ ] **Step 3: Deterministic gate**

Global gate, `<REMOVED-MARKER>` = `portfolio_ajax_loading_data\|ajax-portfolio-content`. Build ok; 404 exit 0; grep exit 1.

- [ ] **Step 4: Controller visual/behavioral gate**

All pages pixel-identical. **Console: no new errors** (removing a script other code calls would throw). Network: script no longer requested. Behavior: full nav/form/blog/search/typed.js pass.
- **Pass →** Step 5. **Any regression →** `git checkout layouts/partials/tail-scripts.html`, record "C5 portfolio-ajax: KEPT — <reason>", SKIP Steps 5–6.

- [ ] **Step 5: Delete orphaned script (only if Step 4 passed)**

```bash
git rm static/theme/assets/js/ajax-portfolio-content.js
git add layouts/partials/tail-scripts.html
git commit -m "Remove unused portfolio-ajax loader (site has no portfolio grid to load)"
```

- [ ] **Step 6: Re-run gate post-delete** — confirm 404-clean.

---

### Task 6: Final sweep, evidence, PR

**Files:** none (creates the PR).

**Interfaces:** consumes every prior task.

- [ ] **Step 1: Full-suite re-verify on the final tree**

```bash
rm -rf public && hugo --quiet
python3 -m http.server 8082 --bind 127.0.0.1 --directory public >/dev/null 2>&1 & SRV=$!
sleep 1
python3 scripts/verify/check_404.py http://127.0.0.1:8082 /blog/ /search/ ; echo "exit $?"   # 0
grep -rn 'wp-content\|wp-includes' layouts/ static/ ; echo "wp-path grep exit $? (expect 1)"
kill $SRV
```
Controller: one final chrome-MCP pass over all 7 pages × 2 widths vs the Task-0 baseline — the WHOLE cleanup must net pixel-identical to where we started.

- [ ] **Step 2: Summarize evidence**

Write the PR body from `cleanup-notes.md`: for each C1–C5 — removed or KEPT(reason), with the gate evidence. State the net result (site pixel-identical; N dead artifacts removed; M candidates kept as load-bearing).

- [ ] **Step 3: Push + open PR**

```bash
git push -u origin wp-cleanup
gh pr create --base main --head wp-cleanup --title "Remove dead WordPress cruft (pixel-identical)" --body-file <(cat verification/cleanup-notes.md)
```

- [ ] **Step 4: CHECKPOINT (Mohamed)** — review the PR (evidence + diff) and merge when satisfied. Do not merge for him. Post-merge, the Pages deploy re-runs; confirm the live site is unchanged.

---

## Self-review (authoring time)

- **Spec coverage:** R1 (pixel) + R2 (console/404) + R3 (behavior) → the gate in every task; R4 (revert) → C4/C5 Step 4 branches + `cleanup-notes.md`; R5 (main untouched) → branch + PR checkpoint. §2 candidates C1–C5 → Tasks C1–C5. §4 baseline → Task 0. Out-of-scope list → Global Constraints "DO NOT TOUCH".
- **Placeholder scan:** the `<REMOVED-MARKER>` token is a documented per-task substitution (each task states its value), not a TBD. No other placeholders.
- **Consistency:** gate procedure defined once in Global Constraints, referenced by name; page set defined once; file paths consistent with the spec's §5.
- **Right-sizing:** each C-task is one removal + one gate + one commit — independently reviewable and revertible. Task 0 is shared setup (baseline). Task 6 is the integration/PR gate.

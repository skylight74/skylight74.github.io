# Content lane notes — skylight-content, branch content-sync-cv

Owner: content-lane agent. Scope: data/*.yaml and content/blog/*.md only.
Layouts, static/css, job-hunt files are off-limits — see relay list at bottom.

**ROLE BOUNDARY (Mohamed, 2026-07-10, direct correction):** sync information
+ clean up the website content. Do NOT make or propose design/IA decisions —
that includes page splits, nav groupings, "which section goes where"
proposals, anything about page structure. That's the rebuild lane's call
entirely (and Mohamed's to approve), even as a mere suggestion. This came up
because the resume-page-split spec sent to team-lead (new Skills anchor,
section groupings) was itself an IA decision, even though no layout file was
touched. If asked about structure/placement going forward: describe what
content exists and its facts, decline to recommend where it should live.

## Local preview

Dev server running: `hugo server --bind 127.0.0.1 --port 1313` in background,
watches data/ and content/blog/ and auto-rebuilds. Preview at
http://localhost:1313/ . Not started via a shell you can Ctrl-C; if it's not
responding, kill and restart: `pkill -f "hugo server"` then rerun the command
above from /home/mohamed/Projects/skylight-content.

## Status log

**2026-07-10** — session start. Read shared memory (user_profile, calibration,
career state, cv_system, humanize-rules) and base-cv.yaml (source of truth).
Reviewed worktree state at commit 5b73c0a + 2 uncommitted edits left by a
prior agent.

Reviewed and KEPT both prior uncommitted edits (they're correct):
- `data/resume.yaml`: restored Boraq-Group entry (Jul 2018 - Sep 2018,
  Business Development Analyst) — matches LinkedIn per its own comment.
- `data/services.yaml`: new file, 3 service offerings, no pricing, exactly-true
  to base-cv.yaml. Not wired to any template yet (relay item, see below).

Full sync pass against base-cv.yaml, done:
- `data/resume.yaml` Freelance entry: restored "PHP/Laravel" (base-cv names the
  stack; site copy had dropped it).
- `data/resume.yaml` Dolusoft entry: was missing base-cv's second bullet
  ("Designed chained, first-seen, and z-score anomaly-detection rules...") —
  added, now 3 of 3 bullets present.
- `data/resume.yaml` Interprobe entry: was missing base-cv's third bullet
  ("Trained 4 engineers and ASELSAN partners...GISEC Dubai") — added, now 3 of
  3 bullets present.
- `data/profile.yaml`, `data/contact.yaml`: checked against memory (name,
  phone, email, GitHub handle) — already correct, no changes needed.
- `content/blog/hello-hugo.md`, `content/blog/_index.md`: checked for stale
  claims / privacy leaks — clean, no changes needed.
- Verified after each edit: `hugo --minify` exits 0, and grepped the rendered
  homepage HTML to confirm the new bullet text actually appears (not just
  that the build didn't error).

Committed: see git log on this branch for the exact commits, small units,
each hugo-build-verified before committing.

## OPEN QUESTIONS for Mohamed

1. **Boraq dates (carried over from prior agent, still open):** LinkedIn says
   Jul-Sep 2018, the old WordPress site said Jun-Sep 2018. Site currently uses
   "Jul 2018 - Sep 2018" (LinkedIn's version) pending your confirmation of
   which is correct.

2. **NEW — Interprobe education entry date contradiction:** `data/resume.yaml`
   education section has "Cyber security specialty program" / "Certificate
   preparation program at Interprobe" dated **May 2021 - Nov 2021**. This
   entry is not in base-cv.yaml and not in any memory file, so I can't verify
   it independently. It also has a date problem: it starts May 2021, two
   months *before* your attested Interprobe employment start date (Jul 2021).
   A prior agent's comment says this was "synced 2026-07-09... titles and
   dates identical across all three surfaces" (implying it matches LinkedIn),
   but I have no direct evidence of that beyond the comment. Two questions:
   was this a real pre-employment certificate program (in which case the
   overlap with your Jul 2021 start is just normal onboarding timing and it's
   fine as-is), or does something need correcting? I left it untouched since
   it's pre-existing content, not something I introduced, and removing it
   would be its own unverified assertion.

## Relay to the REBUILD (layouts) session — do not fix here, content lane is banned from layouts/

1. **`layouts/partials/section-about.html:117`** — "The Legend." dead branding
   string, hardcoded, not data-driven. (Already known per task brief.)

2. **`layouts/partials/tail-scripts.html:45`** — elementor JSON blob has
   `"title": "The%20Legend%20%E2%80%93%20My%20portifolio"` (URL-encoded "The
   Legend – My portifolio", misspelled). Hardcoded JS config object, not a
   data file. (Already known per task brief.)

3. **`layouts/partials/section-resume.html`** — CONFIRMED the exact mechanism
   of the known logo-mismatch bug: the template hardcodes exactly 5
   `<img src="...">` tags by array index for experience (0-4) and pairs a
   fixed image with each `site.Data.resume.experience N` slot regardless of
   what's actually in that slot. Currently: index 2's hardcoded image is
   `/media/Boraq-Group_.png` but data index 2 is METU; index 3's hardcoded
   image is `/media/ApplyCenter-Logo.png` but data index 3 is Interprobe. The
   newly-restored Boraq entry is at data index 4, paired with a generic stock
   image. No data-side fix is possible — reordering the array would break
   base-cv-consistent reverse-chronological order and still not fix it,
   because the images are keyed to position, not content. Needs a
   `{{ range }}` loop keyed to a `logo:` field in the data file (the `logo:`
   field already exists on the Boraq entry, added for exactly this future
   rework — see resume.yaml comment).

   Same hardcoded-index pattern exists for education: template has 4
   hardcoded `<img alt="">` blocks (indices 0-3) but `data/resume.yaml` only
   has 2 education entries. Hugo doesn't error on this (`index` returns a
   zero value out of range, confirmed by a clean `hugo --minify` build) but
   indices 2 and 3 render as empty resume-item blocks on the live page —
   cosmetic dead space, not a build breakage.

4. **`layouts/partials/section-resume.html` Skills section — NOT wired to any
   data file at all.** This is a bigger issue than 1-3: the entire "Skills"
   section (Programming Languages, Knowledge, Front-end, Frameworks &
   Libraries, DevSecOps, Languages) is literal hardcoded HTML in the
   template, no `site.Data` reference anywhere in it. It currently lists
   Haskell, Java, Flutter, Django, Flask, Spring, React (again, separate from
   the Front-end React), high fake-precision percentage bars (C/C++ 90%,
   Python 85%, etc. — arbitrary numbers, not attested anywhere), "Web3
   development", and "HoneyPot Deployments". Per shared memory
   (`cv_system.md`, calibration rules): Flutter and "Java-beyond-coursework"
   were explicitly "dropped for good" from his CV; the percentage-bar format
   itself doesn't match his attested skill list in base-cv.yaml at all. This
   is live, public, contradicts his own calibrated CV, and I have no way to
   fix it from data/*.yaml because there's no data file backing it — needs a
   new `data/skills.yaml` + template rewrite, both rebuild-lane scope.
   Flagging this as the highest-priority relay item: it's actively showing
   stale/inaccurate skill claims to anyone who visits the site.

   **UPDATE 2026-07-10: fixed content-side.** Created `data/skills.yaml`
   (7 categories, copied verbatim from base-cv.yaml's skills section, no
   percentage bars, no invented numbers), plus two more canon sections that
   had NO home anywhere on the site at all: `data/honors.yaml` (IChO 2012,
   Türkiye Bursları 2014, MIT Enterprise Forum Pan-Arab 2017) and
   `data/publications.yaml` (AINA 2024 Springer + MedPower 2024). All three
   committed on content-sync-cv, all three still need template wiring —
   rebuild-lane scope, same as services.yaml.

   **DUPLICATION RISK, flagged to team-lead:** Task #1 in the shared task
   list ("Rewrite Skills section: data/skills.yaml + template") is assigned
   to a DIFFERENT worktree (skylight74-native, branch native-rebuild) and its
   description says "Create data/skills.yaml from base-cv.yaml skills list"
   — that's the exact file I already built here. Different worktrees don't
   share a working tree, so my skills.yaml won't appear there automatically.
   Told team-lead so effort isn't duplicated; my version (and honors.yaml,
   publications.yaml) can be copied over or the branches reconciled.

5. **`data/services.yaml` needs template wiring** (already known per task
   brief) — content is ready, no partial renders it yet.

6. **NEW REQUEST (2026-07-10) — "split the resume page, it's too long."**
   The resume page (`layouts/partials/section-resume.html`, rendered as one
   long anchor-section `#card-resume-e` on the single-page site) bundles
   Experience, Education, two Skills columns ×3 rows, and a quote block, all
   in one scroll. Splitting this into separate pages/tabs/routes is
   information-architecture + routing + template work — new page templates,
   nav changes, possibly a content-type change. Every option here lives in
   layouts/ (and probably static/css for pagination/tab styling), which is
   this lane's hard ban. Content lane is also scoped to content/blog/*.md
   only, not content/ generally, so even adding new content/*.md pages for a
   split is out of bounds here. Needs the rebuild-lane session. If it's
   useful, content lane can propose a split (e.g., which sections group onto
   which page/tab, in what order) for the rebuild session to execute, but
   won't touch the implementation. Asked team-lead for routing/clarification.

   **SPEC for task #5 (2026-07-10):** confirmed the site's nav
   (`layouts/partials/sidebar-nav.html`) is a simple 4-item anchor-jump menu
   (About → #home-e, Resume → #resume-e, Contact → #contacts-e, Blog → real
   page at /blog/) — no client-side router, no page templates beyond the
   existing card pattern. Adding a split is the same shape of change as the
   4 items that already exist: a new anchor id + a new nav `<li>`, not a new
   routing system. Proposal:

   - **Keep "Resume" (#resume-e) to Experience + Education only.** That's
     already a reasonable length (5 experience entries, 2 education entries)
     once Skills is pulled out. Drop the "Favorite Quote" spacer block from
     this page too if convenient — it's generic filler unrelated to resume
     content, not sourced from any canon fact — but that's a nice-to-have,
     not a requirement.
   - **New "Skills" nav item (new anchor, e.g. #skills-e)** holding: Skills
     (`data/skills.yaml`, 7 categories, already committed), then Publications
     (`data/publications.yaml`, 2 entries) and Honors (`data/honors.yaml`,
     3 entries) underneath as two short lists. All three are "credentials"
     in nature and, once the fake percentage bars are dropped per task #1,
     together they're still shorter than the current Skills block alone.
   - **Services (`data/services.yaml`, task #3) is a separate concern** —
     not part of the "resume too long" complaint, already has its own task,
     no opinion on where it lands (About page or its own nav item both work).
   - Same advice as task #1: whatever replaces the current Skills markup,
     build it with a `{{ range }}` loop over the data file, not fixed-index
     hardcoding — avoids reproducing the logo/index bug from task #2.

   Sent this spec to team-lead via SendMessage so task #5 can unblock.

## Original brief checklist — status as of 2026-07-10

Every item team-lead named explicitly, checked against base-cv.yaml/memory:
- Experience (titles, dates, bullets) — synced, 3 bullets restored.
- Education (graduation year 2023 only) — already correct; one open
  question on the Interprobe cert-program entry (see above).
- Publications (AINA 2024 Springer, never IEEE; MedPower 2024) — was
  missing entirely, now in data/publications.yaml.
- Honors (IChO 2012, Türkiye Bursları 2014, MIT Pan-Arab 2017) — was
  missing entirely, now in data/honors.yaml.
- Languages (Arabic native, English C1, Turkish B2) — was wrong on-site
  (hardcoded "German" line, invented percentages), corrected inside
  data/skills.yaml's Languages category; site-side fix is rebuild-lane.
- Skills — was hardcoded/wrong (Haskell, Java, Flutter, Django/Flask/
  Spring, fake percentages), now in data/skills.yaml, 7 canon categories.
- Contact info (phone/email/name) — already correct, verified, no change.
- Projects (base-cv.yaml has SmartGridLedger + Real-Time Detection Engine)
  — checked: NOT a gap. git history shows a portfolio grid was deliberately
  removed as unused (commit 9c72d63, "Remove unused portfolio-ajax loader
  (site has no portfolio grid; binds to 0 elements)"). Not rebuilding this
  without Mohamed asking for a portfolio section back.
- content/blog/*.md — checked for stale claims/privacy leaks, clean.

Everything on the checklist now has accurate content either live or staged
in data/*.yaml, ready for template wiring. Remaining work is rebuild-lane
(tasks #1-5) plus Mohamed's answers to the 2 open questions above.

## Do-not-touch reminders (self, for continuity across runs)
- layouts/, static/css/, any template file — hard ban, rebuild lane owns it.
- job-hunt files under /home/mohamed/Projects/CV/job-hunt/ — off-limits.
- Never push. Commits stay local on content-sync-cv for Mohamed's hand only.

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

## OLD-CONTENT AUDIT (2026-07-10) — main worktree = actual live mohamedalyamin.com

Mohamed asked to investigate "the old content of the website" via the live
site or the actual main worktree (`/home/mohamed/Projects/skylight74.github.io`,
branch `main`). Important discovery: content-sync-cv is NOT a clean diff of
main — a prior WIP commit (5b73c0a, before this lane split existed) already
fixed several things on content-sync-cv (About text, DOB script removal, old
services removal, page titles, typing-title rotator, the address map embed)
that are STILL LIVE on main/production, unfixed. Verified via direct file
diff (not just curl/WebFetch — a WebFetch AI-summary of the live page was
cross-checked against raw curl + direct git diff of main vs content-sync-cv
to avoid trusting a single unreliable probe).

### CRITICAL — live privacy leaks on mohamedalyamin.com right now
1. **DOB/age-calculator script**, `layouts/partials/section-about.html`
   (main): a hidden `<input id="dob" value="1995-12-10">` plus JS that
   computes and displays "Age: XX" live on the page. Exposes his exact
   birthdate to every visitor. Already removed on content-sync-cv/
   native-rebuild's version of this file; NOT removed on main.
2. **Home address Google Maps embed**, `layouts/partials/section-contacts.html`
   (main): HTML-commented-out (not visually rendered) but the full iframe
   `src` with a street-level address (İşçi Blokları, 1523. Sk. No:7,
   Çankaya/Ankara — his old, now-vacated address) is still in page SOURCE,
   visible to anyone who views-source. Comments don't hide from view-source.
   Already absent entirely on content-sync-cv; still present on main.

### Dead "The Legend" branding, confirmed exact locations
- `hugo.toml` site title (main only — already fixed on content-sync-cv)
- `layouts/partials/head.html` `<title>` + RSS feed title (main only)
- `layouts/404.html` title + fake terminal prompt "root@the-legend" (main only)
- `layouts/partials/tail-scripts.html` elementor JSON `"title": "The%20Legend...My%20portifolio"` (misspelled) — **present on BOTH main and content-sync-cv, not yet fixed anywhere.** Confirmed identical file on both branches.
- The one-word testimonial "The Legend." from Özgul Doğan (real named
  colleague, Cyber Security Expert at InterProbe) — the sentiment is
  presumably genuine, but a real quote (or dropping the testimonial) would
  read better than the in-joke. His call.

### Stale professional identity (fixed on content-sync-cv, still live on main)
- Old About paragraph: "Full stack security developer... rich experience in
  Cloud Computing, Crypto & Cyber Security... good at Big Data-analytics &
  scientific computing" — vague, dated, no DevSecOps/Go/security-ML framing.
- Old typing-title rotator: "Full-stack Developer / Blockchain Architect /
  System Admin / Cyber Security Engineer / Security Researcher" — none of
  his current target titles.

### Stale/wrong experience entries — main's data/resume.yaml (pre-sync)
- Interprobe: "CYBER SECURITY ANALYST", July 2021 - June 2022, with an
  **unedited WordPress template placeholder bullet**: "Collaborate with
  creative and development teams on the execution of ideas." Never
  described his actual work. Already corrected on content-sync-cv.
- "Freelancing / Private Tutor, Oct 2020 - Present" — not on current site
  at all. Bullets: "Day trading", "High Frequency trading app development,
  Open source work", tutoring "Block-chain / Malware development" for
  student graduation projects, private Python/JAVA/SQL lessons. Flagging:
  day-trading/HFT and "malware development" framing is bad optics for a
  security professional's public site regardless, and day-trading/HFT
  sits in the grey-to-excluded zone of his own halal constraints (maysir-
  adjacent) — recommend this entry never gets revived in any form, his
  call to confirm.
- Boraq dates here say "Jun 2018 - Sep 2018" (vs "Jul" now on site) — this
  is the actual SOURCE of the Jun/Jul discrepancy noted earlier. Given
  linkedin-checklist.md's 2026-07-07 LIVE-VERIFIED LinkedIn read says
  Jul-Sep 2018, and this WordPress content is much older/unmaintained,
  treating LinkedIn as the tiebreaker — Jul is very likely correct, "Jun"
  here is probably just old WordPress drift. Still his final call.
- "System Admin, Apply Center (Startup), JUN 2019 – Jan 2020", bullet
  "Deploying CRM's and Setting up An e-commerce website." **Conflicts with
  memory**, which has "Sales/IT Manager 2019-20 (Odoo/Zoho ERP)" for the
  same company/period — different title, different described work. This
  entry doesn't exist on the currently-synced site at all (unlike Boraq,
  it was never restored). Genuine discrepancy — needs his input on which
  title/description is accurate, and whether he wants this entry back at
  all (parallel to the Boraq restore decision).
- "CHAIRMAN, METU INTERNATIONAL STUDENT ASSOCIATION, April 2016 - Sep 2018"
  — bullet reads "Optimize website and apps performance using latest
  technology," which describes a dev role, not chairman/leadership duties.
  Clearly a copy-paste artifact. Also note: linkedin-checklist.md's
  Volunteering entry for the same role says "Apr 2016 – Jan 2019" — a
  different end date than this old entry's "Sep 2018." If this entry is
  ever added back, it needs real bullets (200+ members, 5+ events per
  memory), not this leftover text, and the date conflict needs resolving.

### Stale/unverified education entries — main's data/resume.yaml
- "Cyber security specialty program" at Interprobe, May 2021 - Nov 2021,
  "Certificate prepration [sic] program at Interprobe" — this IS the exact
  source of my earlier open question about this entry. Confirmed: literal
  unedited old WordPress content, typo included, never cross-checked
  against LinkedIn or base-cv.yaml by anyone. Still genuinely open.
- "MIT Enterprise Forum Pan Arab leadership program 2nd edition, Dec 2017 -
  Jan 2018, Jordan, Amman" — POTENTIALLY VALUABLE: more precise dates and
  location than anywhere else currently has for this honor (team-lead's
  brief just says "2017 finalist," no dates/location). But titled here as
  "leadership program" not "finalist" — worth asking him whether these are
  the same event (attending included both a leadership program and a
  finalist result) or something needs reconciling before using the extra
  precision.
- "Royal International Language Schools, 2003-2013, Egypt, Cairo" — a high
  school entry with an informal personal bullet ("Build My first PC when I
  was 12 😛"). Charming, but doesn't belong in a professional Education
  section (base-cv.yaml deliberately went university-only), and combined
  with the 2003 start date is a soft age-signal on top of the DOB leak
  above. Could be fun-fact material if he ever wants personal color
  elsewhere, but recommend NOT reviving in Education regardless.

### Stale/halal-conflicting services (fixed on content-sync-cv, still live on main)
- Old "My Services" section: Web-site Development, Cloud/on-premise
  Deployments, Cyber Security Analysis, Pentesting, "Statistical/Technical
  Analysis" (described as "Crypto currencies tracking, Day & High
  frequency trading"), Scientific Computing & Data analytics. The
  crypto-trading service line directly contradicts his current locked
  halal constraints (excludes maysir/speculation) — should never return.
- Newer-but-still-old "Service Offerings" section: Freelancing tier
  (includes "Web3 Development NEW") and Contract tier (includes
  "Blockchain Security NEW", "Financial Consultancy NEW"). Crypto-general
  positioning contradicts the locked career direction (crypto = niche
  halal-curated tool now, not identity); "Financial Consultancy" doesn't
  match current positioning. The already-drafted `data/services.yaml`
  (DevSecOps & Cloud Security / Go Backend & Streaming / Security
  Assessment & Technical Due Diligence) is a correct, ready replacement.

### Confirmed identical on main and content-sync-cv (i.e., still needs fixing everywhere)
- `layouts/partials/section-resume.html` — byte-identical on both branches.
  The hardcoded Skills section (Haskell, Java, Flutter, Django/Flask/
  Spring, a "German" language line, fake percentage bars) is exactly as
  broken on main as documented earlier. Already staged fix: skills.yaml
  (task #1, in progress on native-rebuild).
- `layouts/partials/tail-scripts.html` — the "The Legend...portifolio"
  elementor JSON title (see above), identical on both branches.

### Genuinely good / worth keeping (verified, not urgent)
- The downloadable CV PDF (`static/media/Mohamed_Aly_Amin_CV.pdf`) is
  BYTE-IDENTICAL between main and content-sync-cv, and is fully current
  and accurate against base-cv.yaml (checked earlier this session). One
  bright spot: the "Download CV" button already serves correct content
  even though the surrounding page is stale.
- Contact info (`data/contact.yaml`, `data/profile.yaml`) — identical on
  both branches, already correct, no privacy issues found there.
- Fun Facts widget (`section-about.html`, unchanged on both branches):
  "Black belt in Karate / Ex-Body builder" (unverifiable, low-stakes, his
  call) · "International Chemistry Olympiad Student (USA)" (consistent
  with attested IChO 2012 fact) · "Fluent in 3 Languages" (consistent) ·
  "5 Countries Visited" (unverifiable, possibly outdated, his call).

### Not touched — main is a third worktree neither content-sync-cv nor
native-rebuild currently owns per the original briefs. This was an
investigation-only pass; no edits made anywhere. Reported directly to
Mohamed given the severity of the two live privacy leaks.

## FINALIZED DECISIONS (2026-07-10) — from Mohamed, ready for whoever executes on main

He answered every item from the questionnaire. **He said NO to me executing
directly on main — route through team-lead first**, same as everything else
in this file. Two items are already done on content-sync-cv (data-file
scope, see commit 45f700c); everything else below lives in layout files on
main and needs the rebuild lane.

**Privacy (do regardless of sequencing):**
- DOB/age script — remove.
- Home address map embed — remove entirely, not just re-comment.

**"The Legend" branding — remove everywhere** (hugo.toml, head.html, 404.html,
tail-scripts.html elementor JSON, RSS feed title).
- Testimonial "The Legend." — remove from the live page. He's open to
  turning it into a blog memoir or just archiving it: "not a big deal,
  especially if it's unprofessional."

**About paragraph — HIS FINAL WORDING, use exactly this:**
> "Security and infrastructure engineer. I work where security meets
> systems: led a six-engineer DevSecOps team, sole-built a real-time Go
> detection engine, and first-authored a peer-reviewed intrusion-detection
> paper."
(Not any of the three drafts I offered — he wrote his own, checked
against the record and it's accurate. Use verbatim.)

**Typing-title rotator** — replace with current target titles (already
confirmed content: DevSecOps Engineer / Security Engineer / Backend
Developer (Go) / Site Reliability Engineer / Solutions Engineer /
Security-ML Researcher — matches what's already live on content-sync-cv
and native-rebuild).

**Old "Freelancing / Private Tutor" entry (day trading, HFT, malware-dev
tutoring)** — ARCHIVE, don't delete outright: "we might find some use for
it sometime." Needs a place to live off the public page (not spec'd where
— that's implementation, not mine to decide).

**Services — corrected from my earlier wrong assumption:**
- "Web3 Development" — KEEP.
- "Blockchain Security" — KEEP.
- "Financial Consultancy" — DO NOT DROP, but genuinely unresolved: he'd be
  recommending investments/companies/startups from his own financial
  experience (he does day trading personally); his own words: "the
  question can I do it professionally I'm not sure." Not a content-lane
  call — needs his own further thought, not a decision to force now.
- Old "My Services" section (day-trading/crypto tracking as a service) —
  no explicit final answer captured on this exact item; his halal
  pushback (see below) applies generally, treat as unresolved rather than
  assume removal.

**Halal/crypto framing — CORRECTION, do not repeat my earlier error:**
I originally flatly said day-trading, Web3, Blockchain Security, and
Financial Consultancy contradict his halal constraints. That was wrong —
pattern-matched from "crypto-adjacent" without basis. His exact words:
"trading and crypto arent all haram btw... as a muslim I'm also required
to not make halal stuff haram." His locked constraints (memory:
feedback/career_direction) are specifically riba/gharar/maysir
(interest, excessive uncertainty, gambling-like speculation) — not
trading or blockchain work in general. Do not re-apply a blanket
crypto-is-bad filter to his content anywhere.

**Fun facts (main's section-about.html, unchanged from original WordPress):**
- "5 Countries Visited" — update to **6** (his count, includes Egypt).
- "Black belt in Karate / Ex-Body builder" — true, keep; he's even open to
  it going on LinkedIn/CV, not just the site.
- IChO + "Fluent in 3 Languages" — keep as-is (already confirmed accurate).

**DONE on content-sync-cv (data-file scope, no main/layout access needed):**
- Boraq dates: Jul confirmed over Jun (his words: "late June and early
  July... we could say July"; also matches LinkedIn's live-verified read).
- Added Apply Center entry: "Sales/IT Manager," Jun 2019 - Jan 2020, Zoho
  CRM administration + social media + Odoo/Zoho ERP work. Wasn't on the
  site before. Worth flagging to him whether base-cv.yaml (a different
  project/lane, not mine to edit) should get this too — it's already
  referenced in memory as used in the solutions-engineer CV variant.
- Dropped the standalone "Cyber security specialty program" education
  entry (May 2021 - Nov 2021). Confirmed real by him (TryHackMe course +
  CEH prep, never completed) but the date range predates his attested
  Interprobe start by 2 months and duplicates the Interprobe experience
  entry; recommended and executed the drop per his delegation ("do what
  you recommend and feel wouldn't push recruiters away") rather than ask
  again. Full reasoning in data/resume.yaml's comment at that spot.
- Confirmed with evidence (not just asserted): the Interprobe placeholder
  bullet ("Collaborate with creative and development teams...") predates
  the Hugo port entirely — the port's spec (docs/superpowers/specs/
  2026-07-07-wordpress-to-hugo-port-design.md) states the only source of
  truth was a byte/DOM-verified WordPress export, so nothing was altered
  by porting. It's original WordPress content, never introduced by Hugo.

**Still open, his own uncertainty, not a blocker:** MIT Enterprise Forum
Pan Arab — was actually a 3-day intensive entrepreneurship program in
January (not the Dec-Jan span old content had); Interprobe cert-program
background (TryHackMe/CEH) kept as attested interview-prep material only,
not public content, per the drop decision above.

## FULL INFORMATION SCAN (2026-07-10) — every file in /home/mohamed/Projects/CV/

Mohamed asked for an exhaustive, >0.9-confidence check that nothing about
him was missed before finalizing site content. Read every remaining file:
prep/dolusoft-architecture.md, prep/red-flag-oneliners.md, prep/
linkedin-rebuild.md (superseded by linkedin-checklist.md but read in full
for anything not carried forward), cover-letters/qblox.md + qblox-form-
answers.md, crypto-field-career-briefing.md, README.md, prompts.txt, the
full legacy master-cv.yaml (486 lines, pre-calibration source), and all 7
legacy profiles/*.yaml tailoring configs.

**Titles resolved (his ask this round):** typing-title rotator gets
"Engineering Lead" and "Tech Lead / Project Manager" (combined into one
line, not two — both grounded: Tech Lead is his real OSSArch title,
overleaf-scan-checklist.md separately lists him as "Project Manager" for
the same team of 6, so combining avoids padding the rotation with the same
role said twice). Relay to rebuild lane alongside the other rotator fix.

**Genuine potential gaps found — none acted on, all need his confirmation:**
1. DIONA "enterprise pilot at Koç Sistem" (Azure Sentinel/Defender phase)
   — appears in both the old CHANGELOG and the rejected Qblox cover letter,
   not in base-cv.yaml or anywhere on the site. Two independent old
   sources, not contradicted anywhere, but never verified fresh.
2. "Claude Copilot - AI Agent Orchestration Framework" project (2025-
   present) — in master-cv.yaml only. Notable: this is literally the
   orchestration framework this multi-agent session runs under. Real and
   current if the "extended... orchestrator-worker pattern" claim holds,
   but unverified by me and not on the site anywhere.
3. evarkadasi "61k+ active listings" metric (master-cv.yaml) — treat with
   skepticism, not endorsement: base-cv.yaml describes the platform as
   "currently in staging," which sits awkwardly next to a specific
   listings-count claim. Smells like pre-calibration inflation (same
   document also claims "100k+ users" for Apply Center's "market reach,"
   which directly contradicts what Mohamed described in his own words this
   session as more modest, unglamorous sysadmin work).
4. TÜBİTAK Grant #120E537 — specific grant number backing both
   publications (AINA 2024 and MedPower 2024), appears in overleaf-scan-
   checklist.md and master-cv.yaml. Could add precision to
   data/publications.yaml if he wants it.
5. AINA paper "presented in Japan" — minor color detail, master-cv.yaml
   only.
6. GDPR and CIS Controls as named compliance-familiarity items — appear in
   the old Qblox cover letter, not in base-cv.yaml's skills list (which
   has ISO 27001, MITRE ATT&CK, PCI DSS/SOC 2 familiarity already).
7. Türkiye Bursları scholarship year: **2014** (linkedin-checklist.md,
   dated later, explicitly supersedes linkedin-rebuild.md, and its own
   "FINAL STATUS" section confirms this was actually typed into LinkedIn
   and verified live) vs **2017** (linkedin-rebuild.md's earlier read,
   superseded). Leaning 2014 as correct given the supersession
   relationship, but flagging the discrepancy rather than silently
   picking one — this is what's already in data/honors.yaml.

**Confirmed correctly excluded — old, wrong, or over-claimed content that
should stay off the site (reassurance, not new gaps):** fake "in progress"
certifications (CISSP, OSCP, AZ-500 — current site correctly says only
"AWS SAA, then CKA, planned 2026, not started"); Sardis staking-contract
authorship overclaim (he QC-reviewed work written by others, already
correctly framed everywhere current); the old Interprobe two-role split
with a wrong Dec 2020 start date (superseded by the live-verified Jul 2021
date); "5+ years experience" framing (contradicts his own honest
mid-level/~3-4-years calibration); embedded/FPGA/QEMU/quantum-adjacent
project material (real work, but off-brand for his current fintech-
security positioning — a deliberate fit decision, not an oversight).

**Privacy-sensitive facts confirmed present in LOCAL PROJECT FILES ONLY,
never live on the website (already-known DOB/address leaks are a separate,
already-flagged website issue):** full legal name (Mohamed Aly Amin Selim
Eissa), birth date, student ID number, an old Egypt phone number — all in
master-cv.yaml and overleaf-scan-checklist.md, both local-only project
files never referenced by any site data or layout file. No new exposure,
just confirming the blast radius is contained to local files.

**Not for the website, context only:** two named professional references
(Prof. Dr. Pelin Angin, METU research supervisor; Eng. Mehmet Celik,
Interprobe Technical Lead) appear in master-cv.yaml's references section
— not something a personal site would publish, noting for awareness only.

**Confidence assessment, stated honestly per-dimension rather than
blended:**
- That every currently-known fact about his 2018-2025 professional history
  is now reflected either on the site or in these notes: ~0.92. Cross-
  checked base-cv.yaml against 15+ independent documents spanning January
  to July 2026 (LinkedIn live-verification passes, GitHub scan, Overleaf
  scan, three job-application cover letters, the full legacy master file);
  every major entry reconciles cleanly except the 7 flagged items above.
- That literally nothing has been missed, full stop: cannot honestly claim
  >0.9 here, and said so directly rather than round up. This has a real
  structural ceiling — these are the project's local files; there's no way
  to verify against facts that live only in his head, in physical
  documents, or in accounts/conversations outside this project directory.
  Did NOT re-verify live LinkedIn or live Overleaf/GitHub via API for this
  pass (relied on the existing 2026-07-07 live-verified LinkedIn checklist
  and the existing Overleaf/GitHub scan checklists, both already thorough
  — self-reported 0.97 confidence on the Overleaf scan, 67 projects and 63
  repos catalogued) — a live re-verification would close some of this gap
  but wasn't done here.
- Did not read: job-hunt/* (off-limits per original brief, and it's
  company/role research, not his personal-bio content anyway);
  immigration-career-plan.md (career/immigration strategy planning, a
  different content category from past professional history — deliberately
  out of scope for a website content audit, not a missed file).


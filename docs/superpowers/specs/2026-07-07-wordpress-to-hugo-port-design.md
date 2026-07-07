# WordPress → Hugo Port — Design Spec

- **Date:** 2026-07-07
- **Status:** Approved by Mohamed (section-by-section, via terminal answers + visual companion selections)
- **Branch:** `hugo-port`, cut from `origin/main` @ `b4d128a` (the live site)
- **Supersedes:** all prior migration attempts (`hugo-rebuild`, `phase-a-sanitize`) — those branches are never read for code or content

## 1. Goal

Replace the static WordPress export that currently serves [mohamedalyamin.com](https://mohamedalyamin.com) with a Hugo site that:

1. **Looks exactly the same** — every visible design element, component, color, font, and animation preserved.
2. **Actually works** — features that are broken on the static export (contact form, search, sidebar widgets, feed) become functional.
3. **Contains nothing of WordPress** in its final state — no `wp-content/`, `wp-includes/`, or WordPress-generated markup paths.

### Non-goals

- Redesigning anything (the blog's post-port polish is a separate follow-up — Mohamed has non-critical edits queued for after a successful port)
- Content rewrites (resume text, about text stay verbatim)
- Performance optimization beyond what the port naturally brings
- Reviving the `base-cv.yaml` sync mechanism from the old attempt

## 2. Source of truth & context

- **The only source of truth** is `origin/main` @ `b4d128a`: a 79-file static export (WP2Static-style) of a WordPress site built on the **RyanCV theme** + **Elementor** + **Contact Form 7**. Verified live at mohamedalyamin.com (GitHub Pages, CNAME present, apex serves HTTP 200, www 301→apex).
- The export is one page: `index.html` (3,112 lines) with Elementor sections anchored `#home-e`, `#resume-e`, `#contacts-e`, plus theme/plugin assets and media uploads (all 8 uploads verified referenced, including a background video and 5 experience-section logos).
- **Design-critical inline state lives in the HTML, not the asset files**: 9 inline `<style>` blocks (incl. customizer CSS: accent `#292922`, body gradient `#292922 → #c7dbe7`) and 3 inline JS config blocks (`elementorFrontendConfig`, `wpcf7`, ajax settings) that the theme JS requires. The prior attempt's green-site drift came from missing these. They are ported verbatim.
- Known original defects (see allow-list): mangled `httplocalhost` URLs (5), dead WP head metadata, non-functional form/search/widgets, title typo.

## 3. Requirements

| # | Requirement |
|---|---|
| R1 | Rendered site is visually identical to the original at desktop/tablet/phone widths (allow-list excepted) |
| R2 | Broken features work: contact form delivers, search returns results, sidebar widgets list real content, feed link serves a real feed |
| R3 | Final `main` contains zero WordPress artifacts (paths, markup remnants, backend references) |
| R4 | Real blog: posts in Markdown, categories, RSS, client-side search, discoverable via a new BLOG rail item |
| R5 | Every port stage ends with a machine-verified invariant; evidence attached to the PR |
| R6 | Deployed on GitHub Pages via Actions; custom domain unchanged; `public/` never committed |

## 4. Approved decisions

| Decision | Choice | How approved |
|---|---|---|
| Fidelity bar | Visual fidelity + fix broken features (not byte-faithful) | terminal |
| Porting approach | Staged hybrid (verbatim → data extraction → de-WordPressify) | terminal |
| Contact form backend | **Formspree** (dashboard archive, no silent loss; ~50/mo free tier to re-verify at implementation) | terminal |
| Blog | Yes — real posts, wired sidebar widgets | terminal |
| CV download | Local PDF served from the site (not Google Drive) | terminal |
| Title typo | Fix: "My portifolio" → "My portfolio" | companion click `typo-fix` |
| Blueprint (§1–2) | Approved | companion click `plan-approve` |
| Stages & verification (§3) | Approved | companion click `stages-approve` |
| Blog doorway | 4th rail item **BLOG**, styled like existing items | companion click `nav-blog-item` |
| Blog list layout | Rows, resume-timeline style | companion click `list-rows` |
| Blog overall (§4) | Approved; non-critical edits deferred post-port | companion click `blog-approve` + terminal |
| Deployment & testing (§5) | Approved | terminal |
| Themed 404 page | Include (allow-list #10) | terminal |

## 5. Architecture

### Repo end state (root of `main` after merge)

```
CNAME                      ← unchanged custom domain
hugo.toml                  ← baseURL https://mohamedalyamin.com/, Hugo 0.163.3 extended (matches CI pin)
layouts/
  _default/baseof.html     ← original <head> verbatim (9 inline style blocks, fonts,
                             CSS/JS order) minus allow-listed removals; body shell
  index.html               ← assembles the one-page portfolio
  _default/list.html       ← blog list (rows style)  + section framing
  _default/single.html     ← blog post (theme's article CSS)
  404.html                 ← themed 404 (allow-list #10)
  partials/
    sidebar-nav.html       ← hamburger + ABOUT/RESUME/CONTACT + new BLOG item
    sidebar-widgets.html   ← search, Recent Posts, Categories — real content
    card-profile.html      ← video bg, avatar, name/subtitle, socials, Download CV
    section-about.html     ← #home-e   (Elementor markup untouched)
    section-resume.html    ← #resume-e (experience + 5 logos + education, untouched)
    section-contacts.html  ← #contacts-e (info blocks + form → Formspree)
content/
  blog/                    ← real posts (.md); seeded with one launch post
data/                      ← Stage 2: resume.yaml, contact.yaml, social.yaml
static/
  theme/…                  ← was wp-content/themes/ryancv/… (moved whole)
  vendor/…                 ← was wp-content/plugins/… + wp-includes/js/…
  media/…                  ← was wp-content/uploads/… + CV PDF
  search-index.json        ← generated by Hugo template, consumed client-side
.github/workflows/deploy.yml
docs/superpowers/specs/    ← this spec + future specs
```

### Porting stages & invariants

| Stage | What happens | Invariant (machine-checked) |
|---|---|---|
| **0 — Ground truth** | Serve untouched export locally; capture screenshots (1440/768/390 px), normalized DOM snapshot, loaded-asset manifest, console errors | reference artifacts exist |
| **1 — Verbatim port** | Carve `index.html` into the templates above; copy assets at **original wp-paths**; apply feature fixes (form, CV, metadata, localhost remnants) | rendered page ≡ Stage 0 except allow-list; zero 404s |
| **2 — Content → data** | Resume/contact/social text moves to `data/*.yaml`; templates interpolate | built HTML **byte-identical** before vs. after |
| **3 — De-WordPressify** | Re-home assets to `/theme`, `/vendor`, `/media` (directories moved whole so CSS-internal `url()` refs survive unedited); update template references; delete WP trees | rendered page identical except path prefixes per mapping table; zero 404s; `grep -ri "wp-content\|wp-includes"` over repo & built site = 0 hits |

Blog + search + 404 are built alongside Stages 1–2 (new surface, no diff target; verified functionally + by Mohamed's review).

### Asset path mapping (Stage 3)

| From | To |
|---|---|
| `wp-content/themes/ryancv/style.css` | `theme/style.css` |
| `wp-content/themes/ryancv/assets/**` | `theme/assets/**` |
| `wp-content/plugins/elementor/**` | `vendor/elementor/**` |
| `wp-content/plugins/contact-form-7/**` | `vendor/contact-form-7/**` (kept for CSS; JS replaced by ours) |
| `wp-content/plugins/ryancv-plugin/**` | `vendor/ryancv-widgets/**` |
| `wp-content/uploads/elementor/css/*` | `theme/elementor-css/*` |
| `wp-content/uploads/2022/08/*` | `media/*` |
| `wp-includes/js/**` | `vendor/wpjs/**` |
| `wp-includes/css/dist/block-library/*` | `vendor/wpjs/block-library/*` |
| `wp-includes/wlwmanifest.xml` | deleted (dead metadata, allow-list #7) |

External URLs unchanged: Google Fonts links (Poppins, Space Grotesk, Roboto/Slab — exact subset/weight query strings preserved), FontAwesome CDN `use.fontawesome.com/releases/v6.1.2`.

## 6. The allow-list (frozen — additions need Mohamed's sign-off)

Every difference between the original and the port must match one of:

1. Title typo fixed: "My portifolio" → "My portfolio"
2. Contact form `action` → Formspree endpoint; CF7 submit JS replaced by a small equivalent keeping inline success/error UI; honeypot anti-spam field added (invisible)
3. Download CV `href` → `/media/Mohamed_Aly_Amin_CV.pdf`
4. Sidebar widgets (Recent Posts, Categories) list real blog content instead of WP demo data
5. Search form `action` → `/search/` (client-side results page); input keeps `name="s"`
6. Head feed link → real Hugo RSS (`/index.xml`)
7. Dead WP head metadata removed: `wp-json`/REST link, `xmlrpc`/EditURI, `wlwmanifest`, oEmbed links, RSD, broken `wp-emoji` inline script + its `s.w.org` dns-prefetch, `<meta name="generator" content="WordPress 6.0.1">`, `shortlink`
8. Remaining `httplocalhost` remnants in inline JS configs replaced with real site-relative values (each substitution documented in the PR)
9. Stage 3 asset paths per mapping table above
10. New surface: BLOG rail item, `/blog/` pages, `/search/` page, themed `404.html`, one seed post + one real category

## 7. Feature fixes — detail

- **Contact form:** markup/classes stay CF7-identical. `action` → `https://formspree.io/f/{FORM_ID}`. Our ~30-line vanilla JS submits via fetch, shows CF7-style inline success/error messages; with JS disabled, plain POST to Formspree's hosted page still delivers. **Input needed from Mohamed: Formspree form ID** (free account; click-by-click instructions will be provided at implementation).
- **Search:** Hugo emits `search-index.json` (title, date, category, plain-text content, URL per post). `/search/` reads `?s=`, filters client-side (vanilla JS, no library unless >100 posts someday), renders results in the rows style. No-results state styled; empty query lists all posts.
- **Blog:** posts in `content/blog/*.md`; one real category to start. Seed post (Mohamed may rewrite/delete): short "This site now runs on Hugo" note. Sidebar "Recent Posts" = 5 latest real posts; "Categories" = real taxonomy terms. RSS auto-generated.
- **BLOG rail item:** 4th item under CONTACT, same markup pattern (icon + small-caps label, theme icon font). On blog/search/404 pages the rail persists; portfolio items link to `/#home-e` etc.
- **CV:** PDF taken from `hugo-rebuild:portfolio-hugo/static/Mohamed_Aly_Amin_CV.pdf` (verified: PDF 1.7, 2 pages, 60 KB) → `static/media/`. **Input needed from Mohamed: confirm this PDF is current** (or supply a newer one).
- **404:** same card frame + gradient, "page not found" text, link home. GitHub Pages serves `404.html` automatically.

## 8. Verification protocol

Tools verified installed: Firefox (headless screenshots), ImageMagick (`compare`), Python 3 (local server, DOM normalize/diff scripts). Procedure after every stage:

1. Serve original export (`localhost:A`) and Hugo build (`localhost:B`).
2. **Pixel diff:** Firefox headless screenshots at 1440/768/390 px on both; ImageMagick compare; changed regions must map to allow-list items.
3. **DOM diff:** normalize both HTML documents (whitespace, attribute order); every hunk must match an allow-list item, else the stage fails.
4. **Zero-404:** crawl every `src`/`href`/`url()` the page loads; all must resolve. (The original fails this today; the port must not.)
5. Console-error check: no new JS errors vs. Stage 0 baseline.

Evidence (diff summaries, screenshot pairs, 404 report) is attached to the PR description.

## 9. Deployment

- `.github/workflows/deploy.yml`: on push to `main` → checkout, install **Hugo v0.163.3 extended** (pinned = local version), `hugo` build (**no `--minify`** — would poison DOM diffs), deploy via official `actions/configure-pages` + `upload-pages-artifact` + `deploy-pages`.
- CI job on PRs: build + internal-link integrity check (zero-404 invariant, enforced permanently).
- One-time at merge: switch repo Settings → Pages source to "GitHub Actions" (via `gh` if authorized, else guided manual step).
- `CNAME` kept in repo; DNS untouched. (Noted, informational: `www` DNS CNAMEs to `mohamed9974.github.io` yet 301s correctly to apex via GitHub — works today, works after; no action.)
- **Rollback:** revert the merge commit on `main` → the WordPress export serves again.
- New `.gitignore`: `public/`, `resources/`, `.hugo_build.lock`, `.superpowers/`, the two local reference screenshots (`originalwebsitecolor.jpg`, `hugodeploymentcolor.jpg`) (+ existing wpress backup entries retained).

## 10. Risks & mitigations

| Risk | Mitigation |
|---|---|
| Inline JS configs (`elementorFrontendConfig`, `wpcf7`) subtly required by theme JS | Ported verbatim with only documented URL substitutions; console-error check in verification |
| Google Fonts URL drift (subsets/weights) | `<link>` URLs copied character-for-character |
| CSS-internal relative `url()` breakage in Stage 3 | Directories moved whole (structure preserved); zero-404 check catches any miss |
| Elementor markup sensitive to whitespace/attribute changes during templating | Stage 2's byte-identical invariant; Stage 1's DOM diff |
| Formspree free-tier limits changed | Verify at implementation; swap-friendly design (endpoint + small JS only) |
| Background video (16 MB-class mp4) slow on Pages | Unchanged from today (same file, same hosting) — no regression; optimization is out of scope |

## 11. Inputs Mohamed owes implementation

1. Formspree form ID (instructions will be provided)
2. Confirmation the 2-page CV PDF is current
3. (Post-port) his queued non-critical blog design edits

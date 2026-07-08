# WordPress → Hugo Port Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the static WordPress export serving mohamedalyamin.com with a visually identical Hugo site whose broken features (contact form, search, sidebar widgets, feed) actually work, leaving zero WordPress artifacts.

**Architecture:** Staged hybrid port. The original `index.html` is split into verbatim fragments (concatenation must reproduce the original byte-for-byte), assembled by Hugo templates, then modified only by allow-listed edits — one edit per commit, each proven by a DOM diff that shows nothing else. Content then moves to YAML under a byte-identical build gate, and finally assets are re-homed off WordPress paths under a rendered-identical gate.

**Tech Stack:** Hugo 0.163.3 extended, GitHub Pages via Actions, Formspree (contact form), vanilla JS (form submit + search), Python 3 stdlib + Firefox headless + ImageMagick (verification).

**Spec:** `docs/superpowers/specs/2026-07-07-wordpress-to-hugo-port-design.md` — the allow-list in §6 is FROZEN; if any gate failure tempts you to add an allow-list item, STOP and ask Mohamed.

## Global Constraints

- Branch: `hugo-port` (exists, cut from `origin/main`@`b4d128a`). Never commit to `main` directly.
- Hugo pinned: **0.163.3 extended** (`hugo version` must show it). Never use `--minify` (poisons DOM diffs).
- **HTML output uses `text/template`, not `html/template`** (Task 4 set `[outputFormats.HTML] isPlainText = true` in hugo.toml). Reason: Go's `html/template` strips HTML comments and rewrites `<style>`/`<script>` bodies during contextual autoescaping, which corrupts the verbatim WordPress export; `isPlainText` makes Hugo a pure byte-assembler, which is what Stage 1's byte-identity requires. **Consequence for later tasks: template interpolations are NOT auto-escaped.** For byte-identical work (Tasks 5, 6, 9) this is correct — you WANT verbatim passthrough, so extract values verbatim (entities included). For NEW dynamic content (Task 7 blog/search: post titles, search results), escape explicitly (`| htmlEscape`, or `jsonify` for JSON) since author input is no longer auto-escaped. `.Content` is already safe HTML regardless.
- Title copy (allow-list #1, exact): `The Legend &#8211; My portfolio`
- Formspree endpoint (exact): `https://formspree.io/f/mwvdzgqv`
- Never commit: `public/`, `resources/`, `verification/`, `.hugo_build.lock` (all gitignored; verify before every commit with `git status`).
- `verification/reference/` is written once in Task 2 and never modified afterward.
- Ports: reference site `8081`, Hugo build `8082`, CI link-check `8099`.
- Commit messages end with: `Co-Authored-By: Claude <noreply@anthropic.com>` (project convention; adjust to the identity your harness mandates).
- Every task ends with its gate(s) passing. A failing gate means STOP, diagnose, fix or report — never commit on a red gate, never widen a gate to make it pass.
- The 10-item allow-list from spec §6 is reproduced in Task 5/6/7 steps where used; treat it as immutable.

### Named gates (exact commands, referenced by name from tasks)

All gates run from the repo root. Prerequisites: reference server and build server running (each task says when to start them):

```bash
# serve reference (terminal A, leave running):
python3 -m http.server 8081 --bind 127.0.0.1 --directory verification/reference
# build + serve Hugo output (terminal B, leave running):
hugo && python3 -m http.server 8082 --bind 127.0.0.1 --directory public
```

**GATE-DOM** — structural equality vs. reference (allow-listed hunks only):

```bash
curl -s http://127.0.0.1:8082/ > /tmp/port-index.html
python3 scripts/verify/dom_normalize.py verification/reference/index.html > /tmp/ref.norm
python3 scripts/verify/dom_normalize.py /tmp/port-index.html > /tmp/port.norm
diff -u /tmp/ref.norm /tmp/port.norm | tee /tmp/dom.diff | wc -l
```

Pass = every hunk in `/tmp/dom.diff` maps to an allow-list item named by the current task ("empty" when the task claims no visible/DOM change). Review the diff hunk-by-hunk; any unexplained hunk = FAIL.

**GATE-PIX** — pixel equality vs. reference within noise floor:

```bash
bash scripts/verify/capture.sh http://127.0.0.1:8082 verification/shots-port port
bash scripts/verify/pixel_diff.sh verification/shots-ref verification/shots-port verification/pixdiff
cat verification/noise-floor.txt   # compare: each AE count must be <= 2x its noise-floor line,
                                   # except regions the task allow-lists (inspect diff-*.png visually)
```

GATE-PIX reliability (measured 2026-07-08 post-Firefox-152, see `noise-floor.txt`): **only home-{1440,768,390} are reliably tight (~0) — trust the count there.** ALL `#anchor` captures (resume-e AND contacts-e, every width) are INTERMITTENTLY scroll-jittery — the JS hash-scroll settles at a slightly different Y each run, so the same capture reads 0 on one run and 20%+ on another (observed on contacts-e-768 in Task 6e). For any `#anchor` width, **judge diff-*.png visually** (uniform vertical smear = benign scroll offset; localized colour/text change = real regression) — never by raw count. Byte/DOM identity (GATE-DOM) is the deterministic primary gate; GATE-PIX corroborates, and its home captures are the trustworthy signal.

**GATE-404** — every local reference the page loads resolves:

```bash
python3 scripts/verify/check_404.py http://127.0.0.1:8082 /blog/ /search/
echo "exit: $?"   # expected: exit 0 once Task 7 exists; before Task 7 run without /blog/ /search/
```

**GATE-BYTE** — build output unchanged by a refactor (used in Stage 2):

```bash
hugo --quiet && sha256sum public/index.html
# run before and after the change; the two hashes must be identical
```

**GATE-WP** — zero WordPress artifacts (final stage only):

```bash
grep -rn "wp-content\|wp-includes\|wp-json\|xmlrpc" \
  --exclude-dir=.git --exclude-dir=verification --exclude-dir=public \
  --exclude-dir=docs . ; echo "repo grep exit: $? (expect 1 = no matches)"
hugo --quiet && grep -rn "wp-content\|wp-includes" public/ ; echo "built grep exit: $? (expect 1)"
```

## File Structure (end state)

```
CNAME                                   (untouched)
hugo.toml                               Task 3
.github/workflows/deploy.yml            Task 11
layouts/
  _default/baseof.html                  Task 4   (chains partials, defines "main" block)
  index.html                            Task 4   (home main block: 3 section partials)
  _default/list.html                    Task 7   (blog + category rows)
  _default/single.html                  Task 7   (blog post)
  _default/search.html                  Task 7   (client-side search page)
  404.html                              Task 7
  index.searchindex.json                Task 7   (search index output format)
  partials/head.html                    Task 4   (fragment; edited Tasks 5/6/7)
  partials/body-open.html               Task 4   (fragment, verbatim forever)
  partials/sidebar-nav.html             Task 4   (fragment; BLOG item Task 7)
  partials/card-profile.html            Task 4   (fragment; CV link Task 6d; data Task 9)
  partials/sidebar-widgets.html         Task 4   (fragment; real widgets Task 7)
  partials/section-about.html           Task 4   (fragment; data Task 9)
  partials/section-resume.html          Task 4   (fragment; data Task 9)
  partials/section-contacts.html        Task 4   (fragment; form Task 6e; data Task 9)
  partials/tail-scripts.html            Task 4   (fragment; edited Tasks 6/6e)
content/blog/_index.md                  Task 7
content/blog/hello-hugo.md              Task 7   (seed post)
content/search.md                       Task 7
data/profile.yaml  data/resume.yaml  data/contact.yaml    Task 9
static/js/contact-form.js               Task 6e
static/media/Mohamed_Aly_Amin_CV.pdf    Task 6d
static/theme/ static/vendor/ static/media/               Task 10 (re-homed from wp-* paths)
scripts/verify/{capture.sh,pixel_diff.sh,dom_normalize.py,check_404.py}   Task 1
verification/                           gitignored working dir (reference copy, shots, diffs)
```

Deviation from spec §5 noted and intended: the original `<head>` lives in `partials/head.html` included by `baseof.html` (equivalent to "baseof carries the head verbatim", but keeps every fragment a rename-only file).

---

### Task 1: Verification tooling

**Files:**
- Create: `scripts/verify/capture.sh`, `scripts/verify/pixel_diff.sh`, `scripts/verify/dom_normalize.py`, `scripts/verify/check_404.py`
- Modify: `.gitignore`

**Interfaces:**
- Produces CLIs used by every later gate:
  - `capture.sh <base-url> <out-dir> <label>` → PNGs named `<label>-<home|resume-e|contacts-e>-<1440|768|390>.png`
  - `pixel_diff.sh <dir-a> <dir-b> <out-dir> [fuzz=2%]` → prints `<name>: N differing pixels`, writes `diff-*.png`
  - `dom_normalize.py <file.html>` → canonical one-node-per-line form on stdout
  - `check_404.py <base-url> [extra-start-path ...]` → exit 0 iff all local refs resolve

- [ ] **Step 1: Add verification dir to .gitignore**

Append to `.gitignore`:

```
# Local verification artifacts (reference copy, screenshots, diffs)
verification/
```

- [ ] **Step 2: Write `scripts/verify/dom_normalize.py`**

```python
#!/usr/bin/env python3
"""Normalize an HTML file for structural diffing.

Emits one node per line with sorted attributes and whitespace-collapsed
text so two documents can be compared with plain `diff`. Comments are
dropped (not rendered); inline <style>/<script> bodies are kept (they
are design-critical on this site).
Usage: dom_normalize.py <file.html>
"""
import sys
from html.parser import HTMLParser

VOID = {"area", "base", "br", "col", "embed", "hr", "img", "input",
        "link", "meta", "param", "source", "track", "wbr"}


class Normalizer(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=False)
        self.out = []
        self.depth = 0

    def handle_decl(self, decl):
        self.out.append(f"<!{decl.lower()}>")

    def handle_starttag(self, tag, attrs):
        a = " ".join(f'{k}="{v if v is not None else ""}"'
                     for k, v in sorted(attrs))
        self.out.append("  " * self.depth + f"<{tag}{' ' + a if a else ''}>")
        if tag not in VOID:
            self.depth += 1

    def handle_startendtag(self, tag, attrs):
        self.handle_starttag(tag, attrs)
        if tag not in VOID:
            self.depth -= 1

    def handle_endtag(self, tag):
        if tag not in VOID:
            self.depth = max(0, self.depth - 1)
        self.out.append("  " * self.depth + f"</{tag}>")

    def handle_data(self, data):
        text = " ".join(data.split())
        if text:
            self.out.append("  " * self.depth + f"TEXT {text}")

    def handle_entityref(self, name):
        self.out.append("  " * self.depth + f"TEXT &{name};")

    def handle_charref(self, name):
        self.out.append("  " * self.depth + f"TEXT &#{name};")

    def handle_comment(self, data):
        pass


def main():
    with open(sys.argv[1], encoding="utf-8", errors="replace") as f:
        p = Normalizer()
        p.feed(f.read())
    print("\n".join(p.out))


if __name__ == "__main__":
    main()
```

- [ ] **Step 3: Write `scripts/verify/check_404.py`**

```python
#!/usr/bin/env python3
"""Verify every local asset/link reference resolves.

Fetches the given start paths, extracts src/href/url() references
(recursing into CSS url() refs), and GETs each local one against the
same host. External URLs are counted but not fetched unless --external.
Usage: check_404.py <base-url> [extra-start-path ...] [--external]
Exit 0 iff all local references resolve.
"""
import re
import sys
import urllib.parse
import urllib.request

URL_RE = re.compile(r'url\(\s*["\']?([^"\')]+)["\']?\s*\)')
ATTR_RE = re.compile(r'(?:src|href|action)\s*=\s*["\']([^"\']+)["\']')
SKIP = ("#", "data:", "mailto:", "tel:", "javascript:")


def fetch(url):
    try:
        with urllib.request.urlopen(
                urllib.request.Request(url, method="GET"), timeout=15) as r:
            return r.status, r.read()
    except Exception as exc:
        return None, str(exc).encode()


def main():
    args = [a for a in sys.argv[1:] if a != "--external"]
    check_external = "--external" in sys.argv
    base = args[0].rstrip("/")
    starts = ["/"] + [p if p.startswith("/") else "/" + p for p in args[1:]]

    queue, seen, failures, external = [], set(), [], set()
    for s in starts:
        status, body = fetch(base + s)
        if status != 200:
            failures.append((s, status))
            continue
        for m in list(ATTR_RE.finditer(body.decode("utf-8", "replace"))) + \
                 list(URL_RE.finditer(body.decode("utf-8", "replace"))):
            queue.append((s, m.group(1)))

    while queue:
        page, ref = queue.pop()
        ref = ref.strip()
        if not ref or ref.startswith(SKIP):
            continue
        if ref.startswith(("http://", "https://", "//")):
            external.add(ref)
            continue
        target = urllib.parse.urljoin(page, ref).split("#")[0].split("?")[0]
        if not target or target in seen:
            continue
        seen.add(target)
        status, body = fetch(base + urllib.parse.quote(target, safe="/%.~_-"))
        if status != 200:
            failures.append((target, status))
        elif target.endswith(".css"):
            for m in URL_RE.finditer(body.decode("utf-8", "replace")):
                queue.append((target, m.group(1)))

    print(f"checked {len(seen)} local refs from {len(starts)} start page(s); "
          f"{len(failures)} failures; {len(external)} external refs (unchecked)")
    for t, s in sorted(failures):
        print(f"  FAIL {s}: {t}")
    if check_external:
        for u in sorted(external):
            full = "https:" + u if u.startswith("//") else u
            s, _ = fetch(full)
            print(f"  EXT {s}: {full}")
            if s != 200:
                failures.append((full, s))
    sys.exit(1 if failures else 0)


if __name__ == "__main__":
    main()
```

- [ ] **Step 4: Write `scripts/verify/capture.sh`**

```bash
#!/usr/bin/env bash
# Full-page screenshots of the three site sections at three widths,
# using headless Firefox with a throwaway profile per shot.
# Usage: capture.sh <base-url> <out-dir> <label>
set -euo pipefail
BASE_URL=$1 OUT_DIR=$2 LABEL=$3
mkdir -p "$OUT_DIR"
for W in 1440 768 390; do
  for FRAG in home resume-e contacts-e; do
    URL="$BASE_URL/"
    [ "$FRAG" != "home" ] && URL="$BASE_URL/#$FRAG"
    PROFILE=$(mktemp -d)
    firefox --headless --profile "$PROFILE" --window-size="$W,2400" \
      --screenshot "$OUT_DIR/$LABEL-$FRAG-$W.png" "$URL" >/dev/null 2>&1
    rm -rf "$PROFILE"
  done
done
ls -l "$OUT_DIR"
```

- [ ] **Step 5: Write `scripts/verify/pixel_diff.sh`**

```bash
#!/usr/bin/env bash
# Pixel-compare same-named PNGs across two directories with ImageMagick.
# Prints "<name>: <AE> differing pixels"; writes diff-<name>.png visuals.
# Usage: pixel_diff.sh <dir-a> <dir-b> <out-dir> [fuzz]
set -euo pipefail
A=$1 B=$2 OUT=$3 FUZZ=${4:-2%}
mkdir -p "$OUT"
STATUS=0
for IMG in "$A"/*.png; do
  NAME=$(basename "$IMG")
  # strip the leading label so ref-home-1440.png matches port-home-1440.png
  SUFFIX=${NAME#*-}
  MATCH=$(ls "$B"/*-"$SUFFIX" 2>/dev/null | head -1 || true)
  if [ -z "$MATCH" ]; then
    echo "$SUFFIX: MISSING counterpart in $B"
    STATUS=1
    continue
  fi
  AE=$(compare -metric AE -fuzz "$FUZZ" "$IMG" "$MATCH" \
       "$OUT/diff-$SUFFIX" 2>&1 || true)
  echo "$SUFFIX: $AE differing pixels (fuzz $FUZZ)"
done
exit $STATUS
```

- [ ] **Step 6: Verify the scripts are sane**

```bash
chmod +x scripts/verify/*.sh
python3 -m py_compile scripts/verify/dom_normalize.py scripts/verify/check_404.py && echo "py OK"
printf '<html><head><title>t</title></head><body><p class="b" id="a">hi  there</p></body></html>' > /tmp/nt.html
python3 scripts/verify/dom_normalize.py /tmp/nt.html
```

Expected final output exactly:

```
<html>
  <head>
    <title>
      TEXT t
    </title>
  </head>
  <body>
    <p class="b" id="a">
      TEXT hi there
    </p>
  </body>
</html>
```

- [ ] **Step 7: Commit**

```bash
git add scripts/verify .gitignore
git commit -m "Add verification tooling: DOM normalizer, 404 crawler, screenshot capture/diff"
```

---

### Task 2: Stage 0 — capture ground truth

**Files:**
- Create (gitignored, local only): `verification/reference/` (copy of the export), `verification/shots-ref/`, `verification/shots-ref2/`, `verification/noise-floor.txt`, `verification/ref-404-baseline.txt`

**Interfaces:**
- Produces: `verification/reference/index.html` (THE diff target for every GATE-DOM run), `verification/shots-ref/` (GATE-PIX baseline), `verification/noise-floor.txt` (GATE-PIX thresholds).

- [ ] **Step 1: Snapshot the export before anything moves**

```bash
mkdir -p verification/reference
cp index.html CNAME verification/reference/
cp -r wp-content wp-includes verification/reference/
sha256sum index.html verification/reference/index.html
```

Expected: both hashes identical. Record the hash in `verification/reference/SHA256` (`sha256sum index.html > verification/reference/SHA256`).

- [ ] **Step 2: Serve the reference and capture screenshots twice**

```bash
python3 -m http.server 8081 --bind 127.0.0.1 --directory verification/reference &
sleep 1
bash scripts/verify/capture.sh http://127.0.0.1:8081 verification/shots-ref ref
bash scripts/verify/capture.sh http://127.0.0.1:8081 verification/shots-ref2 ref2
```

Expected: 9 PNGs in each directory (3 sections × 3 widths), each > 20 KB.

- [ ] **Step 3: Measure the noise floor (site's own animation nondeterminism)**

```bash
bash scripts/verify/pixel_diff.sh verification/shots-ref verification/shots-ref2 \
  verification/noise 2% | tee verification/noise-floor.txt
```

Expected: 9 lines of `<name>: N differing pixels`. Whatever N values appear ARE the noise floor — GATE-PIX passes when original-vs-port counts are ≤ 2× these. If any N exceeds ~5% of image pixels, inspect `verification/noise/diff-*.png`: identify the animating region (e.g. typed text) and note it in `noise-floor.txt` as a known-noisy region to judge visually.

- [ ] **Step 4: Record the reference 404 baseline (the original is known-broken)**

```bash
python3 scripts/verify/check_404.py http://127.0.0.1:8081 \
  | tee verification/ref-404-baseline.txt ; echo "exit: $?"
```

Expected: exit 1 with failures — at minimum the `httplocalhost` emoji script cannot even appear as a local ref, but `/?feed=rss2`-style href targets and any missing favicon will FAIL. This file documents what "broken today" means; the port must beat it (GATE-404 exit 0).

- [ ] **Step 5: Verify nothing was committed**

```bash
git status --porcelain
```

Expected: empty (verification/ is gitignored). Nothing to commit in this task.

---

### Task 3: Hugo scaffold

**Files:**
- Create: `hugo.toml`

**Interfaces:**
- Produces: site config later tasks rely on — `categories` taxonomy, `SearchIndex` home output format (consumed by Task 7's `index.searchindex.json` template), pagination size 10.

- [ ] **Step 1: Verify the pinned Hugo version**

```bash
hugo version
```

Expected: contains `v0.163.3` and `+extended`. If not, STOP — install the pinned version before continuing.

- [ ] **Step 2: Write `hugo.toml`**

```toml
baseURL = "https://mohamedalyamin.com/"
languageCode = "en-US"
title = "The Legend – My portfolio"
timeZone = "Europe/Istanbul"

# Only the categories taxonomy is used (sidebar widget); no tags.
[taxonomies]
  category = "categories"

[pagination]
  pagerSize = 10

# Home emits HTML + RSS (allow-list #6) + the client-side search index.
[outputs]
  home = ["HTML", "RSS", "SearchIndex"]

[outputFormats.SearchIndex]
  mediaType = "application/json"
  baseName = "search-index"
  isPlainText = true
  notAlternative = true

[markup.goldmark.renderer]
  # Blog posts may embed raw HTML (same trust level as the site itself).
  unsafe = true
```

- [ ] **Step 3: Verify Hugo accepts the config**

```bash
hugo --quiet && ls public/
```

Expected: build succeeds; `public/` contains at least `index.xml` and `sitemap.xml` (no HTML yet — there are no layouts). A warning about missing layouts is fine.

- [ ] **Step 4: Commit**

```bash
rm -rf public
git add hugo.toml
git commit -m "Add Hugo site configuration"
```

---

### Task 4: Stage 1 — carve the export into verbatim fragments

**Files:**
- Create: `layouts/partials/{head,body-open,sidebar-nav,card-profile,sidebar-widgets,section-about,section-resume,section-contacts,tail-scripts}.html`, `layouts/_default/baseof.html`, `layouts/index.html`
- Move: `wp-content/` → `static/wp-content/`, `wp-includes/` → `static/wp-includes/`
- Delete: `index.html` (repo root; byte-copy lives in `verification/reference/` and in git history)

**Interfaces:**
- Consumes: `verification/reference/index.html` + its SHA256 (Task 2).
- Produces: the nine partial files every later task edits, named exactly as listed above; `baseof.html` with a `{{ block "main" . }}` later reused by blog layouts.

- [ ] **Step 1: Preflight — split anchors must be unique and in order, no template collisions**

```bash
grep -c '{{' verification/reference/index.html   # expected: 0 (else STOP: fragments would
                                                 # need {{ "{{" }} escaping — report first)
for P in '</head>' '<header class="header">' 'card-started' \
         '<div class="sidebar-wrap">' 'id="card-home-e"' 'id="card-resume-e"' \
         'id="card-contacts-e"' 'id="elementor-post-41-css"'; do
  printf '%-38s' "$P"; grep -c -- "$P" verification/reference/index.html
done
grep -n 'card-started\|card-inner' verification/reference/index.html
```

Expected: every count is exactly `1` except `card-inner` occurrences in the last grep, which must show exactly 3 `card-inner` divs whose ids are `card-home-e`, `card-resume-e`, `card-contacts-e`, and one `card-started` line between `<header` (~line 968) and `sidebar-wrap` (~line 1100). If an id differs (e.g. `card-resume` without `-e`), use the actual id in Step 2's pattern — the three-cards structure itself must hold, otherwise STOP and report.

- [ ] **Step 2: Split into 9 fragments and prove reassembly**

```bash
mkdir -p layouts/partials layouts/_default
cd layouts/partials
csplit -z -f frag -b '%02d.html' ../../verification/reference/index.html \
  '/<\/head>/+1' \
  '/<header class="header">/' \
  '/card-started/' \
  '/<div class="sidebar-wrap">/' \
  '/id="card-home-e"/' \
  '/id="card-resume-e"/' \
  '/id="card-contacts-e"/' \
  '/id="elementor-post-41-css"/'
cat frag00.html frag01.html frag02.html frag03.html frag04.html \
    frag05.html frag06.html frag07.html frag08.html | sha256sum
cd ../..
cat verification/reference/SHA256
```

Expected: the two SHA256 values are IDENTICAL. This is the carve invariant — if it fails, the split corrupted something; delete `frag*` and debug the patterns.

- [ ] **Step 3: Rename fragments to their semantic partial names**

```bash
cd layouts/partials
mv frag00.html head.html            # <!doctype> through </head>
mv frag01.html body-open.html       # <body>, background/gradient wrappers
mv frag02.html sidebar-nav.html     # <header>: hamburger + ABOUT/RESUME/CONTACT rail
mv frag03.html card-profile.html    # video bg, avatar, name, socials, Download CV
mv frag04.html sidebar-widgets.html # search, Recent Posts, Categories, close btn
mv frag05.html section-about.html   # card-home-e
mv frag06.html section-resume.html  # card-resume-e (experience + logos + education)
mv frag07.html section-contacts.html# card-contacts-e (info + form)
mv frag08.html tail-scripts.html    # post-41/53 css links, all scripts, </body></html>
cd ../..
head -3 layouts/partials/sidebar-nav.html   # sanity: first line contains <header class="header">
```

- [ ] **Step 4: Write the assembling templates (single-line to add zero bytes)**

`layouts/_default/baseof.html` — exactly one line, no trailing newline concerns beyond the final one:

```
{{ partial "head.html" . }}{{ partial "body-open.html" . }}{{ partial "sidebar-nav.html" . }}{{ partial "card-profile.html" . }}{{ partial "sidebar-widgets.html" . }}{{ block "main" . }}{{ end }}{{ partial "tail-scripts.html" . }}
```

`layouts/index.html` — exactly one line:

```
{{ define "main" }}{{ partial "section-about.html" . }}{{ partial "section-resume.html" . }}{{ partial "section-contacts.html" . }}{{ end }}
```

- [ ] **Step 5: Move assets into Hugo's static dir; retire the root export**

```bash
mkdir -p static
git mv wp-content static/wp-content
git mv wp-includes static/wp-includes
git rm index.html
```

- [ ] **Step 6: Build and run the byte + DOM checks**

```bash
hugo --quiet
sha256sum public/index.html verification/reference/index.html
cmp public/index.html verification/reference/index.html ; echo "cmp exit: $?"
```

Expected: ideally identical hashes (cmp exit 0). Acceptable alternative: `cmp` reports a difference ONLY at the very last byte (trailing newline from the template file). Anything else = FAIL. Then run **GATE-DOM** (serve as described in Global Constraints): expected EMPTY diff. Run **GATE-404** (without `/blog/ /search/` — they don't exist yet): expected failure set ⊆ `verification/ref-404-baseline.txt` (the port may not add breakage; inherited breakage is fixed by Tasks 6b/6c/7).

- [ ] **Step 7: Run GATE-PIX**

Expected: every AE count ≤ 2× its `noise-floor.txt` line. Inspect any exceedance in `verification/pixdiff/diff-*.png` before judging.

- [ ] **Step 8: Commit**

```bash
git status --porcelain   # confirm: no public/, no verification/
git add -A
git commit -m "Carve WordPress export into verbatim Hugo partials (reassembly SHA-verified)"
```

---

### Task 5: Allow-list #1 — title typo

**Files:**
- Modify: `layouts/partials/head.html` (one line)

**Interfaces:** none new.

- [ ] **Step 1: Verify the exact original string**

```bash
grep -n '<title>' layouts/partials/head.html
```

Expected: exactly one line: `<title>The Legend &#8211; My portifolio</title>`.

- [ ] **Step 2: Apply the fix**

Edit that line to exactly: `<title>The Legend &#8211; My portfolio</title>`

- [ ] **Step 3: Gate**

Rebuild (`hugo --quiet`) and run **GATE-DOM**. Expected diff = exactly one hunk, the TEXT line of `<title>` (allow-list #1). No other hunks.

- [ ] **Step 4: Commit**

```bash
git add layouts/partials/head.html
git commit -m "Fix title typo: portifolio -> portfolio (allow-list #1)"
```

---

### Task 6: Allow-list #7/#8 — dead WordPress metadata and localhost remnants

(One concern — invisible head/config cruft: spec allow-list #7, #8, and the mechanical half of #5.)

**Files:**
- Modify: `layouts/partials/head.html`, `layouts/partials/tail-scripts.html`, `layouts/partials/sidebar-widgets.html`
- Delete: `static/wp-includes/wlwmanifest.xml`

**Interfaces:** none new.

- [ ] **Step 1: Enumerate the targets (verify before deleting)**

```bash
grep -n 'api.w.org\|EditURI\|wlwmanifest\|rest_route\|feed=rss2\|shortlink\|wp-emoji\|s.w.org\|generator" content="WordPress\|rel="canonical"' layouts/partials/head.html
grep -n 'httplocalhost' layouts/partials/*.html
```

Expected in head.html: the REST link, EditURI/RSD link, wlwmanifest link, two oEmbed `rest_route` links, the rss2 feed link, shortlink, the wp-emoji inline `<script>` block (contains `httplocalhost`), the `s.w.org` dns-prefetch, the WordPress generator meta. Expected `httplocalhost` total: 5 occurrences across head (emoji script), sidebar-widgets (search form action), and tail-scripts (inline JS configs). Map every hit before proceeding; if counts differ, list them and match each to allow-list #7/#8 or STOP.

- [ ] **Step 2: Remove the dead head metadata (allow-list #7)**

Delete from `head.html`: the `api.w.org` link, EditURI link, wlwmanifest link, both oEmbed links, the `feed=rss2` link (its replacement comes in Task 7), the shortlink link, the entire wp-emoji `<script>...</script>` block and its companion `<style>` block for `img.wp-smiley` IF AND ONLY IF that style block is the emoji-only one at the top of the file (verify: it styles only `img.wp-smiley, img.emoji` — it is dead once the emoji script is gone, but it is also harmless; KEEP it if unsure — keeping is not a gate failure since GATE-DOM hunks must only map to REMOVALS listed here), the `s.w.org` dns-prefetch, and the `<meta name="generator" content="WordPress 6.0.1">`. Keep `rel="canonical"` and `rel="profile"`.

- [ ] **Step 3: Fix remaining `httplocalhost` values (allow-list #8)**

For each hit found in Step 1 outside the deleted emoji block:
- `sidebar-widgets.html` search form: `action="httplocalhost/"` → `action="/search/"` (allow-list #5's mechanical half; the page itself arrives in Task 7).
- `tail-scripts.html` inline configs (`ajax-portfolio-content-js-extra`, `contact-form-7-js-extra`, `elementorFrontendConfig` — whichever contain `httplocalhost` or `localhost` URLs): replace each URL value with the site-relative equivalent (`httplocalhost/wp-admin/admin-ajax.php` → `/`, other `httplocalhost/X` → `/X`). These endpoints are never called (no backend), but the values must be non-garbage. Document each substitution in the commit body.

```bash
grep -rn 'httplocalhost' layouts/ ; echo "exit $? (expect 1 = none left)"
```

- [ ] **Step 4: Delete the orphaned manifest file**

```bash
git rm static/wp-includes/wlwmanifest.xml
```

- [ ] **Step 5: Gate**

Rebuild + **GATE-DOM**: every hunk must be a removal/edit enumerated in Steps 2–3 (allow-list #7/#8/#5). **GATE-PIX**: within noise floor (nothing here is visible). **GATE-404**: failure set must have SHRUNK vs. `ref-404-baseline.txt`; no new failures.

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "Remove dead WordPress metadata; repair mangled localhost URLs (allow-list #5/#7/#8)"
```

---

### Task 6d: Allow-list #3 — local CV download

**Files:**
- Create: `static/media/Mohamed_Aly_Amin_CV.pdf` (from `hugo-rebuild` branch)
- Modify: `layouts/partials/card-profile.html` (one href)

**Interfaces:**
- Produces: `/media/` directory that Task 10 later fills with the rest of the uploads.

- [ ] **Step 1: Retrieve and verify the PDF**

```bash
mkdir -p static/media
git show hugo-rebuild:portfolio-hugo/static/Mohamed_Aly_Amin_CV.pdf > static/media/Mohamed_Aly_Amin_CV.pdf
file static/media/Mohamed_Aly_Amin_CV.pdf
```

Expected: `PDF document, version 1.7, 2 page(s)`.

- [ ] **Step 2: CHECKPOINT (Mohamed) — confirm the CV is current**

Ask Mohamed: "The CV going onto the site is the 2-page PDF from the old attempt (spec §11 input 2). Still current, or do you have a newer file?" If newer: replace the file, keep the same filename. Do not proceed past this step without an answer.

- [ ] **Step 3: Point the button at it**

```bash
grep -n 'drive.google.com' layouts/partials/card-profile.html
```

Expected: one `<a href="https://drive.google.com/file/d/16_o3VeG9yzeMPZgN6pQ93hb84vrp7rbk/view?usp=sharing"` — replace the href value with `/media/Mohamed_Aly_Amin_CV.pdf` and add ` download` attribute after the class attribute (`class="lnk solid-style" download`).

- [ ] **Step 4: Gate + commit**

Rebuild + **GATE-DOM** (expected hunks: the one anchor — allow-list #3), **GATE-404** (PDF resolves).

```bash
git add -A
git commit -m "Serve CV download locally (allow-list #3)"
```

---

### Task 6e: Allow-list #2 — working contact form via Formspree

**Files:**
- Create: `static/js/contact-form.js`
- Modify: `layouts/partials/section-contacts.html` (form tag + honeypot), `layouts/partials/tail-scripts.html` (remove CF7 JS, add ours)

**Interfaces:**
- Consumes: Formspree endpoint `https://formspree.io/f/mwvdzgqv` (Global Constraints).

- [ ] **Step 1: Verify the current form markup**

```bash
grep -n '<form' layouts/partials/section-contacts.html
grep -n 'wpcf7-response-output' layouts/partials/section-contacts.html
grep -n 'contact-form-7' layouts/partials/tail-scripts.html layouts/partials/head.html
```

Expected: one `<form action="/#wpcf7-f62-o1" method="post" ...>`; at least one `wpcf7-response-output` div (if zero: add `<div class="wpcf7-response-output" aria-hidden="true"></div>` immediately before `</form>` and note it under allow-list #2); CF7's `index.js` script tag + `contact-form-7-js-extra` inline config in tail-scripts; CF7 CSS link in head (KEEP the CSS — it styles the form).

- [ ] **Step 2: Rewire the form element**

In `section-contacts.html`:
- `action="/#wpcf7-f62-o1"` → `action="https://formspree.io/f/mwvdzgqv"` (keep `method="post"` and every class/attribute otherwise).
- Immediately after the opening `<form ...>` line insert the spam honeypot and subject line:

```html
<input type="text" name="_gotcha" style="display:none !important" tabindex="-1" autocomplete="off">
<input type="hidden" name="_subject" value="mohamedalyamin.com contact form">
```

- [ ] **Step 3: Write `static/js/contact-form.js`**

```javascript
// Submit the contact form to Formspree via fetch, reproducing the inline
// success/error behaviour Contact Form 7 provided on the WordPress site.
// With JS disabled the form still POSTs to Formspree's hosted fallback page.
(function () {
  var form = document.querySelector('form.wpcf7-form') ||
             document.querySelector('form[action^="https://formspree.io"]');
  if (!form) return;
  var output = form.querySelector('.wpcf7-response-output');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    output.textContent = '';
    output.className = 'wpcf7-response-output';
    output.removeAttribute('aria-hidden');
    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { Accept: 'application/json' }
    }).then(function (res) {
      if (res.ok) {
        form.reset();
        output.textContent = 'Thank you for your message. It has been sent.';
        output.classList.add('wpcf7-mail-sent-ok');
      } else {
        return res.json().then(function (body) {
          output.textContent = (body && body.errors && body.errors.length)
            ? body.errors.map(function (er) { return er.message; }).join(', ')
            : 'There was an error trying to send your message. Please try again later.';
          output.classList.add('wpcf7-mail-sent-ng');
        });
      }
    }).catch(function () {
      output.textContent = 'There was an error trying to send your message. Please try again later.';
      output.classList.add('wpcf7-mail-sent-ng');
    });
  });
})();
```

- [ ] **Step 4: Swap the scripts in `tail-scripts.html`**

Remove: the `<script ... id="contact-form-7-js-extra">...</script>` inline block and the `<script ... src="/wp-content/plugins/contact-form-7/includes/js/index.js..." id="contact-form-7-js"></script>` tag. Add in their place: `<script src="/js/contact-form.js"></script>`.

- [ ] **Step 5: Gate + functional test**

Rebuild + **GATE-DOM** (hunks = exactly the edits above, allow-list #2), **GATE-404**, **GATE-PIX** (honeypot is `display:none` — within noise floor). Functional: serve the build, open `http://127.0.0.1:8082/#contacts-e` in a browser, submit a test message ("port test — ignore"), verify the inline success message appears AND the submission shows in the Formspree dashboard/email. This sends one real email to Mohamed — tell him it's coming.

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "Wire contact form to Formspree with CF7-style inline feedback (allow-list #2)"
```

---

### Task 7: Allow-list #4/#5/#6/#10 — blog, search, widgets, feed, 404, BLOG rail item

**Files:**
- Create: `content/blog/_index.md`, `content/blog/hello-hugo.md`, `content/search.md`, `layouts/_default/list.html`, `layouts/_default/single.html`, `layouts/_default/search.html`, `layouts/404.html`, `layouts/index.searchindex.json`
- Modify: `layouts/partials/head.html` (title templating + feed link), `layouts/partials/sidebar-nav.html` (BLOG item + href conditionals), `layouts/partials/sidebar-widgets.html` (real widgets)

**Interfaces:**
- Consumes: `baseof.html`'s `main` block (Task 4), `SearchIndex` output format (Task 3).
- Produces: `/blog/`, `/search/`, `/404.html`, `/search-index.json` — GATE-404 runs WITH `/blog/ /search/` from now on.

- [ ] **Step 1: Preflight — confirm the theme class names the new layouts reuse**

```bash
grep -o 'class="title[_ a-z"-]*"' layouts/partials/section-resume.html | sort -u
grep -c 'resume-item' layouts/partials/section-resume.html
grep -n 'single-post-text\|resume-items' static/wp-content/themes/ryancv/style.css | head
```

Expected: a heading pattern (some `title`/`title_inner`-like classes), `resume-item` count ≥ 4, and `style.css` containing `.single-post-text` and `.resume-items` rules. The layouts below assume `title`/`title_inner`, `resume-items`/`resume-item`/`date`/`name`, `single-post-text`. If the heading classes differ, mirror the EXACT pattern found in section-resume.html in all four layouts (bounded adaptation; acceptance is Step 9's visual checkpoint).

- [ ] **Step 2: Content files**

`content/blog/_index.md`:

```markdown
---
title: "Blog"
---
```

`content/blog/hello-hugo.md` (seed post — Mohamed may rewrite or delete it later):

```markdown
---
title: "This Site Now Runs on Hugo"
date: 2026-07-07T12:00:00+03:00
categories: ["Notes"]
---

After four years as a frozen WordPress export, this site is now generated by
[Hugo](https://gohugo.io/). Same design, same content — but the contact form
now actually delivers, search works, and posts like this one exist.

The port had one strict rule: every pixel of the original design stays. If
you spot a difference, [tell me](/#contacts-e).
```

`content/search.md`:

```markdown
---
title: "Search"
layout: "search"
---
```

- [ ] **Step 3: Blog layouts**

`layouts/_default/list.html` (rows like the resume timeline — Decision ⑤A):

```html
{{ define "main" }}
<div class="card-inner animated active" id="card-blog">
  <div class="card-wrap">
    <div class="content blog">
      <div class="title"><span class="title_inner">{{ .Title }}</span></div>
      <div class="resume-items">
        {{ range .Paginator.Pages }}
        <div class="resume-item">
          <div class="date">{{ .Date.Format "2 Jan 2006" }}</div>
          <div class="name"><a href="{{ .RelPermalink }}">{{ .Title }}</a></div>
          <div class="text"><p>{{ .Summary | plainify }}</p></div>
        </div>
        {{ end }}
      </div>
      {{ if gt .Paginator.TotalPages 1 }}
      <div class="pager">
        {{ if .Paginator.HasPrev }}<a class="lnk" href="{{ .Paginator.Prev.URL }}"><span class="text">&laquo; Newer</span></a>{{ end }}
        {{ if .Paginator.HasNext }}<a class="lnk" href="{{ .Paginator.Next.URL }}"><span class="text">Older &raquo;</span></a>{{ end }}
      </div>
      {{ end }}
    </div>
  </div>
</div>
{{ end }}
```

`layouts/_default/single.html`:

```html
{{ define "main" }}
<div class="card-inner animated active" id="card-post">
  <div class="card-wrap">
    <div class="content blog-post">
      <div class="title"><span class="title_inner">{{ .Title }}</span></div>
      <div class="date">{{ .Date.Format "2 Jan 2006" }}{{ range .Params.categories }} &middot; <a href="/categories/{{ . | urlize }}/">{{ . }}</a>{{ end }}</div>
      <div class="single-post-text">{{ .Content }}</div>
      <p><a class="lnk" href="/blog/"><span class="text">&laquo; All posts</span></a></p>
    </div>
  </div>
</div>
{{ end }}
```

`layouts/404.html`:

```html
{{ define "main" }}
<div class="card-inner animated active" id="card-404">
  <div class="card-wrap">
    <div class="content">
      <div class="title"><span class="title_inner">Page not found</span></div>
      <p>Nothing lives at this address.</p>
      <p><a class="lnk" href="/"><span class="text">Back to the homepage</span></a></p>
    </div>
  </div>
</div>
{{ end }}
```

- [ ] **Step 4: Search index + search page**

`layouts/index.searchindex.json`:

```
{{- $posts := where site.RegularPages "Section" "blog" -}}
[{{ range $i, $p := $posts }}{{ if $i }},{{ end }}{"title":{{ $p.Title | jsonify }},"url":{{ $p.RelPermalink | jsonify }},"date":{{ $p.Date.Format "2 Jan 2006" | jsonify }},"categories":{{ $p.Params.categories | jsonify }},"content":{{ $p.Plain | jsonify }}}{{ end }}]
```

`layouts/_default/search.html`:

```html
{{ define "main" }}
<div class="card-inner animated active" id="card-search">
  <div class="card-wrap">
    <div class="content blog">
      <div class="title"><span class="title_inner">Search</span></div>
      <p id="search-summary">Loading&hellip;</p>
      <div class="resume-items" id="search-results"></div>
    </div>
  </div>
</div>
<script>
(function () {
  var q = (new URLSearchParams(window.location.search).get('s') || '').trim();
  var summary = document.getElementById('search-summary');
  var box = document.getElementById('search-results');
  fetch('/search-index.json').then(function (r) { return r.json(); }).then(function (posts) {
    var hits = q ? posts.filter(function (p) {
      var hay = (p.title + ' ' + p.content + ' ' + (p.categories || []).join(' ')).toLowerCase();
      return hay.indexOf(q.toLowerCase()) !== -1;
    }) : posts;
    summary.textContent = q
      ? hits.length + ' result' + (hits.length === 1 ? '' : 's') + ' for “' + q + '”'
      : 'All ' + hits.length + ' posts';
    box.innerHTML = hits.map(function (p) {
      return '<div class="resume-item"><div class="date">' + p.date +
             '</div><div class="name"><a href="' + p.url + '">' + p.title +
             '</a></div></div>';
    }).join('') || '<p>No posts matched. Try a shorter word.</p>';
  }).catch(function () { summary.textContent = 'Search index failed to load.'; });
})();
</script>
{{ end }}
```

- [ ] **Step 5: Head — per-page title + real feed link (home output must stay identical except the feed href)**

In `head.html`, replace the `<title>` line with:

```
<title>{{ if .IsHome }}The Legend &#8211; My portfolio{{ else }}{{ .Title }} &#8211; The Legend{{ end }}</title>
```

Where the `feed=rss2` link was removed in Task 6, add (same spot in the head):

```html
<link rel="alternate" type="application/rss+xml" title="The Legend &raquo; Feed" href="/index.xml">
```

- [ ] **Step 6: BLOG rail item + nav hrefs that work off-home (home output unchanged by conditionals)**

In `sidebar-nav.html`, find the three nav items (`grep -n 'ABOUT\|RESUME\|CONTACT' layouts/partials/sidebar-nav.html`). For each existing item, wrap its href so non-home pages jump back to the portfolio: `href="#home-e"` → `href="{{ if .IsHome }}#home-e{{ else }}/#home-e{{ end }}"` (likewise `#resume-e`, `#contacts-e`; copy the EXACT original fragment ids found). Then duplicate the CONTACT item block as a fourth item: label text `Blog`, icon class swapped to `ion ion-ios-book`, href `/blog/` (plain, no conditional). Preserve indentation and every other class verbatim.

- [ ] **Step 7: Real sidebar widgets**

In `sidebar-widgets.html`: keep each `<section>`/heading wrapper exactly; replace ONLY the inner demo list markup.

Recent Posts — replace the `<ul class="wp-block-latest-posts__list wp-block-latest-posts">...</ul>` contents with:

```html
{{ range first 5 (where site.RegularPages "Section" "blog") }}
<li><a class="wp-block-latest-posts__post-title" href="{{ .RelPermalink }}">{{ .Title }}</a></li>
{{ end }}
```

Categories — replace the `<ul class="wp-block-categories-list wp-block-categories">...</ul>` contents with:

```html
{{ range site.Taxonomies.categories.ByCount }}
<li class="cat-item"><a href="{{ .Page.RelPermalink }}">{{ .Page.Title }}</a></li>
{{ end }}
```

(The search form's action was already fixed in Task 6; verify `grep -c '/search/' layouts/partials/sidebar-widgets.html` = 1.)

- [ ] **Step 8: Gates**

Rebuild. **GATE-DOM** on home: hunks limited to — feed link (#6), title tag now emitting identical text via conditional (should produce NO hunk; if a hunk appears here it's a conditional bug — fix it), BLOG rail item (#10), widget list items (#4). **GATE-404** WITH `/blog/ /search/`: exit 0 — from this task on, this is the standing form of the gate. **GATE-PIX**: differing regions must be the rail (one new item) and sidebar widget text only. Also verify outputs exist:

```bash
ls public/blog/index.html public/blog/hello-hugo/index.html public/search/index.html \
   public/404.html public/search-index.json public/index.xml public/categories/notes/index.html
python3 -c "import json;print(len(json.load(open('public/search-index.json'))),'posts indexed')"
```

Expected: all files exist; `1 posts indexed`.

- [ ] **Step 9: CHECKPOINT (Mohamed) — visual review of the new surfaces**

Show Mohamed in his browser: `/blog/`, the post page, `/search/?s=hugo`, `/404.html`, and the home sidebar/rail. He approved the direction via mockups (Decisions ④A/⑤A); this confirms the built reality. His queued non-critical blog edits stay deferred unless he says otherwise. Record his verdict in the task board before continuing.

- [ ] **Step 10: Commit**

```bash
git add -A
git commit -m "Add blog, client-side search, real widgets, feed and 404 (allow-list #4/#5/#6/#10)"
```

---

### Task 9: Stage 2 — content to data files (byte-identical refactor)

**Files:**
- Create: `data/profile.yaml`, `data/resume.yaml`, `data/contact.yaml`
- Modify: `layouts/partials/card-profile.html`, `layouts/partials/section-resume.html`, `layouts/partials/section-contacts.html`

**Interfaces:**
- Produces: `site.Data.profile` (name, subtitle, socials[]), `site.Data.resume` (experience[], education[]), `site.Data.contact` (address, email, phone, freelance) — the editing surface Mohamed uses from now on.

Rule for every substep: extract text VERBATIM from the partial into YAML, replace it in the template with `{{ site.Data.<file>.<path> }}`, and prove nothing changed with **GATE-BYTE**. Multi-line/markup-bearing values use YAML block scalars holding the exact inner HTML (`body_html: |`), rendered with `| safeHTML`. Wrapper markup (Elementor divs with unique data-ids) NEVER moves into YAML.

- [ ] **Step 1: Record the pre-refactor hash**

```bash
hugo --quiet && sha256sum public/index.html | tee /tmp/stage2-before.sha
```

- [ ] **Step 2: profile.yaml**

Schema (values copied verbatim from `card-profile.html` — the grep commands locate them):

```yaml
name: ""        # grep -n 'Mohamed Aly Amin' layouts/partials/card-profile.html — single text node
roles: []       # the typed.js rotator: an ORDERED list of the <p> strings inside
                # <div class="typing-title">. Verify the exact set and order first:
                #   grep -n -A8 'typing-title' layouts/partials/card-profile.html
                # As of origin/main the five roles are, in order: Full-stack Developer,
                # Blockchain Architect, System Admin, DevSecOps Engineer, Cyber Security Engineer.
                # NOTE: the .typing-title block appears in TWO card locations in this partial —
                # both must render the identical list. Copy the verified list verbatim; do not
                # rely on this comment if the grep shows a different set.
socials: []     # list of {icon: "<exact class attr>", url: "<exact href>"} from the
                # social-links block; grep -n 'discordapp\|linkedin\|github\|wa.me\|t.me\|social' layouts/partials/card-profile.html
```

Replace the name text node with `{{ site.Data.profile.name }}`. For the subtitle: the `.typing-title` block is NOT a single text node — it is N sibling `<p>` elements typed.js cycles through. Replace each block's inner `<p>…</p>` list with `{{ range site.Data.profile.roles }}<p>{{ . }}</p>{{ end }}`, applied identically in BOTH locations the block appears; keep the surrounding `.subtitle`/`.typing-title` wrappers literal. If the range's whitespace can't be made byte-identical to the original `<p>` indentation, keep the `<p>` list literal in the template and skip moving roles to YAML — GATE-BYTE, not YAML-completeness, is the requirement. Replace the social anchors block with a range over `site.Data.profile.socials` ONLY IF the anchors are structurally identical (same classes, differing only in href/icon); otherwise keep anchors literal and move just the hrefs into YAML one field each. Run **GATE-BYTE**: hash must equal `/tmp/stage2-before.sha`. Commit: `git add -A && git commit -m "Extract profile text to data/profile.yaml (byte-identical)"`.

- [ ] **Step 3: resume.yaml**

Schema per entry (text fields only, from `section-resume.html`):

```yaml
experience:
  - period: ""      # e.g. "July 2021 - June 2022"
    title: ""       # e.g. "CYBER SECURITY ANALYST"
    company: ""     # e.g. "INTERPROBE BILGI TEKNOLOJILERI"
    body_html: |    # the entry's inner description HTML, verbatim
education:
  - period: ""
    title: ""
    place: ""
    body_html: |
```

Because each entry's Elementor wrapper has unique `data-id` attributes, the wrappers stay literal in the template; only the text nodes/description inner-HTML are swapped for `{{ (index site.Data.resume.experience N).field }}` references (N = 0,1,2…, one per entry, in original order; `body_html` rendered `{{ ... | safeHTML }}`). Logos' `<img>` tags stay in the template untouched. Run **GATE-BYTE** (hash unchanged). Commit: `"Extract resume text to data/resume.yaml (byte-identical)"`.

- [ ] **Step 4: contact.yaml**

```yaml
address: ""    # "Çankaya/Ankara"
email: ""      # "contact@mohamedalyamin.com"
phone: ""      # "+90 552 554 8660"
freelance: ""  # "Available"
```

Swap the four value text nodes in `section-contacts.html` (labels "Address:", "Email:" etc. stay literal). **GATE-BYTE**. Commit: `"Extract contact info to data/contact.yaml (byte-identical)"`.

- [ ] **Step 5: Stage gate**

```bash
sha256sum public/index.html ; cat /tmp/stage2-before.sha
git log --oneline -4
```

Expected: hash identical to Step 1; three clean commits.

---

### Task 10: Stage 3 — de-WordPressify every path

**Files:**
- Move (git mv, whole directories — CSS-internal relative url() refs survive): per the spec §5 mapping table, reproduced in Step 1
- Modify: every `layouts/partials/*.html` containing `/wp-content/` or `/wp-includes/` strings; `static/theme/elementor-css/post-*.css` and any other CSS with absolute `/wp-content/` URLs

**Interfaces:**
- Produces: final public paths `/theme/…`, `/vendor/…`, `/media/…` — the URLs the live site will serve forever.

- [ ] **Step 1: Move the trees**

```bash
mkdir -p static/theme static/vendor
git mv static/wp-content/themes/ryancv/style.css       static/theme/style.css
git mv static/wp-content/themes/ryancv/assets          static/theme/assets
git mv static/wp-content/plugins/elementor             static/vendor/elementor
git mv static/wp-content/plugins/contact-form-7        static/vendor/contact-form-7
git mv static/wp-content/plugins/ryancv-plugin/elementor/assets static/vendor/ryancv-widgets
git mv static/wp-content/uploads/elementor/css         static/theme/elementor-css
git mv static/wp-content/uploads/2022/08/*             static/media/
git mv static/wp-includes/js                           static/vendor/wpjs
git mv static/wp-includes/css/dist/block-library       static/vendor/wpjs/block-library
find static/wp-content static/wp-includes -type f      # expected: NOTHING listed
git rm -r static/wp-content static/wp-includes 2>/dev/null || rmdir -p static/wp-content static/wp-includes 2>/dev/null || true
```

If the `find` lists leftovers: STOP — a file was referenced that the mapping missed; map it explicitly (extend the mv list, same pattern), never delete blind.

- [ ] **Step 2: Rewrite references in templates and CSS**

```bash
grep -rln 'wp-content\|wp-includes' layouts/ static/ | tee /tmp/wp-refs.txt
```

For every file listed apply these exact substitutions (sed -i, in this order):

```bash
sed -i \
 -e 's|/wp-content/themes/ryancv/style.css|/theme/style.css|g' \
 -e 's|/wp-content/themes/ryancv/assets|/theme/assets|g' \
 -e 's|/wp-content/plugins/elementor|/vendor/elementor|g' \
 -e 's|/wp-content/plugins/contact-form-7|/vendor/contact-form-7|g' \
 -e 's|/wp-content/plugins/ryancv-plugin/elementor/assets|/vendor/ryancv-widgets|g' \
 -e 's|/wp-content/uploads/elementor/css|/theme/elementor-css|g' \
 -e 's|/wp-content/uploads/2022/08|/media|g' \
 -e 's|/wp-includes/css/dist/block-library|/vendor/wpjs/block-library|g' \
 -e 's|/wp-includes/js|/vendor/wpjs|g' \
 $(cat /tmp/wp-refs.txt)
grep -rn 'wp-content\|wp-includes' layouts/ static/ ; echo "exit $? (expect 1)"
```

- [ ] **Step 3: Gates — rendering must be untouched by re-homing**

Rebuild and serve. **GATE-404** (exit 0 — this catches ANY missed path instantly). **GATE-DOM**: every hunk is a path-string substitution matching the table (mechanical check: `grep -v` the nine mappings out of the diff; remainder must be prior tasks' known hunks). **GATE-PIX**: within noise floor. **GATE-WP**: both greps exit 1 (no matches) — R3 achieved.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "Re-home all assets off WordPress paths: /theme, /vendor, /media (allow-list #9)"
```

---

### Task 11: GitHub Actions deployment

**Files:**
- Create: `.github/workflows/deploy.yml`

**Interfaces:**
- Consumes: `scripts/verify/check_404.py` (Task 1) as the CI link check.
- Produces: Pages deployment on push to `main`; build+link-check on PRs.

- [ ] **Step 1: Write `.github/workflows/deploy.yml`**

```yaml
name: Deploy Hugo site to Pages

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
  workflow_dispatch:

permissions:
  contents: read
  pages: write
  id-token: write

concurrency:
  group: pages
  cancel-in-progress: false

env:
  HUGO_VERSION: 0.163.3

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Install Hugo (pinned, extended)
        run: |
          curl -fsSL "https://github.com/gohugoio/hugo/releases/download/v${HUGO_VERSION}/hugo_extended_${HUGO_VERSION}_linux-amd64.tar.gz" -o /tmp/hugo.tgz
          sudo tar -C /usr/local/bin -xzf /tmp/hugo.tgz hugo
          hugo version
      - name: Configure Pages
        id: pages
        uses: actions/configure-pages@v5
      - name: Build (no minify — DOM-diff parity with local builds)
        run: hugo --baseURL "${{ steps.pages.outputs.base_url }}/"
      - name: Link check
        run: |
          python3 -m http.server 8099 --bind 127.0.0.1 --directory public &
          sleep 1
          python3 scripts/verify/check_404.py http://127.0.0.1:8099 /blog/ /search/
      - uses: actions/upload-pages-artifact@v3
        with:
          path: ./public

  deploy:
    if: github.ref == 'refs/heads/main' && github.event_name == 'push'
    needs: build
    environment:
      name: github-pages
      url: ${{ steps.deployment.outputs.page_url }}
    runs-on: ubuntu-latest
    steps:
      - uses: actions/deploy-pages@v4
        id: deployment
```

- [ ] **Step 2: Local sanity (CI can't run locally; prove the pieces it uses)**

```bash
hugo --quiet && python3 -m http.server 8099 --bind 127.0.0.1 --directory public &
sleep 1
python3 scripts/verify/check_404.py http://127.0.0.1:8099 /blog/ /search/ ; echo "exit $?"
kill %1
```

Expected: exit 0.

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/deploy.yml
git commit -m "Add Pages deployment workflow with pinned Hugo and link-check gate"
```

---

### Task 12: Final sweep, evidence, PR

**Files:**
- Create: `verification/summary.md` (local; pasted into the PR body, not committed)

**Interfaces:**
- Consumes: every gate.

- [ ] **Step 1: Full gate suite on a clean build**

```bash
rm -rf public && hugo --quiet
# reference on :8081, build on :8082, then:
```

Run **GATE-DOM**, **GATE-PIX**, **GATE-404** (with `/blog/ /search/`), **GATE-WP**. All must pass. Then walk `/tmp/dom.diff` hunk-by-hunk one last time writing the mapping into `verification/summary.md`:

```markdown
# Port verification summary
- Reference: origin/main@b4d128a index.html (sha256 <hash>)
- GATE-DOM: <N> hunks, mapped: #1 title, #2 form (…), #3 CV, #4 widgets, #5 search, #6 feed, #7 metadata removals, #8 localhost fixes, #9 paths, #10 blog/rail/404
- GATE-PIX: max AE <n> vs noise floor <n> (shots + diffs attached)
- GATE-404: 0 failures (reference baseline had <n>)
- GATE-WP: 0 matches in repo and built site
```

Any hunk that maps to nothing = STOP, fix before the PR.

- [ ] **Step 2: Console check (interactive)**

With Mohamed's Chrome (DevTools MCP is configured): open `http://127.0.0.1:8082/`, `/#resume-e`, `/#contacts-e`, `/blog/`, `/search/?s=hugo`; read the console on each. Expected: no errors that don't also occur on the reference site (`http://127.0.0.1:8081/`). Record findings in `verification/summary.md`.

- [ ] **Step 3: Push and open the PR**

```bash
git push -u origin hugo-port
gh pr create --base main --head hugo-port \
  --title "Port site from WordPress static export to Hugo" \
  --body-file verification/summary.md
```

Append to the body (edit on GitHub or via `gh pr edit`): the spec path, the allow-list, screenshot pairs (drag-drop the key `verification/pixdiff/diff-*.png` and shots), and the line `🤖 Generated with [Claude Code](https://claude.com/claude-code)`.

- [ ] **Step 4: Switch Pages to workflow builds**

```bash
gh api -X PUT repos/skylight74/skylight74.github.io/pages -f build_type=workflow
```

If this 403s (needs admin token scope): tell Mohamed to flip it manually — repo → Settings → Pages → "Build and deployment" → Source: **GitHub Actions**. Verify: `gh api repos/skylight74/skylight74.github.io/pages --jq .build_type` → `workflow`.

- [ ] **Step 5: CHECKPOINT (Mohamed) — review and merge the PR**

Mohamed reviews the PR (evidence + the site running locally) and merges when satisfied. Do not merge for him.

---

### Task 13: Post-merge deployment watch

**Files:** none.

- [ ] **Step 1: Watch the deploy**

```bash
gh run list --branch main --limit 1
gh run watch $(gh run list --branch main --limit 1 --json databaseId --jq '.[0].databaseId')
```

Expected: `Deploy Hugo site to Pages` completes green.

- [ ] **Step 2: Verify the live site**

```bash
curl -s https://mohamedalyamin.com/ | grep -c "My portfolio"        # expect 1
curl -s https://mohamedalyamin.com/ | grep -c "formspree.io/f/mwvdzgqv"  # expect 1
curl -s -o /dev/null -w "%{http_code}\n" https://mohamedalyamin.com/blog/          # expect 200
curl -s -o /dev/null -w "%{http_code}\n" https://mohamedalyamin.com/theme/style.css # expect 200
curl -s -o /dev/null -w "%{http_code}\n" https://mohamedalyamin.com/media/Mohamed_Aly_Amin_CV.pdf # expect 200
curl -s https://mohamedalyamin.com/ | grep -c "wp-content"          # expect 0
```

- [ ] **Step 3: Real form test on production**

Mohamed (or you, with him watching) submits one real message on the live contact page; confirm it arrives at Formspree. If anything fails: rollback = `git revert -m 1 <merge-commit>` on `main`, push, which redeploys the previous state via the same workflow… but note the pre-merge state was the RAW export served by classic Pages; after Pages is switched to workflow builds, a revert rebuilds the WP export only if the workflow can build it — it cannot (no hugo site at that tree). TRUE rollback: repo → Settings → Pages → Source back to "Deploy from a branch" → `main`. Verify this path is understood BEFORE merging (it is one settings toggle).

- [ ] **Step 4: Record outcome**

Report live-site status to Mohamed; append any deviations to the task board; memory-worthy lessons go to the session memory per his standing rule.

---

## Self-review (performed at authoring time)

- **Spec coverage:** R1→Tasks 2/4–10 gates; R2→6d/6e/7; R3→10 (GATE-WP); R4→7; R5→named gates in every task; R6→11/12/13. Spec §7 features each have a dedicated task; §9 deployment = 11–13; §10 risks: noise floor (Task 2), byte gates (Task 9), whole-dir moves + GATE-404 (Task 10), no-minify (Global Constraints + workflow comment).
- **Placeholder scan:** the YAML `""` fields in Task 9 are extraction targets with locating greps and a byte-identical gate — deliberate procedure, not TBDs; no other "later/appropriate/similar-to" language present.
- **Type consistency:** partial filenames, script CLIs, ports (8081/8082/8099), gate names, and the Formspree endpoint are referenced identically across tasks; task numbering intentionally has no Task 8 (folded into 7 during authoring — numbering kept stable rather than renumbered).

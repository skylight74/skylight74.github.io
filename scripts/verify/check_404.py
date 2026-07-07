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

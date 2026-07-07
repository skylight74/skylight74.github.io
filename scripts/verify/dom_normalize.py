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

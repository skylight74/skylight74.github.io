# Current CV file — for the redesign session

`static/media/Mohamed_Aly_Amin_CV.pdf` (linked by the "Download CV" button in
`layouts/partials/card-profile.html`) was replaced 2026-07-11. The previous
file at that path was the 2022 WordPress-era CV — stale, do not restore it.

Provenance: generated from the attested CV system at
`/home/mohamed/Projects/CV/` — `base` variant (the general-purpose one, not a
role-tailored overlay):

```bash
cd ~/Projects/CV && source .venv/bin/activate && python tailor.py base
# output: output/base/Mohamed_Aly_Amin_CV.pdf
```

Content-verified at replacement time (pdftotext): current entries only
(Dolusoft ~400k eps engine, AINA 2024, attested skills), zero stale claims
(no CISSP/OSCP "in progress", no Flutter, no "5+ years").

Redesign session: keep the same public path (`/media/Mohamed_Aly_Amin_CV.pdf`)
or update the button href in card-profile.html if you move it. Regenerate with
the command above whenever base-cv.yaml changes — never hand-edit the PDF.

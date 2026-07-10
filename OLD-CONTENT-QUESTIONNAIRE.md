# Old-content decisions — mohamedalyamin.com

Source: full audit of `main` (the live production branch), 2026-07-10.
For each item: what's there now, why it matters, my recommendation, and
space for your answer. Answer inline in chat, or edit this file directly
and tell me — either works.

## 1. Privacy (do these regardless of anything else below)

**1.1 — DOB/age script.** `section-about.html` has a hidden `dob` field set
to `1995-12-10` and JavaScript that computes and shows your exact age live
on the page. Recommend: REMOVE entirely.
> Your answer:

**1.2 — Home address map embed.** `section-contacts.html` has a
commented-out Google Maps iframe with the full address İşçi Blokları, 1523.
Sk. No:7, Çankaya/Ankara in the source (comments don't hide from
view-source). Recommend: REMOVE entirely, not just re-comment.
> Your answer:

## 2. Dead "The Legend" branding

**2.1 — Site title.** `hugo.toml`, `head.html` `<title>`, RSS feed title all
say "The Legend" on main. Already replaced with "Mohamed Aly Amin" on the
other branches. Recommend: same replacement on main.
> Your answer:

**2.2 — 404 page.** Title says "The Legend"; the fake terminal prompt says
"root@the-legend". Already fixed elsewhere to "Mohamed Aly Amin" /
"root@mohamedalyamin". Recommend: same fix on main.
> Your answer:

**2.3 — tail-scripts.html elementor JSON.** `"title": "The%20Legend...My%20portifolio"` (misspelled "portifolio") — this one is NOT fixed on any
branch yet. Recommend: fix to "Mohamed Aly Amin".
> Your answer:

**2.4 — Testimonial.** One-word quote "The Legend." from Özgul Doğan
(real colleague, Cyber Security Expert at InterProbe). Options: (a) ask her
for a real quote, (b) keep as an inside joke, (c) drop the testimonial
section. Your call — I have no recommendation here.
> Your answer:

## 3. Professional identity

**3.1 — About paragraph.** Old: "Full stack security developer... rich
experience in Cloud Computing, Crypto & Cyber Security, also I am good at
Big Data-analytics & scientific computing." Already replaced elsewhere with
current DevSecOps/Go-streaming/security-ML framing. Recommend: confirm
you're happy with the replacement text (ask me to show it if you want to
re-read it) and apply to main.
> Your answer:

**3.2 — Typing-title rotator.** Old: "Full-stack Developer / Blockchain
Architect / System Admin / Cyber Security Engineer / Security Researcher."
Already replaced elsewhere with your current target titles. Recommend:
same replacement on main.
> Your answer:

## 4. Experience entries

**4.1 — Interprobe placeholder bullet.** Old title "CYBER SECURITY ANALYST"
with an unedited WordPress template bullet: "Collaborate with creative and
development teams on the execution of ideas." Already corrected elsewhere
with your real title and bullets. Recommend: apply the correction to main.
> Your answer:

**4.2 — "Freelancing / Private Tutor" entry (Oct 2020 – Present).** Bullets:
day trading, high-frequency trading app development, tutoring students in
"Block-chain / Malware development," private Python/JAVA/SQL lessons. Not
on the current site. My recommendation: never revive this — malware-dev
framing reads badly for a security professional, and day-trading/HFT sits
in the grey-to-excluded zone of your own halal rules. Flagging for your
explicit confirmation since I don't decide your values calls.
> Your answer:

**4.3 — Boraq dates.** Old WordPress content says "Jun 2018 – Sep 2018."
LinkedIn (live-verified 2026-07-07) says "Jul 2018 – Sep 2018," which is
what's currently on the synced site. Recommend: keep Jul, treating the
more recently verified LinkedIn read as the tiebreaker. Need your
confirmation either way.
> Your answer:

**4.4 — Apply Center entry.** Old site: "System Admin, Jun 2019 – Jan 2020,"
bullet about deploying CRMs / an e-commerce site. My notes elsewhere say
"Sales/IT Manager 2019-20 (Odoo/Zoho ERP)" for the same company/period —
different title, different work described. This entry isn't on the current
site at all (never restored, unlike Boraq). Please explain: which
title/description is accurate, and do you want this entry on the site?
> Your answer:

**4.5 — Chairman entry (METU International Student Association).** Old
bullet: "Optimize website and apps performance using latest technology" —
clearly copy-pasted from a different role, doesn't describe chairman
duties. Old end date "Sep 2018"; my LinkedIn notes say "Jan 2019" for the
same role. If you want this on the site: please give me real bullets
(I have "200+ members, 5+ events" from other notes — confirm or correct)
and the right end date.
> Your answer:

## 5. Education entries

**5.1 — Interprobe "Cyber security specialty program" (May 2021 – Nov
2021).** "Certificate preparation program at Interprobe" with pentesting/
security-tools/malware-analysis coursework. This is the literal old
WordPress content, never verified against LinkedIn or your CV. Please
explain: is this real and worth keeping as a separate entry, or should it
be dropped/merged into the Interprobe experience entry?
> Your answer:

**5.2 — MIT Enterprise Forum Pan Arab (Dec 2017 – Jan 2018, Amman,
Jordan).** More precise dates/location than what I have elsewhere for the
"2017 Pan-Arab finalist" honor. Please explain: same event (a leadership
program that included the finalist result), or two different things? If
the same, can I use these dates on the honors entry?
> Your answer:

**5.3 — Royal International Language Schools (high school, 2003–2013,
Cairo).** Has a personal bullet: "Build My first PC when I was 12." Doesn't
belong in a professional Education section (you already went
university-only there on purpose), and it's a soft age-signal stacked on
top of the DOB leak above. Recommend: drop from Education regardless of
what you decide about item 1.1. Could live as a fun fact elsewhere if you
want the personal color — your call.
> Your answer:

## 6. Services

**6.1 — Old "My Services."** Includes "Statistical/Technical Analysis:
Crypto currencies tracking, Day & High frequency trading" as an offered
service. Recommend: REMOVE — directly contradicts your current halal
constraints.
> Your answer:

**6.2 — Old "Service Offerings."** Freelancing tier includes "Web3
Development (new)"; Contract tier includes "Blockchain Security (new)" and
"Financial Consultancy (new)." Recommend: REMOVE — a ready replacement
already exists (`data/services.yaml`: DevSecOps & Cloud Security / Go
Backend & Streaming / Security Assessment & Technical Due Diligence, no
pricing). Please confirm you're happy with that replacement's wording.
> Your answer:

## 7. Fun facts

**7.1 — "Black belt in Karate / Ex-Body builder."** Not in my notes
anywhere; I can't verify it. Keep, remove, or update?
> Your answer:

**7.2 — "International Chemistry Olympiad Student (USA)."** Consistent
with your attested IChO 2012 fact (Washington D.C. is in the USA).
Recommend: keep.
> Your answer:

**7.3 — "Fluent in 3 Languages."** Consistent with Arabic/Turkish/English.
Recommend: keep.
> Your answer:

**7.4 — "5 Countries Visited."** Likely outdated. Keep, update the number,
or remove?
> Your answer:

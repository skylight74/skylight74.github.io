# Fact Audit — Rendered Site vs. Attested Sources

Audited: `public/index.html`, `public/for/{devsecops,platform,ai-security,research,lead}/index.html` (built site, 2026-07-12). Blog post content excluded (owner-written).

**Structural note confirmed before auditing**: the Experience, Projects, Skills, and Research&Honors/Services/Contact blocks are **byte-identical** across all 6 pages (verified via `diff`); only section-label cosmetic text (`01 experience --core` vs `Experience`) differs. Hero (kicker/summary/open-to) and the Impact-stats selection/order are the only per-page content. Accordingly, sections D–I below are audited once each and apply to all 6 pages; section A is audited per page.

Verdict legend: **ATTESTED** = site text matches an attested source verbatim (or near-verbatim, single field) — cite file:line. **DERIVED-OK** = mechanical reformat/condensation/combination of attested value(s), same underlying fact — source shown. **MISMATCH** = site says X, source says Y, contradiction. **UNSOURCED** = no source found for the specific claim.

---

## Known-rule compliance checklist (grep-verified across all 6 pages)

| Rule | Result |
|---|---|
| No phone number anywhere on site | PASS — no `phone`/`tel:` string in any of the 6 pages (contact.yaml:6 has one but it is correctly never rendered) |
| Cert line says planned/not-started, never claims certs held | PASS — "planned 2026: AWS Solutions Architect – Associate", "then CKA" |
| No LLM / TTS / speech-recognition capability claims | PASS — no match for LLM/TTS/"speech recognition"/NLP anywhere on site |
| "MIT-EF Jan 2018" must not say "finalist" | PASS — no "finalist" string anywhere on site |
| MISA/Chairman end = Jan 2019, displayed "2016 — 2019" OK | PASS — site shows "2016 — 2019" (volunteering.yaml period ends Jan 2019 per 2026-07-11 resolution) |

---

## A. Hero — per-page fields (kicker / summary / open-to), 6 pages

| Page | Field | Site text | Source | Verdict |
|---|---|---|---|---|
| `/` (core) | kicker | "Security & Infrastructure Engineering" | presets.yaml:16 | ATTESTED |
| `/` (core) | summary | "I work where security meets systems: formed and led a six-engineer DevSecOps team, sole-built a real-time Go detection engine, and first-authored a peer-reviewed intrusion-detection paper (AINA 2024, Springer)." | presets.yaml:17 (verbatim); facts also in resume.yaml:67, publications.yaml:12-22 | ATTESTED |
| `/` (core) | open-to | "DevSecOps · Platform/Backend (Go) · Security · Tech Lead" | presets.yaml:18; profile.yaml:24 | ATTESTED |
| `/for/devsecops/` | kicker | "DevSecOps — pipelines, detection, response" | presets.yaml:25 | ATTESTED |
| `/for/devsecops/` | summary | "DevSecOps engineer: formed and led a six-engineer team that shipped a SIEM/SOAR platform in 3 months; builds secure CI/CD, Kubernetes, and Terraform pipelines; sole-built a real-time Go detection engine at ~400k events/sec (SAST/DAST/SCA · ELK/Wazuh/Sentinel · AWS)." | presets.yaml:26 (verbatim); facts in resume.yaml:67, skills.yaml:14-16 | ATTESTED |
| `/for/devsecops/` | open-to | "DevSecOps · Security Engineering · Platform/Cloud" | presets.yaml:27 | ATTESTED |
| `/for/platform/` | kicker | "Platform & Backend — Go, streaming, scale" | presets.yaml:34 | ATTESTED |
| `/for/platform/` | summary | "Backend engineer for high-throughput Go systems: sole-built a Kafka → ClickHouse stream engine sustaining ~400k events/sec (Redis, OpenTelemetry), rebuilt a 270k-user platform into 10 microservices on AWS — event-driven architecture, gRPC, distributed systems." | presets.yaml:35 (verbatim); facts in resume.yaml:14,22; roles/go-backend.yaml:3 | ATTESTED |
| `/for/platform/` | open-to | "Platform / Backend (Go) · SRE · DevSecOps" | presets.yaml:36 | ATTESTED |
| `/for/ai-security/` | kicker | "AI × Security — detection models in production" | presets.yaml:43 | ATTESTED |
| `/for/ai-security/` | summary | "Detection engineer bridging security and applied ML: 98.11% IDS accuracy (one-class SVM, AINA 2024), the OSSArch SIEM/SOAR stack, and a ~400k events/sec Go engine with detection-as-code anomaly rules — MLOps on GPU Kubernetes." | presets.yaml:44 (verbatim, matches roles/detection-secml.yaml:3 almost word-for-word incl. "detection-as-code") | ATTESTED |
| `/for/ai-security/` | open-to | "AI Security / Detection Engineering · Security-ML · DevSecOps" | presets.yaml:45 | ATTESTED |
| `/for/research/` | kicker | "Security-ML Research — detection, published" | presets.yaml:52 | ATTESTED |
| `/for/research/` | summary (general) | "ML research engineer, first author at AINA 2024 (Springer) — 98.11% intrusion-detection accuracy (one-class SVM; LSTM sequence models in PyTorch); built the end-to-end GPU training pipeline on Kubernetes for the DIONA IDS (…) and a container-specific vulnerability dataset." | presets.yaml:53 (verbatim); roles/research-ml.yaml:4-9; resume.yaml:57-59 | ATTESTED |
| `/for/research/` | **summary sub-claim** | "…for the DIONA IDS (**TÜBİTAK #120E537**, Koç Sistem pilot)…" | See finding below | **UNSOURCED** |
| `/for/research/` | open-to | "Security-ML Research · Detection Engineering · Applied ML R&D" | presets.yaml:54 | ATTESTED |
| `/for/lead/` | kicker | "Engineering Leadership — teams that ship" | presets.yaml:64 | ATTESTED |
| `/for/lead/` | summary | "I build teams that ship: formed and led six engineers to deliver an MVP SIEM/SOAR in 3 months vs the 6–12 month norm, trained 4 engineers and ASELSAN partners, taught at university, and led a 200+ member organization." | presets.yaml:65 (verbatim); resume.yaml:67,70; volunteering.yaml:11 | ATTESTED |
| `/for/lead/` | open-to | "Tech Lead / Engineering Lead · DevSecOps · Platform · Security" | presets.yaml:66 | ATTESTED |

**Finding (UNSOURCED)**: `data/presets.yaml:53` ties DIONA specifically to **"TÜBİTAK #120E537"**. Grepped every allowed source (`master-cv.yaml`, `base-cv.yaml`, all `roles/*.yaml`, all `data/*.yaml`, REBUILD-STATE.md) for `120E537`: it is attested **only** for (a) the AINA 2024 paper (publications.yaml:22, master-cv.yaml:420) and (b) the MedPower 2024 paper / SmartGridLedger project (publications.yaml:29, master-cv.yaml:240,430). DIONA's own entries (master-cv.yaml:215-229, data/projects.yaml:11-14, base-cv.yaml:62) say only generic **"TÜBİTAK-funded"** — never a grant number. No source ties #120E537 to DIONA specifically; the pairing appears to be an inference made when drafting the preset copy. The *generic* "TÜBİTAK-funded" claim about DIONA is attested (data/projects.yaml:11); the *specific grant number* attached to DIONA is not.

---

## B. Hero — shared fields (identical text on all 6 pages)

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Availability | "Available — freelance / full-time" | profile.yaml:32 | ATTESTED |
| Location line | "Ankara — Istanbul / relocation / remote" | profile.yaml:33; consistent with base-cv.yaml:9 "Ankara, Turkey — open to Istanbul / relocation" | ATTESTED |
| Languages line | "Arabic (native) · English (C1) · Turkish (B2)" | profile.yaml:34; consistent with skills.yaml:26, base-cv.yaml:20 | ATTESTED |
| Rotating roles list | ["DevSecOps Engineer","Security Engineer","Backend Developer (Go)","Site Reliability Engineer","Tech Lead","Security-ML Researcher"] | profile.yaml:26-31 (exact list/order) | ATTESTED |

---

## C. Impact stats — 7 distinct stat definitions (presets.yaml:4-10)

| Stat | Site num + label | Source | Verdict |
|---|---|---|---|
| eps | "~400k/s" — "security events — Go detection engine, 80+ sources" | presets.yaml:4 (verbatim); resume.yaml:22 "~400k events/sec … 80+ sources" | ATTESTED |
| acc | "98.11%" — "IDS accuracy — first-author, AINA 2024 (Springer)" | presets.yaml:5 (verbatim); resume.yaml:57 "98.11% intrusion-detection accuracy … first-author … AINA 2024 (Springer)" | ATTESTED |
| mvp | "3 mo" — "SIEM/SOAR MVP shipped vs 6–12 mo company norm" | presets.yaml:6 (verbatim); resume.yaml:67 "3 months, against the company's 6-12 month norm" | ATTESTED |
| team | "6 eng" — "DevSecOps team formed, led, and trained" | presets.yaml:7 (verbatim); resume.yaml:67 "6-engineer DevSecOps team" (formed+led attested) | DERIVED-OK — see note |
| users | "270k" — "user platform rebuilt as 10 microservices on AWS" | presets.yaml:8 (verbatim); resume.yaml:14 "270k registered users … 10-microservice … on AWS" | ATTESTED |
| papers | "2" — "peer-reviewed papers — AINA (Springer) · MedPower, TÜBİTAK-funded" | presets.yaml:9 (verbatim); publications.yaml (exactly 2 entries, AINA-Springer + MedPower-TÜBİTAK) | ATTESTED |
| trained | "4+" — "engineers & ASELSAN partners trained" | presets.yaml:10 (verbatim); resume.yaml:70 "Trained 4 engineers and ASELSAN partners" | ATTESTED |

**Note on "team" stat**: the number (6 eng) and "formed"/"led" are solidly attested (resume.yaml:67). The verb **"trained"** applied to this same 6-person cohort has no direct source — the only attested "trained" claim in any source is scoped to a *different* headcount ("4 engineers and ASELSAN partners", resume.yaml:70/master-cv.yaml:138), not stated to be the same people as the 6-engineer team. Not flagged as MISMATCH/UNSOURCED because leading a newly-formed team plausibly and ordinarily includes training it, and no source contradicts it — but the specific word choice is a synthesis, not a quote, so DERIVED-OK rather than a clean ATTESTED.

Per-page stat **selection and order** (mechanical, cross-checked against presets.yaml `stats:` arrays) also verified correct on all 6 pages: core=[eps,acc,mvp,team], devsecops=[mvp,acc,eps,team], platform=[eps,users,acc,team], aisec=[acc,eps,mvp,team], research=[acc,eps,mvp,papers], lead=[team,mvp,trained,eps]. ATTESTED.

---

## D. Experience — 8 jobs + 1 education (resume.yaml; identical on all 6 pages)

### D1. Independent / Freelance — Security & Software Engineer

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Jan 2023 — Present" | resume.yaml:7 "Jan 2023 - Present" (em-dash reformat) | DERIVED-OK |
| Title | "Security & Software Engineer" | resume.yaml:8 | ATTESTED |
| Company | "INDEPENDENT / FREELANCE" | resume.yaml:9 | ATTESTED |
| Qualifier | "project-based" | resume.yaml:10 | ATTESTED |
| Bullet 1 | "Sole-built the 10-microservice PHP/Laravel rebuild of a live housing-listings platform (270k registered users, 360k+ cumulative listings) on AWS (EC2, RDS, S3); rebuild currently in staging." | resume.yaml:14; base-cv.yaml:43 | ATTESTED |
| Bullet 2 | "Built protocol adapters (OCPP for EV chargers, SunSpec/Modbus for solar inverters, IEC 62056 for smart meters) to normalize multi-vendor energy-device data for a smart-grid monitoring stack (InfluxDB, Grafana)." | resume.yaml:15; base-cv.yaml:44 | ATTESTED |

*(Note: master-cv.yaml:371 archive figure "61k+ active listings" differs from resume.yaml's "360k+ cumulative listings" — different metrics (active vs. cumulative), and resume.yaml is the priority-1, explicitly-synced source per its own header comment, so this is not scored as a mismatch. Flagged here for owner awareness only.)*

### D2. Dolusoft — R&D Engineer — Cybersecurity

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Aug 2024 — May 2025" | resume.yaml:17 "Aug 2024 - May 2025" | DERIVED-OK |
| Title | "R&D Engineer — Cybersecurity" | resume.yaml:18 | ATTESTED |
| Company | "DOLUSOFT" | resume.yaml:19 | ATTESTED |
| Bullet 1 | "Sustained ~400k events/sec by sole-designing and building a modular real-time filtering engine in Go, ingesting security events from 80+ sources (Kafka in, filter/aggregate, ClickHouse out, Redis-backed state, OpenTelemetry tracing)." | resume.yaml:22; base-cv.yaml:52 | ATTESTED |
| Bullet 2 | "Designed chained, first-seen, and z-score anomaly-detection rules in a configurable detection engine to cut false-positive noise." | resume.yaml:23; consistent with master-cv.yaml:80 (which also states a 40% figure not repeated on-site — under-claim, not an issue) | ATTESTED |
| Bullet 3 | "Built a Go multi-channel alerting service (Slack, email, webhook) with rate limiting and policy-based routing for faster incident response." | resume.yaml:24; base-cv.yaml:54 (verbatim) | ATTESTED |

### D3. Castrum — Technical Consultant

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Dec 2023 — Apr 2024" | resume.yaml:30, with attestation comment (lines 28-29): "End month corrected 2026-07-11, his attestation: 'Dec 2023 to late April 2024'. LinkedIn shows May 2024 — off by one, his-hands fix queued." | DERIVED-OK (em-dash) / date itself ATTESTED via comment |
| Title | "Technical Consultant" | resume.yaml:31 | ATTESTED |
| Company | "CASTRUM" | resume.yaml:32 | ATTESTED |
| Qualifier | "contract" | resume.yaml:33 | ATTESTED |
| Bullet 1 | "Assessed the codebases, architecture, and technical risk of 15-20 startup ventures to support investment decisions." | resume.yaml:36 | ATTESTED |
| Bullet 2 | "Joined investor meetings and produced feasibility assessments for prospective deals." | resume.yaml:37 | ATTESTED |

### D4. Sardis — Web3 Security Engineer / DevSecOps

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Sep 2023 — Jul 2024" | resume.yaml:41, with attestation comment (line 39-40): "Dates his final attestation 2026-07-11: Sep 2023 - Jul 2024 (start was September; LinkedIn's Jul 2023 start wrong, its Jul 2024 end right)." | DERIVED-OK (em-dash) / date itself ATTESTED via comment |
| Title | "Web3 Security Engineer / DevSecOps" | resume.yaml:42 | ATTESTED |
| Company | "SARDIS" | resume.yaml:43 | ATTESTED |
| Qualifier | "contract" | resume.yaml:44 | ATTESTED |
| Bullet 1 | "Investigated and remediated a security incident on the network's web infrastructure." | resume.yaml:47 | ATTESTED |
| Bullet 2 | "Reviewed and redesigned infrastructure, and quality-reviewed smart contracts written by the core team." | resume.yaml:48 | ATTESTED |
| Bullet 3 | "Top contributor to the network's StakeDex staking-platform frontend (7 commits, TypeScript)." | resume.yaml:49; corroborated master-cv.yaml:109,472 ("7 commits", "TypeScript frontend") | ATTESTED |

### D5. METU — Research Assistant (TÜBİTAK-funded)

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Jan 2022 — Apr 2025" | resume.yaml:51 "Jan 2022 - Apr 2025" | DERIVED-OK |
| Title | "Research Assistant (TÜBİTAK-funded)" | resume.yaml:52 | ATTESTED |
| Company | "MIDDLE EAST TECHNICAL UNIVERSITY (METU)" | resume.yaml:53 | ATTESTED |
| Qualifier | "part-time" | resume.yaml:54 | ATTESTED |
| Bullet 1 | "Reached 98.11% intrusion-detection accuracy by developing an SGD one-class SVM model for containerized microservices; first-author publication at AINA 2024 (Springer)." | resume.yaml:57; base-cv.yaml:61; master-cv.yaml:417 | ATTESTED |
| Bullet 2 | "Created a container-specific vulnerability dataset for Kubernetes environments as part of the AINA 2024 work." | resume.yaml:58; master-cv.yaml:96 | ATTESTED |
| Bullet 3 | "Built the end-to-end GPU training pipeline (GPU-enabled Kubernetes cluster, dataset curation, evaluation) for the DIONA LSTM intrusion-detection system." | resume.yaml:59; base-cv.yaml:62 | ATTESTED |

### D6. Interprobe Bilgi Teknolojileri — Cyber Security Specialist (Tech Lead — OSSArch)

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Jul 2021 — May 2022" | resume.yaml:61 "Jul 2021 - May 2022"; base-cv.yaml:66-67 (2021-07 to 2022-05) | DERIVED-OK |
| Title | "Cyber Security Specialist (Tech Lead — OSSArch)" | resume.yaml:62; base-cv.yaml:64 | ATTESTED |
| Company | "INTERPROBE BILGI TEKNOLOJILERI" | resume.yaml:63 | ATTESTED |
| Bullet 1 | "Shipped an MVP SIEM/SOAR platform (OSSArch) in 3 months, against the company's 6-12 month norm, by forming and technically leading a 6-engineer DevSecOps team (TDD, DDD, daily agile)." | resume.yaml:67; base-cv.yaml:69 | ATTESTED |
| Bullet 2 | "Automated threat enrichment by integrating ELK, Wazuh, TheHive, Cortex, MISP, and Suricata with OSINT feeds (VirusTotal, AlienVault OTX)." | resume.yaml:68; base-cv.yaml:70 (verbatim) | ATTESTED |
| Bullet 3 | "Deployed EDR/EPP, WAF, and DNS-security solutions for enterprise clients, including a defense contractor and a municipality, and ran a penetration test for the municipal client." | resume.yaml:69; corroborated master-cv.yaml:133-134 ("Bayraktar, Sakarya Belediyesi" = a defense contractor + a municipality) | ATTESTED |
| Bullet 4 | "Trained 4 engineers and ASELSAN partners and taught a Cyber Security course at Istanbul Aydın University; ran pre-sales demos/POCs at GISEC Dubai." | resume.yaml:70; base-cv.yaml:71 (verbatim, incl. "GISEC Dubai") | ATTESTED |

*(Note: master-cv.yaml:123-127 has an older/superseded Interprobe date range (2020-12–2022-05) plus a separate "Junior Engineer/Intern" entry not reflected on-site; base-cv.yaml's corrected span (2021-07–2022-05) matches resume.yaml/site. Archive discrepancy, not a site mismatch — resume.yaml is priority-1 and explicitly states it was synced to base-cv.yaml + LinkedIn.)*

### D7. Apply Center (Startup) — Sales / IT Manager

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Jun 2019 — Jan 2020" | resume.yaml:75 "Jun 2019 - Jan 2020" | DERIVED-OK |
| Title | "Sales / IT Manager" | resume.yaml:76 | ATTESTED |
| Company | "APPLY CENTER (STARTUP)" | resume.yaml:77 | ATTESTED |
| Bullet 1 | "Administered the company's Zoho CRM and ran its social media as Sales/IT Manager." | resume.yaml:81 | ATTESTED |
| Bullet 2 | "Worked with Odoo/Zoho ERP tooling across sales and IT operations." | resume.yaml:82 | ATTESTED |

### D8. Boraq-Group — Business Development Analyst

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Jul 2018 — Sep 2018" | resume.yaml:86, with attestation comment (lines 72-74): "RESOLVED 2026-07-10 (his answer): Jul confirmed over Jun. … LinkedIn (live-verified 2026-07-07) also said Jul-Sep 2018." | DERIVED-OK (em-dash) / date ATTESTED via comment |
| Title | "Business Development Analyst" | resume.yaml:87 | ATTESTED |
| Company | "BORAQ-GROUP" | resume.yaml:88 | ATTESTED |
| Bullet 1 | "Developed proposals and pitched them to leads and prospective clients." | resume.yaml:92 | ATTESTED |
| Bullet 2 | "Maintained business relationships and CRM records through direct and indirect research and market analysis." | resume.yaml:93 | ATTESTED |

### D9. Education — METU

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Period | "Class of 2023" | resume.yaml:96 (verbatim, not a range needing reformat) | ATTESTED |
| Title | "Middle East Technical University (METU)" | resume.yaml:97 | ATTESTED |
| Place | "Ankara, Turkey" | resume.yaml:98 | ATTESTED |
| Body | "BSc Computer Engineering, Cyber Security specialization. Türkiye Bursları (Turkish Government Scholarship) recipient. Built a containerized MPLAB X development environment used by METU embedded-systems students." | resume.yaml:100; base-cv.yaml:101-105; MPLAB detail corroborated master-cv.yaml:318-329 | ATTESTED |

---

## E. Projects — 5 cards + 2 open-source rows (data/projects.yaml, data/open_source.yaml)

| Item | Site text | Source | Verdict |
|---|---|---|---|
| P1 name+date | "Real-Time Detection Engine (Go)" / "2024 – 2025" | projects.yaml:7-8 | ATTESTED |
| P1 body | "Kafka → ClickHouse stream-processing engine at ~400k events/sec with Redis state, OpenTelemetry observability, and a configurable detection + alerting rule engine." | projects.yaml:9 | ATTESTED |
| P2 name+date | "DIONA — LSTM Intrusion-Detection System" / "2022 – 2025" | projects.yaml:11-12 | ATTESTED |
| P2 body | "TÜBİTAK-funded research system at METU; built the end-to-end GPU training pipeline (…). Ran an enterprise pilot at Koç Sistem (Azure Sentinel/Defender integration phase). Ecosystem repos: ML training engine, GPU-LSTM prediction engine, and a Kubernetes IoT monitoring stack (ai4iot)." | projects.yaml:14, with attestation comment: "Koç Sistem pilot confirmed true + public by Mohamed 2026-07-11." | ATTESTED |
| P2 link | github.com/nightswall/DIONA | projects.yaml:13 | ATTESTED |
| P3 name+date | "SmartGridLedger — Permissioned Blockchain (Hyperledger Fabric)" / "2024" | projects.yaml:15-16 | ATTESTED |
| P3 body | "Authored original multi-org Fabric chaincode (TypeScript) on a Kubernetes + Istio network for secure energy-distribution settlement; published at MedPower 2024 (TÜBİTAK-funded)." | projects.yaml:19; base-cv.yaml:76 | ATTESTED |
| P3 links | github.com/skylight74/SmartGridLedger + gitbook docs link | projects.yaml:17-18 | ATTESTED |
| P4 name+date | "OSSArch — Open-Source SIEM/SOAR Platform" / "2021 – 2022" | projects.yaml:23-24 | ATTESTED |
| P4 body | "Modular NOC/SOC platform with tiered packaging (Zero-Dollar, Pro, Enterprise), built entirely on open source: TheHive, Cortex, MISP, ELK/Beats, Grafana, Snort, Wazuh, Zabbix, pfSense — with OSINT enrichment from VirusTotal and AlienVault OTX. Designed and delivered by the 6-engineer team formed and led at Interprobe." | projects.yaml:25; corroborated master-cv.yaml:252-256 | ATTESTED |
| P5 name+date | "Container Security Vulnerability Assessment" / "2022" | projects.yaml:26-27 | ATTESTED |
| P5 body | "Security assessment of microservices applications (Pitstop, Robot Shop, Kubernetes Goat); identified and documented SQL injection, NoSQL injection, RabbitMQ, and SMTP vulnerabilities. Groundwork for the AINA 2024 container-security research." | projects.yaml:28; corroborated master-cv.yaml:263-266 | ATTESTED |
| OSS1 | "waybar-crypto — PR #105 (merged): custom formatting options — global and per-coin format strings, case-sensitive ticker support." + link | open_source.yaml:5-7; corroborated master-cv.yaml:441-448 | ATTESTED |
| OSS2 | "opencode — PR #9208: documented the theme hot-reloading feature (SIGUSR2), discovered through source-code analysis." + link | open_source.yaml:8-10; corroborated master-cv.yaml:459-465 | ATTESTED |

*(Note: StakeDex PR is deliberately not listed as a separate open-source row per open_source.yaml:1-3 comment — it's folded into the Sardis experience bullet instead (D4, bullet 3), consistent with master-cv.yaml. Not a gap.)*

---

## F. Skills — every tag, all 7 groups (data/skills.yaml)

### Security & DevSecOps (skills.yaml:14)
| Tag | Verdict | Note |
|---|---|---|
| DevSecOps | ATTESTED | |
| secure SDLC | ATTESTED | |
| SAST/DAST/SCA | ATTESTED | |
| SIEM/SOAR (ELK · Wazuh · TheHive · MISP · Suricata · Sentinel) | DERIVED-OK | source also lists Cortex + "Microsoft Sentinel"; site drops Cortex, shortens Sentinel — subset, not fabrication |
| detection engineering | ATTESTED | |
| MITRE ATT&CK | ATTESTED | |
| threat hunting | ATTESTED | |
| red/blue team | ATTESTED | |
| Burp · ZAP · Nmap · Metasploit | DERIVED-OK | source: "penetration-testing tooling (Burp Suite, OWASP ZAP, Nmap, Metasploit)" — abbreviated |
| FortiGate (NGFW) | ATTESTED | |
| ISO 27001 | ATTESTED | |
| GDPR & CIS (familiarity) | DERIVED-OK | source: "GDPR & CIS Controls (familiarity)" |

### Cloud & Infrastructure (skills.yaml:16)
| Tag | Verdict | Note |
|---|---|---|
| Kubernetes (Helm · Istio) | DERIVED-OK | source also lists Skaffold, Kind, MetalLB |
| Docker | ATTESTED | |
| Terraform | ATTESTED | |
| Ansible | ATTESTED | |
| AWS | DERIVED-OK | source: "AWS (EC2, Lightsail, RDS, S3, IAM)" — site drops parenthetical |
| CI/CD · GitOps | DERIVED-OK | source lists as two separate items, merged here |
| OpenTelemetry | ATTESTED | |
| Prometheus · Grafana | DERIVED-OK | source lists as two separate items, merged here |
| Proxmox | ATTESTED | |

### Backend & Streaming (skills.yaml:18)
| Tag | Verdict | Note |
|---|---|---|
| Go | ATTESTED | |
| Python | ATTESTED | |
| C# | ATTESTED | |
| TypeScript | ATTESTED | |
| Bash | ATTESTED | |
| Kafka | ATTESTED | |
| ClickHouse | ATTESTED | |
| Redis | ATTESTED | |
| PostgreSQL | ATTESTED | |
| gRPC · REST | DERIVED-OK | source lists as two separate items, merged here |
| event-driven | DERIVED-OK | source: "event-driven architecture" |

### Applied ML & Data (skills.yaml:20)
| Tag | Verdict | Note |
|---|---|---|
| PyTorch | ATTESTED | |
| TensorFlow | ATTESTED | |
| scikit-learn | ATTESTED | |
| MLOps (GPU K8s) | DERIVED-OK | source: "MLOps (GPU Kubernetes, training pipelines)" |
| anomaly detection | ATTESTED | |
| LSTM | ATTESTED | |

### Fintech & DLT (skills.yaml:22)
| Tag | Verdict | Note |
|---|---|---|
| technical due diligence | DERIVED-OK | source: "Technical due diligence (crypto/fintech ventures)" |
| Hyperledger Fabric (chaincode) | DERIVED-OK | source: "Hyperledger Fabric (permissioned chaincode)" |
| transaction-grade streaming | DERIVED-OK | source: "…streaming pipelines" |
| PCI DSS & SOC 2 (familiarity) | ATTESTED | |

### Certifications (skills.yaml:24) — rule-critical group
| Tag | Verdict | Note |
|---|---|---|
| "planned 2026: AWS Solutions Architect – Associate" | DERIVED-OK | source: "Planned for 2026: AWS Solutions Architect – Associate, then…" — split into 2 tags, no certs claimed as held |
| "then CKA" | DERIVED-OK | source: "…then Certified Kubernetes Administrator (CKA)" — acronym only |

*(Master-cv.yaml:48-67 lists a different, outdated cert set — AZ-500/CISSP/OSCP, all "Pursuing"/"Expected 2026" — vs. skills.yaml's AWS-SAA/CKA. REBUILD-STATE.md:51 explicitly flags this as a known archive/current inconsistency, resolved in favor of skills.yaml. Site correctly uses skills.yaml's version and correctly states nothing as obtained. ATTESTED overall, footnoted for visibility.)*

### Languages (skills.yaml:26)
| Tag | Verdict | Note |
|---|---|---|
| Arabic (native) | ATTESTED | matches profile.yaml:34 exactly |
| English (C1) | ATTESTED | matches profile.yaml:34 exactly |
| Turkish (B2) | ATTESTED | matches profile.yaml:34 exactly |

**Skills section totals: 47 tags — 32 ATTESTED, 15 DERIVED-OK, 0 MISMATCH, 0 UNSOURCED.** No dropped/omitted source items (MongoDB, OpenCV, InfluxDB, "vulnerability management", etc.) constitute a problem — omission ≠ unbacked claim.

---

## G. Research — 2 publications + 3 honors + leadership block

### Publications (data/publications.yaml)
| Field | Site text | Source | Verdict |
|---|---|---|---|
| Pub1 title | "Misuse Detection and Response for Orchestrated Microservices Based Software" | publications.yaml:12 | ATTESTED |
| Pub1 venue/year | "AINA 2024 / Springer" (stacked) | publications.yaml:13 "AINA 2024 (Springer)" | DERIVED-OK |
| Pub1 authors | "Mohamed Aly Amin · Adnan Harun Dogan · Elif Sena Kuru · Yigit Sever · Pelin Angin" | publications.yaml:15-19 | ATTESTED |
| Pub1 link | link.springer.com/chapter/10.1007/978-3-031-57942-4_22 | publications.yaml:21 | ATTESTED |
| Pub1 note | "Presented in Japan. Supported by TÜBİTAK Grant #120E537 and the TÜBA GEBİP Program." | publications.yaml:22 | ATTESTED |
| Pub2 title | "Permissioned Blockchain-Based Monitoring Framework for DER-Integrated Distribution Networks" | publications.yaml:23 | ATTESTED |
| Pub2 venue/year | "MedPower / 2024" (stacked) | publications.yaml:24,28 "MedPower 2024" | DERIVED-OK |
| Pub2 authors | "Pelin Angin · et al. (incl. Mohamed Aly Amin)" | publications.yaml:25-27 | ATTESTED |
| Pub2 note | "TÜBİTAK Grant #120E537." | publications.yaml:29 | ATTESTED |

### Honors + leadership (data/honors.yaml, data/volunteering.yaml)
| Field | Site text | Source | Verdict |
|---|---|---|---|
| Leadership year | "2016 — 2019" | volunteering.yaml:7 "Apr 2016 - Jan 2019" (condensed to years); end date per 2026-07-11 resolution, REBUILD-STATE.md known-rule confirms this display is OK | DERIVED-OK |
| Leadership title | "Chairman — METU International Student Association" | volunteering.yaml:8-9 (role + organization fields combined) | DERIVED-OK |
| Leadership detail | "Led the 200+ member International Student Association at Middle East Technical University." | volunteering.yaml:11 (verbatim); corroborated master-cv.yaml:185 | ATTESTED |
| Honor1 year | "2012" | honors.yaml:10 (extracted from title string; no separate year field) | DERIVED-OK |
| Honor1 title | "International Chemistry Olympiad (IChO) 2012" | honors.yaml:10 | ATTESTED |
| Honor1 detail | "Egypt national team, competition held in Washington, D.C.; certificate of participation (no medal)." | honors.yaml:11 | ATTESTED |
| Honor2 year | "2014" | honors.yaml:12 (extracted from title string) | DERIVED-OK |
| Honor2 title | "Türkiye Bursları (Turkish Government Scholarship), 2014" | honors.yaml:12 | ATTESTED |
| Honor2 detail | "Funded his BSc studies at Middle East Technical University (METU)." | honors.yaml:13 | ATTESTED |
| Honor3 year | "2018" | honors.yaml:16 (extracted from title string) | DERIVED-OK |
| Honor3 title | "MIT Enterprise Forum Pan-Arab, January 2018" | honors.yaml:16, with attestation comment (lines 14-15): "Date + framing corrected 2026-07-10 per Mohamed: three-day intensive in January (old site's 'Dec 2017 - Jan 2018' span resolved to Jan 2018)." No "finalist" claim (rule compliance). | ATTESTED |
| Honor3 detail | "Three-day intensive entrepreneurship program (Amman, Jordan)." | honors.yaml:17 | ATTESTED |

---

## H. Services — 5 entries (data/services.yaml)

| # | Site title + body | Source | Verdict |
|---|---|---|---|
| 1 | "DevSecOps & Cloud Security" — "CI/CD hardening, SIEM/SOAR on open-source stacks (ELK, Wazuh, TheHive), Kubernetes and Terraform infrastructure, AWS security." | services.yaml:7-8 | ATTESTED |
| 2 | "Go Backend & Streaming Systems" — "Real-time pipelines in Go with Kafka, ClickHouse, and Redis; built a filtering engine sustaining ~400k events/sec with OpenTelemetry tracing end to end." | services.yaml:9-10 | ATTESTED |
| 3 | "Security Assessment & Technical Due Diligence" — "Architecture and code review, penetration-testing tooling, and technical due diligence on venture investments (15+ ventures assessed)." | services.yaml:11-12 | ATTESTED |
| 4 | "Web3 Development" — "Permissioned blockchain development on Hyperledger Fabric: multi-org chaincode in TypeScript on Kubernetes, published at MedPower 2024 (SmartGridLedger)." | services.yaml:14-15 | ATTESTED |
| 5 | "Blockchain Security" — "Smart-contract and chaincode review, permissioned-network security, and technical risk assessment of crypto ventures." | services.yaml:16-17 | ATTESTED |

*(Note: service 3's "15+ ventures assessed" and experience bullet D3's "15-20 startup ventures" are independently sourced numbers from two different data files (services.yaml vs resume.yaml) describing the same underlying Castrum work; both match their own respective sources exactly, so neither is a mismatch under the defined methodology, but flagged for owner awareness since 15-20 and 15+ are subtly different framings of the same fact.)*

---

## I. Contact (data/contact.yaml, data/profile.yaml)

| Field | Site text | Source | Verdict |
|---|---|---|---|
| Email | "contact@mohamedalyamin.com" | contact.yaml:5; matches master-cv.yaml:7, base-cv.yaml:10 | ATTESTED |
| Base | "Çankaya/Ankara — Istanbul · relocation · remote" | "Çankaya/Ankara" = contact.yaml:4 (verbatim); "— Istanbul · relocation · remote" = profile.yaml:33 location_line reformatted/combined | DERIVED-OK |
| Status | "Available — freelance / full-time" | profile.yaml:32 (verbatim); consistent with contact.yaml:7 "Available" | ATTESTED |
| Elsewhere | "LinkedIn · GitHub · Telegram" (links) | profile.yaml:12-22 socials (LinkedIn, GitHub, Telegram URLs all match; WhatsApp/Discord present in source but not shown — omission, not a claim issue) | ATTESTED |

Phone number: correctly **absent** from this section despite contact.yaml:6 holding one — compliant with the no-phone rule.

---

## Summary

| Verdict | Count |
|---|---|
| ATTESTED | 141 |
| DERIVED-OK | 32 |
| MISMATCH | 0 |
| UNSOURCED | 1 |
| **Total claims audited** | **174** |

### MISMATCH list
None.

### UNSOURCED list
1. `/for/research/` hero summary (presets.yaml:53): the parenthetical **"(TÜBİTAK #120E537, Koç Sistem pilot)"** attached to "the DIONA IDS" — the grant number #120E537 is attested elsewhere (AINA paper, MedPower paper/SmartGridLedger) but never tied to DIONA in any source; DIONA's own funding is attested only as generic "TÜBİTAK-funded." The "Koç Sistem pilot" half of the same parenthetical is independently and separately attested (base-cv.yaml:62; projects.yaml:14 attestation comment) and is not in question — only the grant-number attribution is flagged.

### Footnoted (not scored as failures, but worth owner's eyes)
- "team" impact stat's "…and trained" verb (6-eng headcount) has no direct source scoping training to that same cohort (only a differently-numbered "4 engineers" group is attested as trained).
- Certifications: skills.yaml (AWS-SAA/CKA, planned) supersedes an outdated master-cv.yaml cert list (AZ-500/CISSP/OSCP) — a known, already-flagged, already-resolved inconsistency (REBUILD-STATE.md:51); site correctly follows the current source.
- Freelance-platform listings figure: resume.yaml's "360k+ cumulative listings" vs. master-cv.yaml's archived "61k+ active listings" — different metrics, resume.yaml is priority-1 and explicitly synced, not scored as a mismatch.
- Castrum ventures count: "15-20" (experience bullet, resume.yaml) vs. "15+" (services bullet, services.yaml) — two independently-sourced, both-correct figures for the same underlying fact.

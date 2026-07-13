# RAM Usage Investigation — Portfolio Site in qutebrowser/QtWebEngine

Date: 2026-07-13. Site under test: `http://127.0.0.1:1414/` (Hugo dev/gate server), pages `/` and `/for/`.
Method: read-only on the repo; browser driven via chrome-devtools MCP (CDP) against the user's live, already-running qutebrowser session. The user was actively browsing other tabs (YouTube ×3, ChatGPT ×2, DuckDuckGo/Wikipedia) throughout — never touched, per instructions.

## Environment

- `qutebrowser v3.7.0`, Backend: `QtWebEngine 6.11.1` (Chromium 140.0.7339.225).
- No dedicated GPU process exists in this qutebrowser's process tree (confirmed by enumerating every `QtWebEngineProcess` child of the qutebrowser PID and its `--type=` flag: only `renderer` ×N, `zygote` ×3, and one `utility --utility-sub-type=audio.mojom.AudioService`). All GPU/compositor work therefore happens **in-process** inside each renderer — it is not hidden in a separate process.
- Single qutebrowser process, multiple OS windows (confirmed one `qutebrowser` main process, PID 746659, owning windows on 3 different monitors/workspaces).
- System: 30 GB RAM, ~8.9 GB available at test time, 48 GB swap free — not memory-starved overall.

## Identifying the site's renderer process

`ps`/`ps -eo ...|grep` was unreliable in this sandboxed shell (returned 0-30 processes depending on invocation, vs. the real process count); switched to enumerating `/proc/[0-9]*/comm` directly, which was consistent every time.

Before any action, 3 renderer processes existed (1203099, 2477006, 368374 — 2.6-4.8 GB each). Reloading the site's tab caused a **new** renderer PID, **1466692**, to appear. I identified it as the site's process via three independent, converging signals:

1. **Precise start-time correlation** — computed each PID's start epoch from `/proc/<pid>/stat` field 22 + `btime`: PID 1466692 started at the exact moment (within ~15s) I triggered the reload; the other 3 renderers had been running since the previous day / hours earlier.
2. **Magnitude** — 1466692 sat at ~1.0-1.2 GB vs. 2.6-4.8 GB for the other three (consistent with "just-opened lightweight tab" vs. "hours-old, heavily used tabs").
3. **Idle stability signature** — 1466692 was flat to the KB across repeated 10-15s idle windows, while the other three grew by 100-200+ MB in the same windows (consistent with active YouTube video buffering / ChatGPT streaming, not an idle static page).

I also confirmed via `hyprctl clients`/`hyprctl monitors` that the site's OS window was genuinely on-screen (mapped, visible, monitor `eDP-1`, workspace 20, no special-workspace overlay covering it) for the whole test, even during periods when a different qutebrowser window (playing a YouTube video, on a different physical monitor) held OS keyboard focus. Chromium throttles rendering based on **occlusion/visibility**, not keyboard focus, so this doesn't invalidate the measurements — `document.visibilityState`/`hasFocus()` were also checked directly and reported `"visible"`/`true`.

**Limitation:** this QtWebEngine build's CDP implementation does not support `Target.createTarget` or `Target.createBrowserContext` (both return errors) — I could not open a second, isolated tab/context for a true side-by-side comparison. All tests ran sequentially in the *same* long-lived renderer process via `navigate_page` reloads.

## Critical methodological finding: this renderer's RSS floor is "sticky"

Before trusting any before/after delta, I ran a control: navigated the tracked tab to `about:blank` (zero page content, same process) and waited **53 seconds**. RSS stayed at 1,171,636 KB — statistically identical to every "page fully loaded" reading taken immediately before and after. Chromium's idle-time memory purge did not visibly reclaim anything in that window either.

**Consequence:** once this renderer process has been through a few reloads, its resident memory is dominated by allocator/compositor high-water-mark retention, not by whatever is *currently* on screen. The task's prescribed method (remove suspect → wait ~20s → compare RSS) cannot detect a real effect under these conditions — any true cost gets baked into the floor on first paint and does not come back out when the element is later removed. All deltas below must be read with this caveat (see "What this does and doesn't prove").

I mitigated this partially by using `navigate_page`'s `initScript` to **pre-empt** element creation (a `DOMContentLoaded` listener registered before site.js's own, so orbs/canvas never exist even at first paint of that reload, and backdrop-filter is neutralized via injected CSS) rather than removing elements after the fact — this at least avoids the simpler "DOM removed but JS/compositor GC hasn't run yet" lag the task also flagged. It does not defeat the cross-reload stickiness described above.

## Measurements — home page (`/`), renderer PID 1466692, settled ~20-25s after each reload

| # | Condition | RSS (KB) | RSS (MB) | CPU ticks (cumulative, 10ms/tick) |
|---|---|---|---|---|
| 1 | Baseline — orbs + backdrop-filter + canvas all present | 1,185,904 | 1158.1 | 2224 |
| 2 | Orbs pre-empted (2× `body>.orb`, never created) | 1,185,904 | 1158.1 | 2224 |
| — | *control:* `about:blank`, 53s idle | 1,171,636 | 1144.2 | n/a |
| 3 | Orbs present again (fresh reload post-control) | 1,171,644-1,171,760 | ~1144.3 | 2224 |
| 4 | `.strip` `backdrop-filter` neutralized (orbs present) | 1,171,644 | 1144.2 | 2225 |
| 5 | Canvas `#grid` removed pre-paint (orbs+backdrop present) | 1,196,404 | 1168.4 | 2248 |
| 6 | All three removed together | 1,185,792 | 1158.1 | 2248 |

All 7 readings fall inside a **24.8 MB band** (1,171,636–1,196,404 KB, ≈2.1% of the ~1.18 GB mean), with no directional trend attributable to any specific suspect — every condition is indistinguishable from noise given this process's behavior.

**CPU**, measured directly (not inferred): **0 ticks (0ms)** of utime+stime consumed by the renderer over two independent 15-second idle windows — one with the 2 `.orb` elements actively animating (13s/17s `drift1`/`drift2` transform+scale keyframes), one with them entirely absent from the DOM. This was on a window confirmed genuinely visible/on-screen, not backgrounded.

## Measurements — `/for/` page (heavier: 4 orbs, not 2 — see correction below)

| Condition | RSS (KB) | CPU ticks |
|---|---|---|
| Baseline — 4 orbs + 12 backdrop-filter faces + canvas | 1,185,792 | 2248 |
| Orbs (all 4) + all 12 backdrop-filter faces removed pre-paint | 1,185,792 | 2248 |

Byte-for-byte identical in both RSS and CPU.

## Correction to the investigation brief: `/for/` has 4 orbs, not 2

Source inspection shows:
- `layouts/_default/baseof.html:5` unconditionally injects `<span class="orb o1">`/`<span class="orb o2">` (the fixed, `body>.orb`, opacity-.32 pair) on **every** page, including `/for/`.
- `layouts/for/list.html:3` **additionally** injects its own local `.orb.o1`/`.o2` pair (absolute-positioned inside `#for.glassfor`, base `.orb` opacity .45, not overridden by the `body>.orb` rule since these aren't direct children of `<body>`).

Confirmed live: `document.querySelectorAll('.orb').length === 4` on `/for/`, vs. 2 on every other page. Also confirmed 12 `.peek .face` elements (6 role cards × front/back), each carrying `backdrop-filter: blur(14px) saturate(1.15)`, matching the brief's count for that suspect.

## JS heap — rules out a JS-side leak

`performance.memory`, checked on both pages / multiple conditions, consistently reported: `usedJSHeapSize` = `totalJSHeapSize` ≈ **179.3 MB** (heap limit 3585.8 MB). That's ~15% of the renderer's ~1.18 GB RSS — small enough relative to the ~1 GB balance to confirm the *bulk* of resident memory is not JS objects (consistent with Blink/compositor/allocator-retained memory, not a script-side leak). 179 MB itself is non-trivial for ~7 KB of `site.js`; almost certainly V8/Blink baseline + context-snapshot overhead (the renderer's cmdline shows `--shared-files=v8_context_snapshot_data:100`), not page-specific script.

## System-wide context

Final full snapshot, all qutebrowser processes:

| PID | Role | RSS |
|---|---|---|
| 1203099 | renderer (other tabs) | 5,126,276 KB (4.89 GB) |
| 1466692 | renderer (**this site**) | 1,185,796 KB (1.13 GB) |
| 2477006 | renderer (other tabs) | 4,693,844 KB (4.48 GB) |
| 368374 | renderer (other tabs) | 3,237,012 KB (3.09 GB) |
| 746659 | main/browser process | 1,116,928 KB (1.07 GB) |
| 746790/746791/746799 | zygotes | ~239 MB combined |
| 747758 | utility (audio service) | 128,028 KB |
| **Total** | | **~15,726,908 KB ≈ 14.99 GB** |

The site's own renderer is **~7.5-8%** of qutebrowser's total footprint. The other 3 renderers — hosting the user's YouTube (×3 tabs) / ChatGPT (×2) / DuckDuckGo+Wikipedia tabs, some alive since the previous day — were observed growing by hundreds of MB per 10-15s idle window during this investigation, consistent with active video/streaming content. If the original "qutebrowser uses a lot of RAM" impression came from looking at overall system/browser memory, these other tabs — not the portfolio site — are almost certainly the dominant contributor.

## Culprit ranking

The empirical RSS delta was inconclusive for all three suspects (masked by the sticky-floor artifact described above). Ranking below combines the one clean empirical signal available (CPU, ~zero for every condition) with established Chromium rendering-cost characteristics of the specific CSS primitives in use:

1. **`backdrop-filter`** (`.strip` + 12× `.peek .face` on `/for/`) — highest theoretical risk. `backdrop-filter` requires Chromium to maintain a "backdrop root" that samples the actual rendered content behind the element on every compositor update — structurally more expensive than a plain `filter`, which only operates on the element's own subtree. 12 simultaneous instances on `/for/` is a lot of backdrop-root bookkeeping, even though it did not show up as resident MB in this test.
2. **`.orb` (`filter: blur(72px)`, animated)** — second. A blurred layer's backing store is cheap once allocated and stable in size, but the continuous `transform`/`scale` animation (drift1 13s / drift2 17s) forces a recomposite of that blurred layer every frame it moves. My CPU measurement showed this costing nothing measurable at idle (genuinely reassuring), but I could not confirm whether it spikes momentarily during the animation's highest-motion phase specifically — my sampling windows were not synchronized to the keyframe timeline.
3. **Canvas `#grid`** — lowest risk, and already well-built: offscreen-prerender pattern, zero-size guards, `visibilitychange` pause of the pointer-glow trail, no continuous `requestAnimationFrame` loop (redraws are resize/pointermove-driven only — verified by reading `static/js/site.js` in full). The only (minor, unrequested) cost: 2 canvas backing stores sized at device-pixel resolution — on this test machine's HiDPI panel (2880×1800 @ 2× scale) that's real but not large (tens of MB, not hundreds).

## What this does and doesn't prove

**Does prove:** idle CPU cost of the orb animation is unmeasurable (0 ticks over 15s) while the tab is genuinely on-screen; JS heap is not the source of the ~1.15-1.2 GB footprint; the investigation brief undercounted `/for/`'s orbs (4, not 2); the canvas grid's existing optimizations are working as designed.

**Does NOT prove:** that the suspects are free. The `about:blank` control shows this renderer's RSS is decoupled from current content after a few reloads — so if the orbs/blur DO inflate the *peak* usage at first paint (plausible and well-documented for filter/backdrop-filter compositor layers generally), that peak gets baked into the floor and cannot be recovered afterward by removing the elements. I attempted to get a pristine never-loaded renderer process to test first-paint peak directly; this QtWebEngine build's CDP rejects both `Target.createTarget` and `Target.createBrowserContext` ("Not supported" / "Failed to create browser context"), so no isolated second tab could be opened. A separate attempt to use a large synthetic heap allocation (300 MB) to help pin down process identity was correctly blocked by the permission system as unsafe workload interference on an already memory-pressured shared browser — I used a smaller 20 MB probe and OS-level start-time correlation instead (see "Identifying the site's renderer process").

## Recommended fixes (ordered by confidence, not just estimated size)

1. **Pause `.orb` animation when the tab is hidden** — genuine gap, matches the investigation brief's original suspicion exactly. `site.js`'s existing `visibilitychange` handler (line 55) only resets the canvas pointer trail; it does nothing for `.orb`. Add:
   ```js
   document.addEventListener('visibilitychange',()=>{document.body.classList.toggle('paused',document.hidden)});
   ```
   ```css
   .paused .orb{animation-play-state:paused}
   ```
   Zero visual cost (a backgrounded tab isn't seen anyway); removes any per-frame recomposite cost for however long the tab sits backgrounded — the common case over a browsing session, not the few seconds I could directly measure.

2. **Replace `.orb`'s `filter:blur(72px)` with a pre-softened gradient, no `filter` primitive**: widen the `radial-gradient(circle, var(--accent), transparent 66%)` falloff (or swap to an already-soft PNG/SVG) so blur is baked into the asset/gradient stops instead of computed live. A `filter:blur()` element needs an offscreen raster surface padded ~3× the blur radius per side — for a 72px blur that's a meaningfully larger backing store than the 400×400px element itself. This removes an architecturally expensive primitive for a cheap one, independent of the inconclusive RSS test — same look, no filter surface. Highest-confidence "free win" on this list.

3. **Convert `backdrop-filter` to a solid/gradient translucent fallback** on `.strip` and `.peek .face`: e.g. `background: color-mix(in srgb, var(--bg-2) 92%, transparent)` with no `backdrop-filter`. Removes backdrop-root sampling entirely — 12 instances on `/for/` alone. Slightly less "frosted glass" look, close visually on this site's dark palette. This is the standard, well-documented mitigation for backdrop-filter cost.

4. **Leave the canvas grid as-is** — already follows the mandatory perf pattern (`.superpowers/sdd/global-constraints.md`) and showed no evidence of a problem in this test. Optional micro-opt only: explicitly cap backing-store scale (e.g. `min(devicePixelRatio,2)`) if not already implicitly bounded, to avoid oversized buffers on very-high-DPI panels.

5. **Belt-and-suspenders**: if #2 isn't done, simply shrinking the orbs (e.g. 400×400/blur(72px) → 280×280/blur(48px)) reduces backing-store area+padding roughly proportionally for negligible visual difference.

6. **Not a site fix, but worth knowing**: the other browser tabs (YouTube ×3, ChatGPT ×2, DuckDuckGo/Wikipedia — none touched during this investigation), not this site, account for ~87-93% of qutebrowser's current ~15 GB total RSS and were actively growing during the test. If "qutebrowser is heavy" was the original complaint in a general sense, that's almost entirely those tabs.

## Files referenced

- `/home/mohamed/Projects/skylight74-native/static/css/site.css` — lines 53-58 (`.orb`/`.o1`/`.o2` + keyframes), 81-82 (`.peek .face` backdrop-filter), 103 (`#grid`), 108-110 (`.strip` backdrop-filter)
- `/home/mohamed/Projects/skylight74-native/static/js/site.js` — lines 27-61 (`initCanvas`, already-optimized pattern), line 55 (existing `visibilitychange` handler, canvas-only — the gap fix #1 above closes)
- `/home/mohamed/Projects/skylight74-native/layouts/_default/baseof.html` — line 5 (unconditional orbs + canvas on every page)
- `/home/mohamed/Projects/skylight74-native/layouts/for/list.html` — line 3 (second local orb pair; source of the 4-vs-2 discrepancy on `/for/`)

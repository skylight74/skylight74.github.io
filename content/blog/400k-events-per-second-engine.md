---
title: "The 400k events/sec engine"
date: 2025-06-10T21:30:00+03:00
categories: ["Engineering"]
---

I spent most of the last year building one thing: a real-time filtering engine in Go that takes security events from 80+ different sources and decides, per event, whether anyone should care. It sustained around 400k events per second. This is the write-up I kept promising myself.

The shape of it is simple to say and annoying to build. Kafka on the way in. A filtering and aggregation layer in the middle, with rules defined as JSON, not code. ClickHouse on the way out for analytics. Redis holds the state that rules need between events, and OpenTelemetry traces the whole path so I could actually see where time went.

The part I ended up caring about most was the rules. Three families did almost all the work:

- chain detection. Some attacks only make sense as a sequence. One failed login is noise, forty in a row from the same place is a brute force attempt. The rule engine tracks the chain in Redis and fires when the pattern completes.
- first-seen. The cheapest anomaly signal there is. A service talking to a host it has never talked to before is worth a look, and "never seen this pair before" is a set lookup.
- z-score outliers. Plain statistics on top of aggregates. When a metric wanders too many deviations from its own history, that gets flagged. No model, no training, and it caught real problems.

Keeping the rules as JSON data instead of compiled code turned out to be the decision that aged best. Detection logic changed weekly. The engine did not have to.

Alerts were their own small service, also Go. Slack, email, webhooks, with rate limiting and time conditions so a noisy rule at 3am becomes one message, not four hundred. Routing is policy-based, so who gets woken up depends on what fired, not on who wrote the rule.

Things I would tell someone building the same thing: measure before believing anything, the tracing paid for itself in the first week. Put every piece of state behind one interface, because you will swap it. And write the boring aggregation path first, the clever detections are worthless if the pipe under them drops events.

The number is nice to say out loud. The real result is quieter: false positives went down because the chained and statistical rules replaced a pile of single-event ones, and the on-call channel got readable again.

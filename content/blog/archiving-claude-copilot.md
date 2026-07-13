---
title: "Archiving Claude Copilot"
date: 2026-04-18T14:00:00+03:00
categories: ["Notes"]
---

I archived Claude Copilot this spring. It deserves a proper goodbye instead of a quiet repo archive button, so here it is.

Copilot started as an extension of an open source framework, back when Claude Code was newer and I kept hitting the same walls: one agent, one repo, no memory between sessions. So I built around the walls. An orchestrator-worker pattern that could coordinate work across multiple repositories at once. Thirteen specialized agents with actual roles: architect, engineer, QA, security, DevOps, UX, and friends. Parallel streams of work with conflict detection between them, git worktrees keeping the agents out of each other's way, and persistent memory in PostgreSQL so a session could pick up where the last one stopped. Node.js and Python glue, the Claude Code API underneath.

It worked. I ran real projects through it, including parts of this site's history.

Then the vendor caught up. Claude Code grew native subagents, skills, and its own memory story. Every release, another piece of my framework became a built-in feature with better integration than mine could ever have from the outside. There is a version of this where I keep maintaining a parallel implementation out of pride. I have seen those projects, and I did not want to own one.

So: archived. Not deleted, the repo stays up because the patterns still read well, and honestly some of them arrived before the official versions did. The orchestrator routing work by task type. Worktrees as the isolation boundary for parallel agents. Memory as files a future session can load. If those ideas look familiar to anyone using agent tooling today, that is the point. Everyone converged on them because they are the correct shapes.

What I actually kept is not code. It is the habit of treating an AI agent like a junior engineer with no context: write the brief properly, hand over exact interfaces, review what comes back, never accept "done" without evidence. That transfers to every tool that came after, and it will transfer to the next one too.

Tools should retire when the platform absorbs their reason to exist. This one did its job.

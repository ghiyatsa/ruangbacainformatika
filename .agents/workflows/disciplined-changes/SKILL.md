---
name: disciplined-changes
description: "Use this skill for any code change in this project — writing, reviewing, refactoring, or fixing. Trigger before editing any file. Enforces four habits that prevent the most common mistakes: state assumptions before coding, prefer the simplest solution, make surgical changes that touch only what the request needs, and turn every task into a verifiable goal with a concrete check. Activate whenever you are about to modify Laravel, Filament, Inertia/React, migrations, widgets, or tests, and especially when a request is vague, when you are tempted to refactor adjacent code, or when you cannot yet say how you will prove the change works. Do not use for pure reading, searching, or answering questions about the codebase."
license: MIT
metadata:
  author: multica-ai
  adapted-from: andrej-karpathy-skills
---

# Disciplined Changes

Habits that reduce the most common mistakes when editing this codebase, adapted from
[Andrej Karpathy's observations](https://x.com/karpathy/status/2015883857489522876) on LLM coding
pitfalls and tuned to this project's stack and tooling.

**Tradeoff:** these habits bias toward caution over speed. For a one-line typo fix, use judgment.

## 1. Think Before Editing

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before touching a file:

- State your assumptions explicitly. If uncertain, ask.
- If the request has more than one reasonable interpretation, present them — don't pick silently.
  Example from this project: "make the widget tidy" could mean align card widths, reduce the card
  count, or change grid columns. These are different jobs.
- If a simpler approach exists, say so. Push back when warranted.
- If the report is unclear, stop and name what's confusing.

A user report often describes a **symptom**, not the cause. Reproduce it before changing code.

## 2. Simplest Thing That Works

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you wrote 200 lines and it could be 50, rewrite it.

Ask: "Would a senior engineer call this overcomplicated?" If yes, simplify.

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you would do it differently.
- If you notice unrelated dead code, **mention it — don't delete it**. (This project keeps some
  unused services on purpose; the owner decides.)

When your change creates orphans, remove the imports/variables your change made unused — but leave
pre-existing dead code alone.

**The test:** every changed line should trace directly to the request.

## 4. Goal-Driven Execution

**Define success criteria first. Loop until verified.**

Turn each task into a verifiable goal:

- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Tidy the widget" → "Render the widget and confirm the layout matches the requested shape"

For multi-step work, state a short plan:

```
1. [step] → verify: [check]
2. [step] → verify: [check]
```

Weak criteria ("make it work") require constant clarification. Strong criteria let you finish alone.

## Verify the Right Way in This Project

Choose the check that actually covers the change — several blind spots exist here.

**Widgets and Filament code:** PHPStan **excludes `app/Filament/*`** (see `phpstan.neon`), so type
errors there pass static analysis silently. Render the widget and inspect the output directly.

**Frontend:** `tsc` exhausts memory on this server (909 MB RAM). Use **ESLint** as the frontend
check, per the owner's standing decision. A frontend change is not done until it is **built**
(`npm run build` plus `npm run build:ssr`) — the browser will not see it otherwise.

**Backend deploys:** OPcache runs with `validate_timestamps=Off`, so **every code deploy needs
`sudo systemctl reload php8.4-fpm`**. Without it the old code keeps serving.

**Tests:** dev dependencies are stripped on the production server, so run Pest from a copy at
`/tmp/testrun3`, never on the live tree.

**Before claiming success:** state the evidence, not the intention. Quote the passing count, the
HTTP status, the rendered output. "Should work" is not a result.

## Match the House Rules

These exist because breaking them caused real problems:

- Follow existing conventions; check sibling files before creating one.
- Reuse existing components before writing new ones.
- Don't add or change dependencies without approval.
- Don't create new base folders without approval.
- Keep commits conventional (types: build, chore, ci, docs, feat, fix, perf, refactor, revert,
  test — note: **no `style`**), subject lowercase, body lines under 100 characters.
- Never commit secrets, and never place a `.env` backup inside the repository.

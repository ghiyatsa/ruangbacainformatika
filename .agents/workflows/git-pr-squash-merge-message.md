---
name: git-pr-squash-merge-message
description: Generate a final squash commit message for pull requests using Conventional Commits, with versioning-aware guidance for release-please and changelog automation.
---

# Git PR Squash Merge Message

## When to Apply

Activate this workflow when:

- the user is about to use `Squash and merge`,
- a pull request already exists and needs a final squash commit message,
- the repository uses Conventional Commits and release automation,
- the user wants AI to suggest the merge message instead of relying on GitHub's default text.

Do not apply this workflow when:

- the user is not merging a PR,
- the repository does not care about Conventional Commits,
- the user wants to preserve the branch's individual commits without squash.

## Goal

Produce a squash commit message that is:

- accurate to the PR's real change,
- short and readable in `main`,
- valid for Conventional Commits,
- and useful for automated versioning and changelog generation.

## Repository Context

This repository uses:

- `commitlint`
- Conventional Commits
- `release-please`

SemVer expectations:

- `fix(...)` usually maps to `PATCH`
- `feat(...)` usually maps to `MINOR`
- `!` or `BREAKING CHANGE:` maps to `MAJOR`

Preferred scopes in this repository:

- `auth`
- `admin`
- `catalog`
- `loan`
- `return`
- `kiosk`
- `whatsapp`
- `similarity`
- `settings`
- `ui`
- `github`
- `deps`
- `test`
- `ci`

## Core Rules

- Never trust GitHub's default squash message without review.
- Always infer the message from the actual PR content, not only the branch name.
- Prefer one clear Conventional Commit message over a vague summary.
- Keep the subject concise and user-meaningful.
- If the PR is primarily CI, GitHub Actions, commitlint, release, or automation work, prefer `ci(...)` or `chore(...)` with an appropriate scope.
- If the PR introduces user-facing functionality, prefer `feat(...)`.
- If the PR corrects broken behavior, prefer `fix(...)`.
- If the PR only updates tests, prefer `test(...)`.
- If the PR only updates documentation, prefer `docs(...)`.
- If the PR introduces a breaking change, mark it explicitly with `!` and explain why.

## Required Workflow

### 1. Inspect the PR Changes

Review the actual PR content:

- changed files,
- diff summary,
- current PR title,
- commit history if useful,
- whether the change is feature, fix, refactor, CI, docs, test, or config.

Do not propose a message before understanding the dominant purpose of the PR.

### 2. Determine the Main Intent

Choose the single strongest purpose of the PR:

- feature,
- fix,
- CI or release automation,
- internal maintenance,
- tests,
- docs,
- refactor.

If the PR mixes several concerns, base the squash message on the most important merged outcome in `main`.

### 3. Choose the Best Conventional Commit

Use:

```txt
type(scope): subject
```

Examples:

```txt
ci(github): add release-please and commitlint automation
feat(kiosk): add subnet-aware pin validation
fix(admin): correct book item action visibility
```

Breaking change example:

```txt
feat(loan)!: change qr draft consumption contract
```

### 4. Report Alternatives

Provide:

- 1 recommended squash commit message,
- up to 2 alternatives if the scope or type is somewhat ambiguous,
- the expected versioning impact for each option.

### 5. Keep Merge Safe

- Do not merge automatically unless the user explicitly asks.
- Do not assume GitHub's auto-filled title/body is good enough.
- If the PR content is too mixed, say so and recommend splitting future PRs more cleanly.

## Output Contract

When running this workflow, the agent should return:

1. A one-paragraph summary of what the PR mainly changes.
2. The recommended squash commit message.
3. The SemVer impact: `MAJOR`, `MINOR`, `PATCH`, or `no release`.
4. Up to 2 alternative messages when useful.
5. A short note on whether GitHub's default squash message should be replaced.

## Execution Prompt

Use the following prompt when the user wants AI help for the squash message:

```txt
You are an AI agent preparing the final squash commit message for a pull request in this repository.

Your job is to inspect the PR changes and generate the best final squash commit message for `Squash and merge`.

Rules:
1. Review the actual changed files and diff before proposing a message.
2. Use Conventional Commits in the format `type(scope): subject`.
3. Prefer repository scopes such as:
   `auth`, `admin`, `catalog`, `loan`, `return`, `kiosk`, `whatsapp`, `similarity`, `settings`, `ui`, `github`, `deps`, `test`, `ci`.
4. Choose the message based on the dominant outcome of the PR, not on the branch name alone.
5. If the change is CI, GitHub Actions, release automation, or commit tooling, prefer `ci(...)` or `chore(...)`.
6. If the change is user-facing functionality, prefer `feat(...)`.
7. If the change is a bug fix, prefer `fix(...)`.
8. If the change is breaking, mark it with `!` or explain `BREAKING CHANGE:`.
9. Keep the subject concise and readable in the `main` branch history.
10. Do not rely on GitHub's default squash message unless it already matches the best Conventional Commit.

Output:
1. Short summary of the PR.
2. Recommended squash commit message.
3. Versioning impact: `MAJOR`, `MINOR`, `PATCH`, or `no release`.
4. Up to 2 alternative messages.
5. One sentence telling whether the default GitHub squash message should be replaced.
```

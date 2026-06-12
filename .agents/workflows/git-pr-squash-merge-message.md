---
name: git-pr-squash-merge-message
description: Generate a final squash commit message for pull requests using Conventional Commits, with versioning-aware guidance for release-please and changelog automation.
---

# Git PR Squash Merge Message

## When to Apply

Activate this workflow when:

- the user is about to use `Squash and merge`,
- a pull request already exists and needs a final squash commit message,
- the user wants AI to suggest the merge message.

## Goal

Produce a squash commit message that is:

- accurate to the PR's real change,
- valid for Conventional Commits,
- passes `commitlint` (allowed types, scopes, and line length limits),
- and useful for automated versioning and changelog generation.

## Repository Context

This repository uses:

- `commitlint` (with a strict configuration)
- `release-please`

### Allowed Types

Only these types are allowed:
- `build`, `chore`, `ci`, `docs`, `feat`, `fix`, `perf`, `refactor`, `revert`, `test`

> [!IMPORTANT]
> **The `style` type is NOT allowed.** For UI styling, layouts, or code formatting changes, use `refactor` or `chore`.

### Allowed Scopes

Only these scopes are allowed:
- `admin`, `auth`, `catalog`, `deps`, `github`, `kiosk`, `loan`, `settings`, `similarity`, `ui`, `return`, `whatsapp`

> [!WARNING]
> Do NOT use `ci` or `test` as a scope. Use `github` for CI/workflow changes, or another valid scope.

### Commit Body Limits

- **Ensure no line in the commit message body or PR description exceeds 100 characters.** Long lines will cause the commitlint check to fail on merge.

## Core Rules

- Never trust GitHub's default squash message without review.
- Always infer the message from the actual PR content, not only the branch name.
- Keep the subject concise and user-meaningful.
- Ensure the description is formatted (manually wrapped at ~80 characters) to pass the 100-character line length limit.

## Required Workflow

### 1. Inspect the PR Changes

Review the actual PR content:
- changed files,
- diff summary,
- current PR title,
- and whether the change is feature, fix, refactor, CI, docs, test, or config.

### 2. Determine the Main Intent

Choose the single strongest purpose of the PR using the allowed types and scopes.

### 3. Draft the Conventional Commit Message

Use:
```txt
type(scope): subject
```

If there are breaking changes, add `!` after the scope:
```txt
feat(loan)!: change qr draft consumption contract
```

### 4. Format the Extended Description

If the commit has a body, break the lines manually using newlines so that no single line exceeds 100 characters.

### 5. Verify local commitlint (optional)

Verify the message before suggesting it:
`echo "type(scope): subject" | npx commitlint --verbose`

## Output Contract

When running this workflow, the agent should return:

1. A short summary of the PR.
2. The recommended squash commit title.
3. The recommended extended description (with lines manually wrapped to be under 100 characters).
4. SemVer impact: `MAJOR`, `MINOR`, `PATCH`, or `no release`.

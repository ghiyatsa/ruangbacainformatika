---
name: git-commit-automation
description: Analyze git changes, run checks, split them into logical commits, stage files, create Conventional Commits, and push to GitHub.
---

# Git Commit and Push Automation

## When to Apply

Activate this workflow when:

- The user has finished a feature, fix, refactor, or maintenance change.
- An AI agent should analyze the current worktree, run tests, and prepare commits.
- The user wants the agent to automatically commit and push the changes to GitHub.

Do not apply this workflow when:

- The user explicitly asks to avoid touching git state or pushing.
- The worktree contains unresolved ambiguity that could mix unrelated work.

## Goal

Turn the current git changes into a clean series of logical commits that:

- use Conventional Commit messages passing local `commitlint` checks,
- pass PHP tests and frontend linting/type checks,
- and are pushed to the remote branch automatically.

## Repository Context

This repository uses:

- `commitlint` (with a strict configuration)
- `release-please` (which triggers on main pushes)

### Allowed Types

Only these types are allowed:
- `build`, `chore`, `ci`, `docs`, `feat`, `fix`, `perf`, `refactor`, `revert`, `test`

> [!IMPORTANT]
> **The `style` type is NOT allowed.** For UI styling, layouts, or code formatting changes, use `refactor` or `chore`.

### Allowed Scopes

Only these scopes are allowed:
- `admin`, `auth`, `catalog`, `deps`, `github`, `kiosk`, `loan`, `settings`, `similarity`, `ui`, `return`, `whatsapp`

> [!WARNING]
> Do NOT use `ci` or `test` as a scope, as they will fail the scope check. Use `github` for CI/workflow changes, or another valid scope.

### Commit Body Limits

- **Ensure no line in the commit message body exceeds 100 characters.** Long lines will cause `commitlint` validation to fail.

## Core Rules

- Always inspect `git status` and `git diff` before touching staging.
- Never use destructive git commands such as `git reset --hard` on uncommitted user work.
- Automatically run `vendor/bin/pint --dirty --format agent`, `php artisan test --compact`, `npm run types:check`, and `npm run lint:check` to verify code quality before committing.
- Commit in logical groups and push to the remote branch.

## Required Workflow

### 1. Run Quality Checks & Formatting

Before committing:
- Format modified PHP code: `vendor/bin/pint --dirty --format agent`
- Run PHP tests: `php artisan test --compact`
- Run frontend type check: `npm run types:check`
- Run frontend lint: `npm run lint:check`

### 2. Inspect Changes

Review changes in the worktree:
- `git status --short`
- `git diff`

### 3. Determine Commit Groups

Produce a commit plan:
- One logical purpose per commit.
- Use Conventional Commit messages with allowed types and scopes.
- Ensure all line lengths in the body are under 100 characters.

### 4. Stage & Verify Commit Message

For each commit group:
- Stage only the relevant files.
- Verify the proposed commit message using the local commitlint config:
  `echo "type(scope): subject" | npx commitlint --verbose`
- If commitlint passes, commit the files.

### 5. Push changes

Once all commits are created:
- Push commits to the remote branch: `git push origin <branch-name>`

## Output Contract

When running this workflow, the agent should return:

1. A short summary of the current worktree.
2. The results of the tests and lint checks.
3. The list of commits created (with their commit messages).
4. The confirmation that the commits have been pushed to GitHub.

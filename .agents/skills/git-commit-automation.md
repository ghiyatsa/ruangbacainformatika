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
- Commit in logical groups, push to remote, and optionally create a Pull Request.

## Required Workflow

### 1. Run Quality Checks & Formatting

Before staging or committing any code, run all project checks to ensure that the code meets repository standards:
- **Format PHP Code**: Run `vendor/bin/pint --dirty --format agent` to automatically style any modified PHP files.
- **Run Backend Tests**: Run `php artisan test --compact` to make sure all existing and new tests pass successfully.
- **Type Check Frontend**: Run `npm run types:check` to ensure there are no TypeScript compiler errors.
- **Lint Frontend**: Run `npm run lint:check` to check for ESLint/Prettier issues.

> [!IMPORTANT]
> If any formatting, testing, type-checking, or linting step fails, you **MUST** resolve the errors before proceeding.

### 2. Verify Branch Context

Check which branch you are on:
- Run `git branch --show-current` to identify the current branch.
- **If you are on `main`**:
  - Do NOT commit feature work or refactors directly to `main` unless it is a minor maintenance chore (e.g., config tweak, workflow fix) that has been explicitly approved.
  - For features, bugs, or refactors, create and switch to a new branch: `git checkout -b <type>/<short-description>` (e.g., `feat/whatsapp-otp-service` or `fix/loan-qr-validation`).
- **If you are on a feature branch**:
  - Ensure the branch is up-to-date with remote `main` before committing:
    `git pull --rebase origin main`

### 3. Inspect and Stage Changes

Review the worktree to ensure only the desired modifications are committed:
- Run `git status --short` to see modified, created, and deleted files.
- Run `git diff` or `git diff --cached` to verify the exact changes.
- Stage changes in logical groups using:
  `git add <file1> <file2>`
  Avoid staging unrelated files together.

### 4. Draft and Validate the Commit Message

For each staged group:
- Compose a Conventional Commit message following the allowed types and scopes.
- **Verify the message locally** using `commitlint` to prevent CI validation failures.
  > [!TIP]
  > On Windows PowerShell, standard piping like `echo "msg" | npx commitlint` can append a BOM (Byte Order Mark) or trailing spaces, causing `commitlint` to fail.
  > Always run the validation using the following CMD wrapper:
  > `cmd /c "echo type(scope): message| npx commitlint --verbose"` (make sure there is no trailing space before the pipe `|`).
- Once verified, commit the changes:
  `git commit -m "type(scope): subject" -m "Optional body with wrapped lines under 100 chars"`

### 5. Push and Create Pull Request

After all commits are successfully created locally:
- Push the branch to the remote repository:
  `git push origin <branch-name>`
- Create a Pull Request (PR) using the GitHub CLI:
  `gh pr create --title "type(scope): subject" --body "Detailed description of the changes"`
- Note: Use `--draft` if the work is still in progress or needs review before merging.
- Provide the user with the URL of the created PR.

## Output Contract

When completing this workflow, the agent must output:

1. **Check Status**: Success/failure of formatting, testing, and linting checks.
2. **Current Branch**: The branch name and its tracking status.
3. **Commit Details**: The exact commit hashes and messages created.
4. **Pull Request Info**: The GitHub PR URL and state (e.g., draft or open).

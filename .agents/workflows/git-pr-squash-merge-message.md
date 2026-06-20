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

### 1. Identify PR Type and Origin

First, determine if the PR is an automated release PR or a standard feature/bugfix PR:
- **Case A: Automated `release-please` PR**
  - **Indicator**: The branch name matches `release-please--branches--main` and the title is `chore(main): release X.Y.Z`.
  - **Rule**: **DO NOT** customize or alter the squash commit message or title. Altering it will prevent the release pipeline from creating git tags and changelogs.
  - **Action**: Merge directly without overriding the subject or body (see step 5).
- **Case B: Standard Feature / Bugfix / Chore PR**
  - Proceed to the following steps to construct a high-quality squash commit message.

### 2. Inspect the PR Changes

For standard PRs, review the changes to summarize the overall impact:
- View the diff or files changed: `git diff main...<branch-name>` (or use `gh pr diff <pr-number>`).
- Confirm the PR title matches conventional rules.
- Identify the primary intent (e.g. `feat`, `fix`, `refactor`) and the correct scope (e.g. `auth`, `loan`, `kiosk`).

### 3. Draft the Conventional Commit Message

Construct the squash commit title:
```txt
type(scope): subject
```
- Example: `feat(loan): add qr code confirmation for book return`
- If there are breaking changes, add `!` after the scope: `fix(auth)!: enforce secure cookie flags`
- Write an optional commit body summarizing the changes. Make sure to **manually wrap lines in the body to be under 100 characters** to prevent `commitlint` validation errors.

### 4. Verify Local Commitlint (For Standard PRs)

Before executing the merge, verify the drafted message locally using the CMD wrapper:
`cmd /c "echo type(scope): subject| npx commitlint --verbose"` (make sure there is no trailing space before the pipe `|`).

### 5. Execute Squash and Merge

Merge the Pull Request using the GitHub CLI (`gh`):

- **For Standard PRs** (override the squash commit title and body to keep history clean and lint-compliant):
  ```bash
  gh pr merge <pr-number> --squash --subject "type(scope): subject" --body "Extended description or bullet points"
  ```
- **For Release PRs** (use the default title and body provided by release-please):
  ```bash
  gh pr merge <pr-number> --squash
  ```

### 6. Synchronize Workspace

After the PR is merged:
1. Switch back to the main branch:
   `git checkout main`
2. Pull the latest merged changes:
   `git pull origin main`
3. Prune remote-tracking references and delete the local feature branch:
   `git remote prune origin`
   `git branch -d <branch-name>`

## Output Contract

When completing this workflow, the agent must output:

1. **PR Summary**: The target PR number, title, and type.
2. **Merge Method**: The exact `gh pr merge` command used or recommended.
3. **Squash Message**: The conventional commit subject and body applied.
4. **Workspace Status**: Confirmation that local `main` has been synced and the local feature branch has been deleted.

---
description: Analyze git changes, split them into logical commits, stage the right files, and create Conventional Commits without pushing. Designed for AI agents working in this repository with automated versioning and changelog rules.
---

# Git Commit Automation

## When to Apply

Activate this workflow when:

- The user has finished a feature, fix, refactor, or maintenance change
- An AI agent should analyze the current worktree and prepare commits
- The repository uses Conventional Commits, commitlint, and automated changelog/versioning
- The user wants help splitting changes into small, reviewable commits

Do not apply this workflow when:

- The user only wants analysis without staging or committing
- The user explicitly asks to avoid touching git state
- The worktree contains unresolved ambiguity that could mix unrelated work

## Goal

Turn the current git changes into a clean series of logical commits that:

- use Conventional Commit messages,
- preserve unrelated user work,
- keep release notes readable,
- and never push automatically.

## Repository Context

This repository uses Conventional Commits plus automated versioning/changelog rules.

- `fix(...)` normally maps to `PATCH`
- `feat(...)` normally maps to `MINOR`
- `type(scope)!:` or `BREAKING CHANGE:` maps to `MAJOR`

Breaking changes in this repository require extra care. Treat these areas as potentially breaking:

- authentication and member approval flows
- kiosk access and QR consumption flows
- loan and return draft behavior
- destructive migrations or schema resets
- new required environment variables
- external integration contract changes such as WhatsApp or similarity sync

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

## Safety Rules

- Always inspect `git status` and `git diff` before touching staging.
- Never assume every changed file belongs together.
- Never use destructive git commands such as `git reset --hard`.
- Never push automatically.
- Never switch branches automatically.
- Never amend a commit unless the user explicitly asks.
- Do not stage unrelated files just because they are already modified.
- If existing user changes are mixed together in the same file and cannot be safely separated, stop and explain the ambiguity before committing.

## Required Workflow

### 1. Inspect Changes

Start by reviewing:

- `git status --short`
- `git diff`
- `git diff --staged` if anything is already staged

Build a mental model of:

- which files belong to which feature or fix,
- whether tests/config/docs are coupled to that change,
- whether there are unrelated edits in the same worktree.

### 2. Propose Commit Groups

Before staging, produce a commit plan:

- one logical purpose per commit,
- the files expected in each commit,
- the Conventional Commit message,
- the expected versioning impact: `MAJOR`, `MINOR`, `PATCH`, or `no release`.

Good commit groups:

- feature code plus its directly related tests
- CI/workflow changes separated from application behavior
- docs-only changes separated from runtime changes

Avoid:

- mixing UI cleanup with backend behavior unless they are inseparable
- mixing workflow automation with product code
- mixing refactors with bug fixes if they can be split cleanly

### 3. Stage Carefully

For each commit group:

- stage only the files that belong to that group,
- review the staged set,
- confirm the staged diff matches the intended purpose.

If a file contains both relevant and unrelated edits, prefer not to commit it unless the separation is safe and explicit.

### 4. Commit with Conventional Commits

Use the format:

```txt
type(scope): subject
```

Examples:

```txt
fix(admin): simplify resource table actions
feat(kiosk): add subnet-aware pin validation
ci(github): enforce commitlint on pull requests
```

For breaking changes:

```txt
feat(loan)!: change qr draft consumption contract
```

And include a body when needed:

```txt
BREAKING CHANGE: draft QR payload now requires loan item level identifiers.
```

### 5. Repeat Until Clean

Continue commit-by-commit until every safe, relevant change is committed.

Leave unrelated or ambiguous changes untouched and report them clearly at the end.

## Output Contract

When running this workflow, the agent should report:

1. A short summary of the current worktree.
2. The proposed commit groups before committing.
3. For each commit:
    - purpose
    - files being staged
    - commit message
    - versioning impact
4. A final summary:
    - commits created
    - remaining uncommitted changes, if any
    - whether any ambiguity blocked full completion

## Execution Prompt

Use the following prompt when the user wants analysis + stage + commit:

```txt
You are an AI agent responsible for git workflow in this repository.

Your job is to:
- analyze the current git changes,
- split them into small logical commits,
- stage the correct files for each commit,
- create Conventional Commits,
- and never push.

Rules:
1. Start by checking `git status --short`, `git diff`, and `git diff --staged` when relevant.
2. Group changes by real purpose: feature, fix, refactor, test, docs, config, CI, or build.
3. Do not mix unrelated changes in one commit.
4. Use Conventional Commits in the format `type(scope): subject`.
5. Prefer these scopes when relevant:
   `auth`, `admin`, `catalog`, `loan`, `return`, `kiosk`, `whatsapp`, `similarity`, `settings`, `ui`, `github`, `deps`, `test`, `ci`.
6. Determine the versioning impact of each commit:
   - `fix` => `PATCH`
   - `feat` => `MINOR`
   - `!` or `BREAKING CHANGE:` => `MAJOR`
   - otherwise explain whether it is `no release`
7. Treat auth, member approval, kiosk, QR borrow/return, destructive migrations, required env changes, and external integration contract changes as potentially breaking.
8. Stage only the files that belong to the current commit.
9. Show the intended commit grouping before creating commits.
10. Create commits only when the grouping is clear and safe.
11. Do not push.
12. Do not switch branches.
13. Do not amend existing commits unless explicitly asked.
14. If unrelated or ambiguous changes are mixed together, stop and explain the problem instead of guessing.

Required execution order:
1. Analyze the worktree.
2. Propose commit groups.
3. Stage the first commit group.
4. Review staged changes.
5. Create the commit.
6. Repeat for the next group until safe changes are complete.
7. Report all created commits and any remaining changes.

Output format:
1. Current change summary.
2. Proposed commit list.
3. For each commit:
   - purpose
   - staged files
   - commit message
   - versioning impact
4. Final result with commits created and remaining changes.
```

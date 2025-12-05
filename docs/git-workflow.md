<!-- File: docs/git-workflow.md -->

# Git Workflow

This project follows a simplified **Git Flow** model.

Full original Git Flow explanation: https://nvie.com/posts/a-successful-git-branching-model

## Branches

| Branch      | Purpose                            | Direct commits? | Merge target     |
|-------------|------------------------------------|-----------------|------------------|
| `main`      | Production (always deployable)     | No              | ← develop        |
| `develop`   | Current development / next release | No              | ← feature/*      |
| `feature/*` | Individual tasks                   | Yes             | → develop        |
| `hotfix/*`  | Urgent production fixes            | Yes             | → main + develop |
| `release/*` | Release preparation (rarely used)  | Yes             | → main + develop |

## Naming conventions

- Branches: `feature/JIRA-TICKET-KEY-short-description`  
  Example: `feature/TK-23-user-profile-edit`

## Commit messages

We use **Conventional Commits** → https://www.conventionalcommits.org

Jira ticket key should be included.

```text
feat: TK-18 add user registration en dpoint
fix: TK-19 prevent SQL injection in search
refactor: TK-23 extract authentication service
docs: TK-28 update API documentation
test: TK-20 add tests for password reset
```

## Prohibited

- Direct commits or force-push to `main` and `develop`
- Merging PRs without review
- Working without a feature branch

# Twitter #

How to start/install read in [SETUP.MD](./docs/setup.md)



## 🌿 Branch Structure

* `main` — stable version (production)
* `develop` — active development
* `feature/*` — individual tasks / features

---

### `main`

* Contains production-ready and stable code
* Used for deployment
* Direct commits are **not allowed**
* Accepts changes **only via Pull Requests from `develop`**

---

### `develop`

* The main branch for developers
* All new features are merged here
* Direct commits are **not allowed** — only via `feature/*` branches

---

### `feature/*`

* Branches for individual tasks and features
* Created from `develop`

**Naming format:**

```
feature/task-name
```

**Examples:**

```
feature/SCRUM-22-login-page
feature/SCRUM-37-api-integration
feature/SCRUM-37-admin-dashboard
feature/API-123-login-endpoint
feature/AUTH-45-password-reset
feature/FRONT-12-dashboard-layout
```

---

## 🚫 Not Allowed

* Direct commits to `main` or `develop`
* Working in `develop` without a `feature` branch
* Merging code without [Ihor](https://github.com/ihpr) code review

---

## 📝 Commit Rules

Useful guidance links: 
* [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
* [How to Write a Git Commit Message](https://cbea.ms/git-commit/)


---



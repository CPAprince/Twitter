# PHPStan in CI / "prod"

This document describes how to run **PHPStan** in **non-dev environments**:
CI pipelines (GitHub Actions) and, optionally, other "prod-like" environments.

It is separate from `phpstan.md`, which focuses on **local Docker-based dev setup**
and quick start for developers.

---

## Scope

- **Dev (local)**: `phpstan.md` + `./tools/phpstan.sh`  
  Uses Docker, auto-installs dependencies, smart cache warmup.

- **CI / "prod" (this document)**: `docs/phpstan-ci.md`  
  No auto-install logic, clean environment, deterministic behaviour.

---

## Requirements

PHPStan in CI assumes:

1. PHP 8.4 is available.
2. Composer dependencies are installed via:

   ```bash
   composer install --no-interaction --prefer-dist

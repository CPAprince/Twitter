# PHPStan - Static Analysis

This project uses [PHPStan](https://phpstan.org/) for static analysis
with Symfony and Doctrine integration.

---

## Installation

PHPStan and its extensions are installed as dev dependencies:

```bash
docker compose exec php composer require --dev \
    phpstan/phpstan \
    phpstan/phpstan-symfony \
    phpstan/phpstan-doctrine
```

Make sure the dev container has been warmed up at least once:
```docker compose exec php php bin/console cache:warmup --env=dev```

Run PHPStan:

```docker compose exec php composer phpstan```

Create a type test error in src/:
For example, in any service:
```
public function testPhpStan(): int
{
    return 'not int';
}
```

Expectation:
PHPStan finds a type mismatch error;
the command terminates with a non-zero exit code (fails).

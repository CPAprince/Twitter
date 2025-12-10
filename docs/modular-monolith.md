```text 
src/
├── Iam/
│   ├── resources/
│   │   └── config/
│   │       ├── doctrine/
│   │       │   └── *.orm.xml
│   │       ├── services.yaml
│   │       ├── security.yaml
│   │       └── routes.yaml
│   ├── UI/
│   │   └── Rest/
│   │       ├── Request/
│   │       ├── Controller/
│   │       └── Exception/
│   ├── Application/
│   │   ├── Validator/  # (interfaces) e.g., UniqueEmailCheckerInterface (define contract here) 
│   │   ├── Handler/    # Validate DTO, call validators/interfaces, orchestrate use case
│   │   ├── Command/
│   │   ├── Dto/
│   │   ├── Exception/
│   │   └── Service/
│   ├── Domain/
│   │   ├── Model/
│   │   ├── ValueObject/
│   │   ├── Repository/
│   │   └── Exception/
│   └── Infrastructure/
│       ├── Exception/
│       ├── EventSubscriber/
│       │   ├── Doctrine/
│       │   │   └── HashPasswordSubscriber.php
│       │   └── Symfony/
│       │       └── ExceptionToHttpResponseSubscriber.php
│       ├── Persistence/
│       │   └── MySQL
│       │       ├── Repository/
│       │       └── Migrations/
│       └── Auth/
│           ├── Authenticator/      # Guard/ is soft-deprecated since Symfony 5.3
│           │   └── JsonLoginAuthenticator.php
│           ├── UserProvider.php    # implements Symfony user provider
│           └── UserAdapter.php     # maps Domain\Model\User -> Symfony user
├── Profile/
│   └── ...
└── Tweet/
    └── ...
```

## Modular monolith structure

The structure is solid and modular: each **feature module** (Iam, Profile, Tweet) follows the same vertical-slice
pattern (Resources / UI / Application / Domain / Infrastructure). That’s exactly what you want for a modular monolith:
clear boundaries, easy testing and straightforward extraction later. Below — precise, directory-by-directory
explanation (what it does, why it’s here, and short pitfalls/guidance).

### `src/` (top level)

**What:** root for all application code, organized by feature/module.
**Why:** encourages feature ownership, reduces cross-cutting coupling, simplifies CI/test scoping.
**Pitfalls / guidance:** keep a small `Shared` area for truly cross-cutting contracts (not business logic). Add
top-level `tests/` and `migrations/` or document migration ownership per module.

### `src/Iam/resources/config/`

**What:** YAML/XML config files used by Symfony for this module: Doctrine mapping fragments (`*.orm.xml`),
`services.yaml` (service wiring), `security.yaml` (auth rules), `routes.yaml` (module routes).
**Why:** module-local configuration groups wiring and settings next to code; easier to reason about ownership and to
load only module config when needed.
**Pitfalls / guidance:** ensure each file only contains module-relevant settings. Use `services.yaml` to bind domain
interfaces → infra implementations for that module. Consider using PHP Attributes on entities instead of many XML files
unless XML is a team decision.

### `src/Iam/UI/Rest/Controller/`

**What:** HTTP controllers that handle API endpoints (request → call Application layer → return Response/DTO).
**Why:** controllers are the entry point for HTTP/REST; they translate HTTP into application commands/queries.
**Pitfalls / guidance:** keep controllers **thin** — no business logic. Use Request DTOs and validators. Return mapped
DTOs (not Doctrine entities). Unit-test controllers for wiring and integration tests for end-to-end behavior.

### `src/Iam/UI/Rest/Request/`

**What:** HTTP request objects / validators / request mappers (e.g., `LoginRequest`, `RegisterRequest`).
**Why:** centralizes request parsing and validation; prevents controllers from parsing raw request bodies. Also serves
as a boundary for input validation and API docs.
**Pitfalls / guidance:** perform minimal transformation here. Map to Application DTO/Command and pass that to handlers.
Keep Symfony Form usage optional — JSON APIs often use DTO + Symfony Validator.

### `src/Iam/Application/Handler/`

**What:** Handlers that implement use case orchestration (e.g., `LoginHandler`). They receive Commands/Queries and call
Domain + Repositories + Services.
**Why:** isolates use-case logic, easy to unit test, prevents controllers from containing business logic.
**Pitfalls / guidance:** handlers may call the message bus to publish events; keep heavy side effects asynchronous when
possible.

### `src/Iam/Application/Command/`

**What:** Command/Query objects that carry input data for use cases (e.g., `LoginCommand`), used by handlers.
**Why:** explicit data carriers make interfaces clear and testable; they decouple transport layer from application
layer.
**Pitfalls / guidance:** keep Commands primitive/simple; do not put behavior here.

### `src/Iam/Application/Dto/`

**What:** DTOs for responses or internal mapping (e.g., `LoginResponseDto`, `UserDto`).
**Why:** stable output contracts for UI; avoid exposing domain objects.
**Pitfalls / guidance:** treat DTOs as part of the application API; keep them small and serializable.

### `src/Iam/Application/Service/`

**What:** Application-level services (thin orchestration helpers) that don’t belong in Domain and are used by handlers (
e.g., `PasswordChecker`, `TokenGenerator`).
**Why:** implements processes that are application-scoped and depend on infrastructure (hashers, mailers) but are not
domain rules.
**Pitfalls / guidance:** avoid putting domain rules here. If logic is a core business rule, push to Domain.

### `src/Iam/Domain/Model/`

**What:** domain entities/aggregates (e.g., `User`), with domain behavior and invariants.
**Why:** the source of truth for business logic; should be framework-agnostic and unit-testable without Symfony.
**Pitfalls / guidance:** **never** implement Symfony interfaces or Doctrine types inside domain classes. Expose only
pure PHP APIs and value objects.

### `src/Iam/Domain/ValueObject/`

**What:** immutable small types used in domain (e.g., `UserId`, `Email`).
**Why:** encode invariants, intent and stronger typing inside domain.
**Pitfalls / guidance:** keep them immutable and consider `final` + strict typing.

### `src/Iam/Domain/Repository/`

**What:** repository interfaces (contracts) the domain expects (e.g., `UserRepositoryInterface`).
**Why:** separates persistence implementation from domain. Infrastructure implements these interfaces.
**Pitfalls / guidance:** do not put persistence code here. Define clear method contracts and semantics (throws
exceptions, returns null, etc.).

### `src/Iam/Domain/Exception/`

**What:** domain-specific exceptions (e.g., `UserNotFound`, `InvalidCredentials`).
**Why:** explicit error types enable friendly mapping to HTTP statuses at the infrastructure layer and clearer tests.
**Pitfalls / guidance:** treat domain exceptions as part of domain API; map them to HTTP codes in an Infrastructure
exception subscriber.

### `src/Iam/Infrastructure/EventSubscriber/Doctrine/HashPasswordSubscriber.php`

**What:** Doctrine lifecycle subscriber — listens to entity `prePersist`/`preUpdate` to hash plain passwords before
saving.
**Why:** a classic place to centralize persistence concerns that must hook into the ORM lifecycle. It’s infrastructure
because it depends on Doctrine.
**Pitfalls / guidance:** avoid business rules here. Prefer explicit hashing in the Application handler when possible
(makes behavior explicit for tests). If you keep the subscriber, ensure tests cover it, and it only operates on
infrastructure DTOs or explicit entity state.

### `src/Iam/Infrastructure/EventSubscriber/Symfony/ExceptionToHttpResponseSubscriber.php`

**What:** listens to Symfony kernel exceptions and maps selected exceptions to standardized HTTP responses (JSON API
error shape).
**Why:** converts domain or application exceptions into API-friendly HTTP responses centrally. Infrastructure because it
depends on `KernelEvents`.
**Pitfalls / guidance:** keep mapping rules minimal and explicit. Do not implement complex logic in subscribers.

### `src/Iam/Infrastructure/Persistence/MySQL/Repository/`

**What:** concrete repository implementations using Doctrine/DBAL (e.g., `MySqlUserRepository`). They implement domain
repository interfaces.
**Why:** they bind domain repository contracts to a concrete storage technology.
**Pitfalls / guidance:** ensure repositories return domain objects (or mappers that produce domain aggregates) — do not
leak ORM proxies or Entities tied to Doctrine into Domain or Application layers. Use mappers to convert ORM
rows/entities → Domain objects if needed.

### `src/Iam/Infrastructure/Persistence/MySQL/Migrations/`

**What:** module-owned DB migration files for schema changes related to this module.
**Why:** clear ownership and easier reasoning about which module touched which tables.
**Pitfalls / guidance:** coordinate migration ordering in CI; avoid multiple modules changing the same table. Consider
central runner or naming conventions to avoid collisions.

### `src/Iam/Infrastructure/Auth/Authenticator/JsonLoginAuthenticator.php`

**What:** Symfony Authenticator class for the API JSON login path — extracts credentials, builds `Passport`, returns an
authentication result. Replaces legacy Guard classes.
**Why:** implements authentication flow in modern Symfony (supports stateless JWT/Firebase or session-based flows).
Infrastructure-specific.
**Pitfalls / guidance:** keep authentication transport code here only. Delegate password checks and token generation to
Application/Service.

### `src/Iam/Infrastructure/Auth/UserProvider.php`

**What:** implements Symfony `UserProviderInterface` / `PasswordAuthenticatedUserProviderInterface` — loads a user by
identifier during authentication. Typically, uses a domain `UserRepositoryInterface`.
**Why:** adapter between Symfony Security and your Domain repositories.
**Pitfalls / guidance:** return a `UserAdapter` (or minimal security user) — do not return domain entities directly
unless very carefully adapted.

### `src/Iam/Infrastructure/Auth/UserAdapter.php`

**What:** adapter that implements Symfony `UserInterface` (and `PasswordAuthenticatedUserInterface`) and internally
wraps a Domain `User`. Exposes `getUserIdentifier()`, `getPassword()`, `getRoles()`.
**Why:** keeps Domain free from Symfony types while making domain data usable by Symfony Security.
**Pitfalls / guidance:** keep this adapter thin and stateless; it should only delegate to domain getters. Avoid adding
logic here.

### `src/Profile/` and `src/Tweet/`

**What:** other feature modules following the same vertical-slice pattern. Each contains its own `resources`, `UI`,
`Application`, `Domain`, `Infrastructure`.
**Why:** same rationale — independent ownership, easier refactor/extract.
**Pitfalls / guidance:** avoid direct coupling between modules; prefer interfaces, events (Messenger) or a
`Shared/Contract` for cross-module contracts.

## Cross-cutting concerns and recommended additions

1. **Shared/Contract** — a small place for stable contracts (pagination DTOs, domain event interfaces) used by multiple
   modules. Don’t put business logic here.
2. **Composer PSR-4 mapping** — ensure each module namespace is mapped in `composer.json` so static analysis and
   autowiring are predictable.
3. **Deptrac / architecture rules** — add to CI to prevent module boundary violations
   (UI→Application→Domain→Infrastructure only).
4. **Static analysis & tests** — PHPStan (strict), Psalm, and module-level unit tests + contract tests. Add integration
   tests for Messenger and DB interactions.
5. **Messenger / Domain Events** — domain events (in `Domain/Event` or `Application`) should be dispatched by handlers
   and handled by async handlers in `Infrastructure` or other modules. Use `Messenger` to decouple modules.
6. **Migration strategy** — document module migration ownership; consider a CI step that runs migrations in a
   deterministic order.

## Practical summary

* Keep **Domain** pure and stable.
* Implement adapters in **Infrastructure** (Authenticator, UserProvider, UserAdapter).
* Orchestrate use cases in **Application** via Commands/Handlers and publish domain events for cross-module
  interactions.
* Use module-local `resources/config` to make module self-contained and easier to extract.

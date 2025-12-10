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

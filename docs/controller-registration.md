# How to Create and Register Controllers

## Create a new controller

Controllers are located in `Controller` directory of corresponding modules,
for example `../src/Health/UI/REST/Controller/HealthController.php)`.
Each controller class name **must** have `Controller` prefix.

## Register routes

Controller configuration has to be done manually added to [`routes.yaml`](../config/routes.yaml) in the following way:

```yaml
<route_name_in_snake_case>:
    path: /<prefix>/<route>
    resource: Twitter\<Module>\UI\REST\Controller\<Controller>
    methods: GET, POST, PUT, DELETE
```

For example, [Health](../src/Health/UI/REST/Controller/HealthController.php) controller:

```yaml
api_health:
    path: /api/health
    controller: Twitter\Health\UI\REST\Controller\HealthController
    methods: GET
```

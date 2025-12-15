# Services Configuration

## CQRS

### `_instanceof`

Applies configuration to **all services that implement a specific interface**, without listing them manually. When
Symfony registers any service implementing `CommandHandlerInterface` or `QueryHandlerInterface`, it automatically
receives the `app.command_handler` tag. This eliminates repetitive per-handler configuration.

### Interface Aliases

```yaml
CommandBusInterface:
    alias: InMemoryCommandBus
```

The `alias` tells Symfony to inject the `InMemoryCommandBus` service when any service type-hints `CommandBusInterface`.

### Bus `arguments` with `!tagged_locator`

```yaml
InMemoryCommandBus:
    arguments:
        $handlersLocator: !tagged_locator 'app.command_handler'
```

The `!tagged_locator` creates a **service locator** containing all services tagged with `app.command_handler`. This
locator is injected into the bus constructor as `$handlersLocator`.

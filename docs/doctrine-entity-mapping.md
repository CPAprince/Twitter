# How to Create and Map Doctrine Entities

## Create a new entity

Entities are located in `Entity` directory of corresponding modules,
for example `../src/HealthCheck/Entity/HealthCheckReport.php)`.

## Doctrine XML mapping

### Create an XML file

Doctrine entities corresponding `.orm.xml` files are located in [`config/doctrine`](../config/doctrine) directory.
The mapping filename has the following structure: `<ModuleName>.<EntityDirectiry>.<EntityClassName>.orm.xml`, for
example: [
`HealthCheck.Entity.HealthCheckReport.orm.xml`](../config/doctrine/HealthCheck.Entity.HealthCheckReport.orm.xml).

Example configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="
                    http://doctrine-project.org/schemas/orm/doctrine-mapping
                    https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <entity name="MyApp\SomeModule\Entity\SomeEntity" table="some_entities">

        <id name="id" type="integer">
            <generator strategy="AUTO"/>
        </id>

        <field name="title" type="string"/>
        <field name="description" type="text"/>
        <field name="createdAt" type="datetime_immutable"/>

    </entity>

</doctrine-mapping>
```

### Update Doctrine schema

```shell
# Validate XML mappings
docker compose exec php php bin/console doctrine:schema:validate
# Create required migrations based on current database schema
docker compose exec php php bin/console doctrine:migrations:diff

docker compose exec php php bin/console doctrine:migrations:migrate
docker compose exec php php bin/console doctrine:migrations:up-to-date
```

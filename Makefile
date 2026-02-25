router:
	docker compose exec php bin/console debug:router

cache-clear:
	docker compose exec php php bin/console cache:clear

stan:
	docker compose exec php php -d memory_limit=1G vendor/bin/phpstan analyse

cs:
	docker compose exec php vendor/bin/php-cs-fixer fix --diff --allow-risky=yes

csdr:
	docker compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes

test:
	docker compose exec php vendor/bin/phpunit

about:
	docker compose exec php php bin/console about

start:
	docker compose up --wait -d

stop:
	docker compose down

allcheks: stan cs test

r: router
cc: cache-clear
s: stan
t: test

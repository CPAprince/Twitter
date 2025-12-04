#!/usr/bin/env bash
set -e

# 1. Ensure PHP container is up
docker compose up -d php

# 2. Run everything inside the php container
docker compose exec php sh -lc '
missing=""

for pkg in phpstan/phpstan phpstan/phpstan-symfony phpstan/phpstan-doctrine; do
    if ! composer show "$pkg" >/dev/null 2>&1; then
        missing="$missing $pkg"
    fi
done

if [ -n "$missing" ]; then
    echo "Installing missing PHPStan packages:$missing"
    composer require --dev $missing
else
    echo "All PHPStan packages are already installed."
fi

CONTAINER_XML="var/cache/dev/App_KernelDevDebugContainer.xml"

if [ ! -f "$CONTAINER_XML" ]; then
    echo "Symfony container XML not found, warming up cache..."
    php bin/console cache:warmup --env=dev
else
    echo "Symfony container XML already exists, skipping cache:warmup."
fi

echo "Running PHPStan via composer phpstan..."
composer phpstan
'

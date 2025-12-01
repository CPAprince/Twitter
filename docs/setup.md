# Symfony Franken MySQL API Template

## Getting started

```sh
git clone https://github.com/CPAprince/Twitter.git
cd Twitter
```

Install [Composer](http://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
and required packages

```sh
composer install
composer update
```

## Build and run Docker containers

```sh
docker compose build --pull --no-cache
docker compose up -d
```

Wait a bit the database to launch and PHP to connect to it. After that you can open https://localhost

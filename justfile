compose := "docker compose"
exec    := compose + " run --rm php"

# Show available recipes
default:
    @just --list

# Build the dev container (passes host UID/GID)
build:
    HOST_UID=$(id -u) HOST_GID=$(id -g) {{compose}} build

# Run PHP's built-in server on http://localhost:8000 (serves ./public)
up:
    {{compose}} run --rm --service-ports php php -S 0.0.0.0:8000 -t public

# Open an interactive bash shell in the container
shell:
    {{exec}} bash

# Run composer, e.g. `just composer install` or `just composer require foo/bar`
composer *args:
    {{exec}} composer {{args}}

# Run PHPUnit
test *args:
    {{exec}} vendor/bin/phpunit {{args}}

# Run PHPStan
phpstan *args:
    {{exec}} vendor/bin/phpstan analyse {{args}}

# Run php-cs-fixer (dry-run + diff)
cs:
    {{exec}} vendor/bin/php-cs-fixer fix --dry-run --diff

# Run php-cs-fixer (apply changes)
cs-fix:
    {{exec}} vendor/bin/php-cs-fixer fix

# Run Rector (dry-run)
rector:
    {{exec}} vendor/bin/rector process --dry-run

# Run Rector (apply changes)
rector-fix:
    {{exec}} vendor/bin/rector process
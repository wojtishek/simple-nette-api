#!/bin/bash
set -e

cd /srv
composer install
php vendor/bin/openapi app -o www/openapi.yml
php bin/console migrations:migrate --no-interaction

chmod -R 777 /srv/temp
chmod -R 777 /srv/log

exec "$@"
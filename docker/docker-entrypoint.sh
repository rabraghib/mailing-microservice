#!/bin/sh
set -e

until composer exec doctrine dbal:run-sql "select 1" >/dev/null 2>&1; do
        (>&2 echo "Waiting for MariaDB to be ready...")
    sleep 1
done
composer run-script migrations:migrate

exec "$@"
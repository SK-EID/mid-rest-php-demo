#!/bin/bash
#
# Waits for the database to accept connections and runs the Doctrine migrations.
set -e

cd /app

for i in $(seq 1 30); do
    if php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; then
        break
    fi
    echo "Waiting for database ($i)..."
    sleep 2
done

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

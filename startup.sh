#!/bin/bash

# Wait for MariaDB to be ready
until mysqladmin ping -h"$DRUPAL_DB_HOST" --silent; do
    echo "Waiting for MariaDB..."
    sleep 5
done

echo "🚀 Checking for module updates..."
composer install --no-interaction --prefer-dist

echo "📥 Importing latest Drupal configuration..."
drush config-import -y

echo "🧹 Clearing cache..."
drush cr

echo "✅ Startup checks completed."

# Keep container running with Apache
exec apache2-foreground

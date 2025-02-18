#!/bin/bash

echo "🚀 Checking for module updates..."
composer install --no-interaction --prefer-dist

echo "🚀 Importing latest Drupal configuration..."
drush config-import -y

echo "🚀 Clearing cache..."
drush cr

echo "✅ Startup checks completed."

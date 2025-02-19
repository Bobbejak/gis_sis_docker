#!/bin/bash

echo "Setting correct permissions..."
chown -R www-data:www-data /var/www/html/sites/default/files
chown -R www-data:www-data /var/www/html/sites/default/files/translations
chmod -R 775 /var/www/html/sites/default/files
chmod -R 775 /var/www/html/sites/default/files/translations

echo "Installing modules..."
drush pm:enable admin_toolbar -y
drush cr

echo "Starting services..."
apachectl -D FOREGROUND

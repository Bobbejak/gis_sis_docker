#!/bin/bash
# Convert CRLF to LF if needed
if file "$0" | grep -q CRLF; then
    echo "Fixing line endings for startup.sh..."
    sed -i 's/\r$//' "$0"
    chmod +x "$0"  # Ensure it's still executable after conversion
fi

# # Set PHP memory limit
# echo "INCREASING PHP MEMORY LIMIT..."
# echo "MEMORY_LIMIT = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# # Enable OPcache
# echo "ENABLING OPCACHE..."
# echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini
# echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini
# echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini

# Set correct permissions for the files and translations directories
echo "🔒 SETTING PERMISSIONS FOR THE FILES AND TRANSLATIONS DIRECTORIES..."
chown -R www-data:www-data /var/www/html/sites/default/files
chmod -R 775 /var/www/html/sites/default/files

# Set correct permissions for the translations directory
echo "🔒 Setting permissions for the translations directory..."
chown -R www-data:www-data /var/www/html/sites/default/files/translations
chmod -R 775 /var/www/html/sites/default/files/translations

# Set permission for db custom.cnf file
chmod 644 /etc/mysql/conf.d/custom.cnf

echo "PERMISSIONS SET"
echo "RESTART CONTAINER TO TAKE EFFECT"

# echo "DISABLING KEEPALIVE FOR BETTER PERFORMANCE..."
# echo "KeepAlive Off" >> /etc/apache2/apache2.conf

# # Restart Apache for changes to take effect
# echo "RESTARTING APACHE..."
# service apache2 restart

# Update packages and install utilities
echo "UPDATING SYSTEM..."
apt update && apt install -y curl git unzip nano default-mysql-client
echo "UPDATE COMPLETE."

echo "CHECKING IF COMPOSER IS INSTALLED"
# Install Composer if not already installed
if ! [ -x "$(command -v composer)" ]; then
  echo 'NOT INSTALLED. INSTALLING COMPOSER...'
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi
  echo 'COMPOSER INSTALLATION COMPLETE'

echo "CHECKING IF DRUSH IS INSTALLED"
# Install Drush if not already installed
if ! [ -x "$(command -v drush)" ]; then
  echo 'NOT INSTALLED. INSTALLING DRUSH...'
  composer require drush/drush
  ln -sf /var/www/html/vendor/bin/drush /usr/local/bin/drush
fi
  echo 'DRUSH INSTALLATION COMPLETE'

echo 'NAVIGATING TO /var/www/html'
# Navigate to the Drupal installation directory
cd /var/www/html

# Run Composer install
read -p "Do you want to run 'composer install'? (y/n): " run_composer
if [[ "$run_composer" =~ ^[Yy]$ ]]; then
  composer install
fi

read -p "Have you installed Drupal through the browser'? (y/n): " drupal_installed
  if [[ "$drupal_installed" =~ ^[Yy]$ ]]; then

    # Clear Drupal cache
    read -p "Do you want to clear the Drupal cache with 'drush cr'? (y/n): " clear_cache
    if [[ "$clear_cache" =~ ^[Yy]$ ]]; then
      drush cr
    fi

    # Import Drupal configuration
    read -p "Do you want to import your sql dump file (name should be ./local/import/db_backups/drush_gis_sis.sql in host pc) in db_backups? (y/n): " import_dumpfile
    if [[ "$import_dumpfile" =~ ^[Yy]$ ]]; then
      drush sql-drop
      drush sql-cli < /db_backups/gis_sis_drush.sql
    fi

    # Enable contributed modules
    read -p "Do you want to enable contributed modules? (y/n): " enable_modules
    if [[ "$enable_modules" =~ ^[Yy]$ ]]; then
      NEW_MODULES=$(ls -1 /var/www/html/modules/contrib/)
      echo "ENABLING MODULES"
      for MODULE in $NEW_MODULES; do
          echo "Enabling module: $MODULE"
          drush pm-enable $MODULE -y
      done
      echo "MODULES PROCEDURE FINISHED."
    fi

    read -p "Do you want to enable custom modules? (y/n): " enable_modules
    if [[ "$enable_modules" =~ ^[Yy]$ ]]; then
      NEW_MODULES=$(ls -1 /var/www/html/modules/custom/)
      echo "ENABLING MODULES"
      for MODULE in $NEW_MODULES; do
          echo "Enabling module: $MODULE"
          drush pm-enable $MODULE -y
      done
      echo "MODULES PROCEDURE FINISHED."
    fi

    # Update Drupal database
    read -p "Do you want to update the database with 'drush updb'? (y/n): " update_db
    if [[ "$update_db" =~ ^[Yy]$ ]]; then
      drush updb
      echo "drush updb PROCEDURE FINISHED."
    fi

    # Import Drupal configuration
    read -p "Do you want to import configuration with 'drush cim'? (y/n): " import_config
    if [[ "$import_config" =~ ^[Yy]$ ]]; then
      drush cim
      echo "drush cim PROCEDURE FINISHED."
    fi

    # Clear Drupal cache
    read -p "Do you want to clear the Drupal cache with 'drush cr'? (y/n): " clear_cache
    if [[ "$clear_cache" =~ ^[Yy]$ ]]; then
      drush cr
    fi

fi

echo "STARTUP/SETUP SCRIPT ENDED."
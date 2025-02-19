#!/bin/bash

# Update packages and install utilities
echo "UPDATING SYSTEM..."
apt update && apt install -y curl git unzip nano default-mysql-client
echo "UPDATE COMPLETE."

# Set correct permissions for the files and translations directories
echo "🔒 SETTING PERMISSIONS FOR THE FILES AND TRANSLATIONS DIRECTORIES..."
chown -R www-data:www-data /var/www/html/sites/default/files
chmod -R 775 /var/www/html/sites/default/files

# Set correct permissions for the translations directory
echo "🔒 Setting permissions for the translations directory..."
chown -R www-data:www-data /var/www/html/sites/default/files/translations
chmod -R 775 /var/www/html/sites/default/files/translations
echo "🔒 PERMISSIONS SET"


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

echo 'CHECKING IF DRUPAL IS INSTALLED'
# Check if Drupal is already installed
if [ -f web/sites/default/services.yml ]; then
  echo "DRUPAL SITE NOT FOUND!"
  echo "PLEASE INSTALL DRUPAL MANUALLY THROUGH THE BROWSER AT HTTP://LOCALHOST:8080."
  echo "AFTER INSTALLATION, RERUN THIS SCRIPT TO COMPLETE SETUP."
else
  echo "DRUPAL SITE DETECTED. CONTINUING WITH MAINTENANCE TASKS..."
fi

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
    read -p "Do you want to import your sql dump file (name should be db_backups/drush_gis_sis.sql) in db_backups? (y/n): " import_dumpfile
    if [[ "$import_dumpfile" =~ ^[Yy]$ ]]; then
      drush sql-drop
      drush sql-cli < /db_backups/drush_gis_sis.sql
    fi

    # Set correct permissions for the files and translations directories
    # echo "FIRST SETTING PERMISSIONS..."
    # chown -R www-data:www-data /var/www/html/modules/contrib
    # chmod -R 775 /var/www/html/modules/contrib
    # chown -R www-data:www-data /var/www/html/modules/custom
    # chmod -R 775 /var/www/html/modules/custom

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


echo "STARTUP/SETUP SCRIPT COMPLETED."

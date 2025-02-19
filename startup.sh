#!/bin/bash

# Update packages and install utilities
apt update && apt install -y curl git unzip nano

# Install Composer if not already installed
if ! [ -x "$(command -v composer)" ]; then
  echo 'Installing Composer...'
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi

# Install Drush if not already installed
if ! [ -x "$(command -v drush)" ]; then
  echo 'Installing Drush...'
  composer require drush/drush
  ln -sf /var/www/html/vendor/bin/drush /usr/local/bin/drush
fi

# Navigate to the Drupal installation directory
cd /var/www/html

# Check if Drupal is already installed
if [ ! -f web/sites/default/settings.php ]; then
  echo "⚠️  Drupal site not found!"

  read -p "Do you want to install Drupal now? (y/n): " install_drupal
  if [[ "$install_drupal" =~ ^[Yy]$ ]]; then
    echo "Installing Drupal..."
    drush site:install standard \
      --account-name=admin \
      --account-pass=admin \
      --db-url="mysql://gis_sis_user:user_240885MP@db/gis_sis" \
      --site-name="MySchool Site" \
      -y
    echo "Drupal installation completed."
  else
    echo "❌ Skipping Drupal installation. Please ensure Drupal is installed before using Drush."
    exit 1
  fi
else
  echo "✅ Drupal site detected."
fi

# Run Composer install
read -p "Do you want to run 'composer install'? (y/n): " run_composer
if [[ "$run_composer" =~ ^[Yy]$ ]]; then
  composer install
fi

# Clear Drupal cache
read -p "Do you want to clear the Drupal cache with 'drush cr'? (y/n): " clear_cache
if [[ "$clear_cache" =~ ^[Yy]$ ]]; then
  drush cr
fi

# Update Drupal database
read -p "Do you want to update the database with 'drush updb'? (y/n): " update_db
if [[ "$update_db" =~ ^[Yy]$ ]]; then
  drush updb -y
fi

# Import Drupal configuration
read -p "Do you want to import configuration with 'drush cim'? (y/n): " import_config
if [[ "$import_config" =~ ^[Yy]$ ]]; then
  drush cim -y
fi

# Set correct permissions for the translations directory
read -p "Do you want to set permissions for the translations directory? (y/n): " set_permissions
if [[ "$set_permissions" =~ ^[Yy]$ ]]; then
  chown -R www-data:www-data /var/www/html/sites/default/files/translations
  chmod -R 775 /var/www/html/sites/default/files/translations
fi

echo "🎉 Startup script completed successfully."

FROM drupal:10-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install Composer
RUN apt-get install -y curl unzip git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Install Drupal using Composer
RUN composer create-project drupal/recommended-project:10.4.0 /var/www/html --no-dev --prefer-dist

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Enable Apache mod_rewrite (required for Drupal)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]

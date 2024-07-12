FROM php:8.2-apache

# Install necessary packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    nano \
    vi \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy custom php.ini
COPY php/php.ini /usr/local/etc/php/

# Set working directory
WORKDIR /var/www/symfony

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the existing application directory contents
COPY . /var/www/symfony

# Set permissions
RUN chown -R www-data:www-data /var/www/symfony/var /var/www/symfony/vendor

# Install Symfony dependencies as www-data user
USER www-data

RUN composer install --no-interaction

# Revert back to root user to complete other tasks
USER root

# Expose port 80
EXPOSE 80

# Configure Apache to pass Authorization header
RUN echo "SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=\$1" >> /etc/apache2/apache2.conf
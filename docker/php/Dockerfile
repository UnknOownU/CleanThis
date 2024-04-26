FROM php:8.2.12-fpm

# Update system packages and install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libicu-dev  # For intl extension

# Install PHP extensions
RUN docker-php-ext-install zip pdo pdo_mysql intl

# Set the working directory
WORKDIR /var/www/cleanthis

# Install Composer globally
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install API Platform
RUN composer require api-platform/core


# Utilise l'image PHP FPM de base
FROM php:8.2.12-fpm

# Met à jour les packages systèmes et installe les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libicu-dev \
    git \
    unzip \
    curl

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install zip pdo pdo_mysql intl

# Définit le répertoire de travail
WORKDIR /var/www/cleanthis

# Installe Composer globalement
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Installe API Platform et autres bundles utiles pour le développement
RUN composer require api-platform/core

RUN composer require --dev symfony/maker-bundle symfony/profiler-pack


# Ajuste les configurations PHP pour le développement
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=100M"; \
    echo "max_execution_time=300"; \
    echo "opcache.enable=1"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=1"; \
    echo "display_errors=on"; \
    echo "display_startup_errors=on"; \
    echo "xdebug.mode=develop,debug"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
    echo "xdebug.start_with_request=yes"; \
    echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    echo "upload_max_filesize=100M" >> /usr/local/etc/php/conf.d/docker-php-upload-size.ini; \
    echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/docker-php-max-execution-time.ini; \
    echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-opcache.ini; \
    echo "opcache.revalidate_freq=1" >> /usr/local/etc/php/conf.d/docker-php-opcache.ini;\
    } > /usr/local/etc/php/conf.d/docker-php-dev.ini

# Nettoie les listes de packages pour minimiser la taille de l'image
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Expose le port 9000 pour PHP-FPM
EXPOSE 9000

# Commande exécutée au démarrage du conteneur
CMD ["php-fpm"]



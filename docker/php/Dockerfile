# Utilise l'image PHP FPM de base
FROM php:8.2.12-fpm

# Met à jour les packages systèmes et installe les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libicu-dev \
    git \
    unzip \
    curl \
    procps \  
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install zip pdo pdo_mysql intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Crée les dossiers nécessaires pour Symfony
RUN mkdir -p /symfony/var /symfony/vendor

# Définit le répertoire de travail
WORKDIR /var/www/cleanthis

# Installe Composer globalement
COPY --from=composer /usr/bin/composer /usr/bin/composer

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
    } > /usr/local/etc/php/conf.d/docker-php-dev.ini

# Définit les volumes pour persister les données
VOLUME /symfony/var
VOLUME /symfony/vendor

# Change ownership and permissions of the work directory to avoid permission issues
RUN chown -R www-data:www-data /var/www/cleanthis && \
    chmod -R 775 /var/www/cleanthis

# Copie le script de démarrage
COPY setup-symfony.sh /usr/local/bin/setup-symfony
RUN chmod +x /usr/local/bin/setup-symfony

# Expose le port 9000 pour PHP-FPM
EXPOSE 9000

# Commande exécutée au démarrage du conteneur
CMD ["setup-symfony"]

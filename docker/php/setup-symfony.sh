#!/bin/bash

# Attendez que le volume soit monté et accessible
while [ ! -f /var/www/cleanthis/composer.json ]; do
  echo "Waiting for volume to mount..."
  sleep 1
done

# Création des liens symboliques pour var et vendor si ce n'est pas déjà fait
if [ ! -d "/var/www/cleanthis/var" ]; then
  ln -s /symfony/var /var/www/cleanthis/var && echo "Linked /symfony/var to /var/www/cleanthis/var"
fi

if [ ! -d "/var/www/cleanthis/vendor" ]; then
  ln -s /symfony/vendor /var/www/cleanthis/vendor && echo "Linked /symfony/vendor to /var/www/cleanthis/vendor"
fi

# Maintenant, démarrons PHP-FPM en arrière-plan
php-fpm -D && echo "PHP-FPM started..."

# Vérifiez que PHP-FPM fonctionne bien
if ! pgrep -x "php-fpm" > /dev/null
then
    echo "PHP-FPM failed to start, exiting..."
    exit 1
fi

# Et on attend indéfiniment pour empêcher le conteneur de sortir
echo "Container is now running..."
tail -f /dev/null

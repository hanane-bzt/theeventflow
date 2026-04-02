FROM php:8.2-cli
 
# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
 
# Installer composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
 
WORKDIR /app
 
# Copier fichiers
COPY . .
 
# Installer dépendances Symfony
RUN composer install --no-interaction --optimize-autoloader
 
# Créer les dossiers var nécessaires à Symfony
RUN mkdir -p var/cache var/log && chmod -R 777 var
 
# Exposer port Render
EXPOSE 10000
 
# Démarrer : mise à jour BDD puis serveur PHP (exec = PHP devient PID 1)
CMD php bin/console doctrine:database:create --if-not-exists \
    && php bin/console doctrine:schema:update --force \
    && exec php -S 0.0.0.0:10000 -t public public/index.php

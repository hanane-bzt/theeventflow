FROM php:8.2-cli

# Installer dépendances

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

# Exposer port Render

EXPOSE 10000

# Lancer serveur

CMD php bin/console doctrine:query:sql "DROP SCHEMA public CASCADE; CREATE SCHEMA public;" \
&& php bin/console doctrine:migrations:migrate --no-interaction \
&& php bin/console doctrine:fixtures:load --no-interaction \
&& php -S 0.0.0.0:10000 -t public
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo pdo_mysql

RUN pecl install mongodb && docker-php-ext-enable mongodb
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-interaction --no-dev --optimize-autoloader
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000

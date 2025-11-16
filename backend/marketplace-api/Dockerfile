# Etapa base con PHP y Composer
FROM php:8.2-cli

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo pdo_mysql

# Instalar extensión de MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Dar permisos al almacenamiento
RUN chmod -R 777 storage bootstrap/cache

# Copiar entruypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exponer puerto
EXPOSE 8000

# —— Usar entrypoint ——
ENTRYPOINT ["/entrypoint.sh"]

# —— Esto se ejecutará después del entrypoint ——
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

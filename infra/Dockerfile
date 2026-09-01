FROM php:8.4-fpm

# Dependencias del sistema necesarias para las extensiones PHP y PostGIS client
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libgeos-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP: pdo_pgsql/pgsql para PostgreSQL, gd/zip/bcmath/mbstring estándar
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    bcmath \
    mbstring \
    gd \
    exif

# Redis vía PECL (para cache/queues)
RUN pecl install redis && docker-php-ext-enable redis

# Opcache — recomendado en producción
RUN docker-php-ext-install opcache
COPY infra/docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-custom.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction || true

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

EXPOSE 9000

CMD ["php-fpm"]

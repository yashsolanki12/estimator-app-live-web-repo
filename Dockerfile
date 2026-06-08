FROM php:8.3.21-fpm-alpine

ARG SHOPIFY_API_KEY
ENV SHOPIFY_API_KEY=${SHOPIFY_API_KEY}

# Install system packages
RUN apk add --no-cache \
    postgresql-dev \
    mariadb-dev \
    sqlite \
    sqlite-dev \
    oniguruma-dev \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    pkgconf \
    nodejs \
    npm \
    nginx \
    bash \
    su-exec \
    openrc

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo_mysql pdo_pgsql pdo_sqlite mbstring exif pcntl bcmath zip opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Application directory
WORKDIR /app

# Copy application FIRST (before composer to ensure .dockerignore applies)
COPY --chown=www-data:www-data web .

# PHP configuration
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install PHP dependencies as root
RUN php -m && composer --version && \
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Laravel permissions
RUN mkdir -p \
    /app/storage/logs \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/bootstrap/cache && \
    chown -R www-data:www-data \
    /app/storage \
    /app/bootstrap/cache

# Nginx config
COPY web/nginx.conf /etc/nginx/nginx.conf

# Entrypoint
RUN chmod +x /app/entrypoint.sh && \
    sed -i 's/\r$//' /app/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/app/entrypoint.sh"]
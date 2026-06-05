# ============================================================
# Stage 1: Node — build frontend assets (Vite + TailwindCSS 4)
# ============================================================
FROM node:22-alpine AS node_builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install --ignore-scripts

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ============================================================
# Stage 2: PHP 8.3 — production image
# ============================================================
FROM php:8.3-fpm-alpine AS app

# --- System dependencies ---
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

# --- PHP extensions ---
RUN docker-php-ext-configure intl \
 && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        mbstring \
        bcmath \
        intl \
        opcache \
        pcntl

# --- Redis extension (PECL) ---
RUN pecl install redis \
 && docker-php-ext-enable redis

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Nginx (to serve PHP-FPM) ---
RUN apk add --no-cache nginx supervisor

# --- Working directory ---
WORKDIR /var/www/html

# --- PHP dependencies ---
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --optimize-autoloader

# --- Copy application source ---
COPY . .

# --- Copy built frontend assets from node stage ---
COPY --from=node_builder /app/public/build ./public/build

# --- Finalize composer autoload & run post-install scripts ---
RUN composer dump-autoload --optimize \
 && composer run-script post-autoload-dump || true

# --- Permissions ---
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# --- Nginx config ---
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# --- PHP-FPM config ---
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# --- Supervisor config (runs nginx + php-fpm together) ---
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --- Entrypoint ---
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]

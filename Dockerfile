# Smart Maket IoT - Production Dockerfile for Render.com
FROM php:8.2-cli-alpine

# Install system dependencies & SQLite extension
RUN apk add --no-cache \
    sqlite-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_sqlite pcntl

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Set default production environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV DB_CONNECTION=sqlite
ENV LOG_CHANNEL=stderr

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create database and storage directories with write permissions
RUN mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && touch database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache database

# Render injects $PORT (default 8080 if not set)
EXPOSE 8080

CMD ["sh", "-c", "touch database/database.sqlite && if [ -z \"$APP_KEY\" ]; then php artisan key:generate --force; fi && php artisan migrate --force && php artisan db:seed --force && php artisan config:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]

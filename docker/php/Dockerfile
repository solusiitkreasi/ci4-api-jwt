# Multi-stage Dockerfile untuk optimasi
FROM php:8.1-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    nginx

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create system user to run Composer and Artisan Commands
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change current user to www
USER www

# ===================================
# Development stage
# ===================================
FROM base AS development

USER root

# Install development dependencies
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Set development PHP configuration
COPY docker/php/php.development.ini /usr/local/etc/php/php.ini

USER www

# Install composer dependencies with dev packages
RUN composer install --optimize-autoloader

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# ===================================
# Production stage
# ===================================
FROM base AS production

USER root

# Set production PHP configuration
COPY docker/php/php.production.ini /usr/local/etc/php/php.ini

USER www

# Install composer dependencies without dev packages
RUN composer install --optimize-autoloader --no-dev

# Optimize for production
RUN php spark optimize

# Set proper permissions
USER root
RUN chown -R www:www /var/www/html/writable
RUN chmod -R 755 /var/www/html/writable

USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

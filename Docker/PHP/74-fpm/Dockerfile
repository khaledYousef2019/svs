# Use the official PHP 7.4 FPM image as the base
FROM php:7.4-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libpspell-dev \
    redis-server \
    supervisor \
    cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    gd \
    intl \
    pdo \
    pdo_mysql \
    mbstring \
    tokenizer \
    xml \
    ctype \
    json \
    curl \
    zip \
    bcmath \
    pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (PHP Dependency Manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure Composer
RUN composer config --global process-timeout 2000
RUN composer global config --no-plugins allow-plugins.kylekatarnls/update-helper true

# Install Laravel Horizon
RUN composer global require laravel/horizon

# After installing Composer in the Dockerfile
RUN composer global install --optimize-autoloader --prefer-dist

# Copy Supervisor configuration file
COPY Docker/PHP/74-fpm/supervisord.conf /etc/supervisor/supervisord.conf

# Create directories for supervisor and logs
RUN mkdir -p /var/log/supervisor

# Copy application files
COPY . /var/www/html

# Set the correct permissions for the storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Set working directory
WORKDIR /var/www/html

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start Supervisor, which will manage both PHP-FPM and cron
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]

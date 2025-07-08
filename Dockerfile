# Use PHP 8.3.3 FPM as base image
FROM php:8.3.3-fpm

# Set environment variables
ENV COMPOSER_PROCESS_TIMEOUT=600
ENV REBUILD_DB=1

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    nodejs \
    npm \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libxml2-dev \
    libzip-dev \
    libc-dev \
    wget \
    zlib1g-dev \
    zip \
    unzip \
    git \
    default-mysql-client \
    supervisor \
    procps \
    psmisc \
    apt-transport-https \
    gnupg \
    lsb-release \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        soap \
        zip \
        iconv \
        bcmath \
        sockets \
        exif \
        pcntl

# Create SSL certificate symlink
RUN mkdir -p /etc/pki/tls/certs && \
    ln -s /etc/ssl/certs/ca-certificates.crt /etc/pki/tls/certs/ca-bundle.crt

# Install Redis extension
RUN wget -O redis-5.3.7.tgz 'https://pecl.php.net/get/redis-5.3.7.tgz' \
    && pecl install redis-5.3.7.tgz \
    && rm -rf redis-5.3.7.tgz \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Install Swoole extension
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Copy composer files first for better caching
COPY composer.* /var/www/

# Copy PHP configuration
COPY ./init/php.development.ini /usr/local/etc/php/php.ini

# Copy the application
COPY . /var/www

# Install Node.js dependencies and build assets
RUN npm install --save-dev chokidar \
    && npm run build 2>/dev/null || npm run production 2>/dev/null || echo "No build script found"

# Laravel setup
RUN composer install \
    && php artisan octane:install --server=swoole --no-interaction \
    && php artisan storage:link \
    && php artisan optimize:clear \
    && php artisan optimize \
    && php artisan config:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chmod -R 755 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && composer dumpautoload --optimize

# Generate Swagger documentation
RUN php artisan l5-swagger:generate

# Create supervisor directories and log files
RUN mkdir -p /var/log/supervisor /var/run/supervisor \
    && touch /var/log/supervisor/supervisord.log \
    && touch /var/log/supervisor/octane.log \
    && touch /var/log/supervisor/horizon.log

# Copy Supervisor configuration
RUN groupadd -r supervisor || true
RUN usermod -a -G supervisor www-data || true
COPY ./docker/supervisord.conf /etc/supervisor/supervisord.conf

COPY ./docker/resource-monitor.sh /var/www/docker/resource-monitor.sh
RUN chmod +x /var/www/docker/resource-monitor.sh \
    && chown www-data:www-data /var/www/docker/resource-monitor.sh

COPY ./docker/run-scheduler.sh /var/www/docker/run-scheduler.sh
RUN chmod +x /var/www/docker/run-scheduler.sh \
    && chown www-data:www-data /var/www/docker/run-scheduler.sh

# Set proper permissions for www-data user
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Expose port
EXPOSE 8100

# Start supervisord
CMD ["sh", "-c", "/usr/bin/supervisord -c /etc/supervisor/supervisord.conf"]
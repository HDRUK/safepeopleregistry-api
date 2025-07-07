FROM php:8.3.3-fpm

ENV COMPOSER_PROCESS_TIMEOUT=600
ENV REBUILD_DB=1

WORKDIR /var/www

COPY composer.* /var/www/

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
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql soap zip iconv bcmath \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install sockets \
    && docker-php-ext-install exif \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

# Install Supervisor and other dependencies
RUN apt-get update && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /etc/pki/tls/certs && \
    ln -s /etc/ssl/certs/ca-certificates.crt /etc/pki/tls/certs/ca-bundle.crt

# Install Redis
RUN wget -O redis-5.3.7.tgz 'https://pecl.php.net/get/redis-5.3.7.tgz' \
    && pecl install redis-5.3.7.tgz \
    && rm -rf redis-5.3.7.tgz \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Install OpenSwoole
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Send update for php.ini
COPY ./init/php.development.ini /usr/local/etc/php/php.ini

# Copy the application
COPY . /var/www

# Composer & laravel
RUN composer install --optimize-autoloader \
    && npm install --save-dev chokidar \
    # && php artisan octane:install \
    && php artisan storage:link \
    && php artisan optimize:clear \
    && php artisan optimize \
    && php artisan config:clear \
    # && php artisan octane:install --server=swoole \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data storage \
    && composer dumpautoload

# Generate Swagger
RUN php artisan l5-swagger:generate

# Copy Nginx config
# COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
# EXPOSE 8100

# RUN chmod +x /var/www/docker/start.sh

# Copy Supervisor configuration file
COPY ./docker/supervisord.conf /etc/supervisor/supervisord.conf

# Install Supervisor (if not already installed)
RUN mkdir -p /var/log/supervisor && \
    touch /var/log/supervisor/supervisord.log && \
    touch /var/run/supervisord.pid

# Starts both, laravel server and job queue
# CMD ["/var/www/docker/start.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
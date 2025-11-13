FROM php:8.3.3-fpm

ENV COMPOSER_PROCESS_TIMEOUT=600
ENV REBUILD_DB=1

WORKDIR /var/www

COPY composer.* /var/www/

RUN apt-get update && apt-get install -y \
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
    && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql soap zip iconv bcmath \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install sockets \
    && docker-php-ext-install exif \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /etc/pki/tls/certs && \
    ln -s /etc/ssl/certs/ca-certificates.crt /etc/pki/tls/certs/ca-bundle.crt

# Install Redis
# RUN wget -O redis-5.3.7.tgz 'https://pecl.php.net/get/redis-5.3.7.tgz' \
#     && pecl install redis-5.3.7.tgz \
#     && rm -rf redis-5.3.7.tgz \
#     && rm -rf /tmp/pear \
#     && docker-php-ext-enable redis
RUN apt-get update && apt-get install -y \
    autoconf \
    g++ \
    make \
    && pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Send update for php.ini
COPY ./init/php.development.ini /usr/local/etc/php/php.ini

# Copy the application
COPY . /var/www

RUN curl https://frankenphp.dev/install.sh | sh \
    && mv frankenphp /usr/local/bin/frankenphp \
    && chmod +x /usr/local/bin/frankenphp

# Composer & laravel
RUN composer install \
    && php artisan octane:install \
    && php artisan storage:link \
    && php artisan optimize:clear \
    && php artisan optimize \
    && php artisan config:clear \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data storage \
    && composer dumpautoload

# Generate Swagger
RUN php artisan l5-swagger:generate

# Cleanup unwanted files
RUN rm /var/www/public/.htaccess

# Starts both, laravel server and job queue
CMD ["/var/www/docker/start.sh"]

# Expose port
EXPOSE 8100
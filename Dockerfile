FROM php:8.3.3-fpm

ENV COMPOSER_PROCESS_TIMEOUT=600

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
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql soap zip iconv bcmath \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install sockets \
    && docker-php-ext-install exif \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Send update for php.ini
COPY ./init/php.development.ini /usr/local/etc/php/php.ini

# Copy the application
COPY . /var/www

#add a new line to the end of the .env file
# RUN echo "" >> /var/www/.env
# #add in these extra variables to the .env file
# RUN echo "TED_ENABLED=$TED_ENABLED" >> /var/www/.env
# RUN echo "TRASER_ENABLED=$TRASER_ENABLED" >> /var/www/.env
# RUN echo "FMA_ENABLED=$TRASER_ENABLED" >> /var/www/.env


# Composer & laravel
RUN composer install \
    && php artisan storage:link \
    && php artisan optimize:clear \
    && php artisan optimize \
    && php artisan config:clear \
    && php artisan octane:install --server=swoole \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data storage \
    && php artisan octane:install --server=swoole \
    && composer dumpautoload

# Generate Swagger - Removed for now as we don't have
RUN php artisan l5-swagger:generate

# Starts both, laravel server and job queue
CMD ["/var/www/docker/start.sh"]

# Expose port
EXPOSE 8100
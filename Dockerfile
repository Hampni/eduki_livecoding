FROM php:8.1-fpm

RUN apt-get update
RUN apt-get install -y curl zip unzip
RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.* /var/www/html/
RUN composer install --no-scripts
COPY . .
RUN composer dump-autoload

CMD php artisan serve --host=0.0.0.0

FROM php:7.4-apache
RUN a2enmod headers
RUN pecl install redis && docker-php-ext-enable redis
COPY . /var/www/html/

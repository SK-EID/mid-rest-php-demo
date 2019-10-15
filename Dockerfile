FROM php:7.2-apache
RUN a2enmod rewrite
RUN apt-get update && apt-get install -y \
        zip \
        unzip \
        git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
COPY composer.json ./
COPY composer.lock ./
RUN composer install --no-scripts --no-autoloader
COPY . /var/www/html/
WORKDIR /var/www/html
RUN composer dump-autoload --optimize

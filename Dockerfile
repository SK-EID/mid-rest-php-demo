# PHP 8.4.27+ includes the fix for PHP2Shell (CVE-2026-109618 / CVE-2026-109619)
FROM php:8.4.27-apache-trixie@sha256:c6527b06c772a2c3cae648ce58c23a2a12ed2fed41cdfb0619ea64be008cc601

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer

# gmp is needed by sop/asn1 (used by mobile-id-php-client for certificate parsing)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libgmp-dev libicu-dev unzip \
    && docker-php-ext-install gmp intl opcache pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# EGPCS exposes container env vars (APP_ENV, DATABASE_URL, MID_*) to Symfony under mod_php
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "date.timezone = Europe/Tallinn" > "$PHP_INI_DIR/conf.d/app.ini" \
    && echo "variables_order = EGPCS" >> "$PHP_INI_DIR/conf.d/app.ini" \
    && echo "ServerName localhost" > /etc/apache2/conf-enabled/servername.conf

ENV APACHE_DOCUMENT_ROOT=/app/public
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /app

ENV APP_ENV=prod
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY ./ /app
RUN composer dump-autoload --no-dev --classmap-authoritative \
    && composer run-script --no-dev auto-scripts \
    && chown -R www-data:www-data var

COPY run.sh /run.sh
COPY makeAppTables.sh /makeAppTables.sh
RUN chmod +x /run.sh /makeAppTables.sh

EXPOSE 80

CMD ["/run.sh"]

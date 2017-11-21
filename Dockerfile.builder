FROM amazeeiolagoon/develop-php:7.1-cli

COPY composer.json composer.lock /app/
COPY scripts /app/scripts
RUN composer install --no-dev
COPY . /app

FROM uselagoon/php-7.4-cli-drupal:21.9.0

RUN echo $(php -m | grep intl) || true \
    && cat /etc/os-release \
    && docker-php-ext-install intl \
    && echo $(php -m | grep intl) || true

COPY composer.json composer.lock /app/
COPY scripts /app/scripts
RUN composer install --no-dev
COPY . /app

# Define where the Drupal Root is located
ENV WEBROOT=web

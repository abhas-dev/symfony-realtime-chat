FROM php:8.1-apache-bullseye

WORKDIR /var/www/

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Setup context
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

# Quelques library necessaires
RUN apt-get update \
    &&  apt-get install -y --no-install-recommends \
    locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev unzip libgmp-dev

# les locales
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen  \
    &&  echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    &&  locale-gen

# On télécharge et deplace composer
RUN  curl -sS https://getcomposer.org/installer | php -- \
    &&  mv composer.phar /usr/local/bin/composer

# On télécharge et deplace le symfony cli
RUN echo 'deb [trusted=yes] https://repo.symfony.com/apt/ /' | tee /etc/apt/sources.list.d/symfony-cli.list \
    && apt update \
    && apt install symfony-cli \
    && docker-php-ext-configure \
    intl \
    && docker-php-ext-install \
    pdo pdo_mysql opcache intl zip calendar dom mbstring gd xsl gmp \
    \
    && pecl install apcu && docker-php-ext-enable apcu \
    && pecl install xdebug && docker-php-ext-enable xdebug

RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && npm install --global yarn

# On créé un utilisateur avec le même gid/uid que le local
RUN useradd -d /home/dev -m -s /bin/bash dev
USER dev
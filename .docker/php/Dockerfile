FROM dunglas/frankenphp:php8.3

ENV SERVER_NAME=":80"

WORKDIR /app
# tes lagi
COPY . /app

RUN apt update && apt install -y zip libzip-dev unzip zlib1g-dev && \
    docker-php-ext-install zip && \
    docker-php-ext-enable zip


COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer install


# FROM dunglas/frankenphp

# ENV SERVER_NAME=":80"

# COPY php.ini /usr/local/etc/php/
# COPY docker.conf /usr/local/etc/php-fpm.d/docker.conf
# COPY .bashrc /root/

# # Copy the entrypoint script
# COPY entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh

# # mix
# RUN apt-get update \
#   && apt-get install -y build-essential zlib1g-dev default-mysql-client curl gnupg procps vim git unzip libzip-dev libpq-dev \
#   && docker-php-ext-install zip pdo_mysql pdo_pgsql pgsql

# # intl
# RUN apt-get install -y libicu-dev \
#   && docker-php-ext-configure intl \
#   && docker-php-ext-install intl

# # gd
# RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
# docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
# docker-php-ext-install gd

# # redis
# RUN pecl install redis && docker-php-ext-enable redis

# # pcov
# RUN pecl install pcov && docker-php-ext-enable pcov

# # pcntl
# RUN docker-php-ext-install pcntl

# # Xdebug
# # RUN pecl install xdebug \
# # && docker-php-ext-enable xdebug \
# # && echo ";zend_extension=xdebug" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# # Node.js, NPM, Yarn
# RUN curl -sL https://deb.nodesource.com/setup_22.x | bash -
# RUN apt-get install -y nodejs
# RUN npm install npm@latest -g
# RUN npm install yarn -g

# # Composer
# RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
# RUN php composer-setup.php
# RUN php -r "unlink('composer-setup.php');"
# RUN mv composer.phar /usr/local/bin/composer
# RUN docker-php-ext-install exif

# ENV COMPOSER_ALLOW_SUPERUSER 1
# ENV COMPOSER_HOME /composer
# ENV PATH $PATH:/composer/vendor/bin
# RUN composer config --global process-timeout 3600
# RUN composer global require "laravel/installer"
# WORKDIR /root
# RUN git clone https://github.com/seebi/dircolors-solarized
# RUN git config --global --add safe.directory /var/www
# EXPOSE 5173
# WORKDIR /var/www
# COPY . /var/www/
# #entrypoint
# COPY entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh

# ENTRYPOINT ["entrypoint.sh"]
# # CMD ["php-fpm"]
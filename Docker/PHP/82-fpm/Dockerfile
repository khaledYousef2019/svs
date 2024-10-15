FROM php:8.2-fpm
RUN apt-get update
RUN pecl install xdebug
COPY /Docker/PHP/82-fpm/xdebug.ini "${PHP_INI_DIR}/conf.d"
WORKDIR /var/www/html

#RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
#COPY custom.ini $PHP_INI_DIR/conf.d/
#WORKDIR /usr/src/myapp
FROM php:8.1-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y git unzip tzdata dnsutils \
 && apt-get clean

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions opcache pdo_mysql openssl \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && mkdir -p /var/run/php-fpm/ && chmod 777 /var/run/php-fpm/ \
 && sed -ri -e 's!^listen = .*$!listen = /var/run/php-fpm/php.sock!g' /usr/local/etc/php-fpm.d/*.conf \
 && echo "upload_max_filesize = 512M" > $PHP_INI_DIR/conf.d/upload_max_filesize.ini \
 && echo "post_max_size = 512M" > $PHP_INI_DIR/conf.d/post_max_size.ini \
 && curl -sS https://getcomposer.org/installer | php \
 && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install

COPY . .

RUN composer dump-autoload --optimize
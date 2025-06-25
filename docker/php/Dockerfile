FROM php:8.4-fpm

WORKDIR /var/www/html

ARG USER_ID
ARG GROUP_ID

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt update && \
    apt install -y curl gnupg && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt install -yy \
    git \
    nodejs \
    nginx \
    wkhtmltopdf \
    default-jdk \
    ghostscript \
    default-mysql-client \
    neovim \
    p7zip-full \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP Extensions
#      grpc \
#      igbinary \
#      memcached \
#      redis \
#      soap \
#      sockets \
#      ip \
#      rpc \
RUN docker-php-source extract \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && install-php-extensions \
    exif \
    gd \
    imagick \
    intl \
    ldap \
    mongodb \
    opcache \
    pdo_mysql \
    redis \
    xdebug \
    zip \
    pcntl \
    && docker-php-source delete

# Required folders
RUN mkdir -p /init/ && ln -s /usr/bin/php8 /usr/bin/php

# Configuration files
COPY config/php-fpm.conf    /usr/local/etc/php-fpm.d/php-fpm.conf
COPY config/www.conf        /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/USER_ID/1000/" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/GROUP_ID/1000/" /usr/local/etc/php-fpm.d/www.conf
COPY config/zz-docker.conf  /usr/local/etc/php-fpm.d/zz-docker.conf
COPY config/php.ini      $PHP_INI_DIR/php.ini
COPY config/xdebug.ini   $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini

COPY entrypoint.sh          /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN chown -R 1000:1000 /tmp \
    && chmod u+rwX,g+rwX,o+rw -R /tmp \
    && mkdir -p /var/www/php \
    && chown -R 1000:1000 /var/www/php \
    && chmod u+rwX,g+rwX,o+rw -R /var/www/php \
    && chown -R 1000:1000 /usr/local \
    && mkdir -p /.cache \
    && chown -R 1000:1000 /.cache \
    && chmod u+rwX,g+rwX,o+rw -R /.cache

USER 1000:1000

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

########################################################################################################################
####################################################    BASE   #########################################################
########################################################################################################################
FROM php:8.4.11-fpm-alpine3.22 AS base

RUN apk --no-cache add \
    postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql

RUN apk add icu-dev
RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install gd

ARG USER_ID=1000
RUN adduser -u $USER_ID --disabled-password --system --ingroup www-data hostuser

COPY --from=composer:2.8.10 /usr/bin/composer /usr/local/bin/composer
ADD ./conf/php.ini  /usr/local/etc/php/conf.d/php.ini
ADD ./conf/www.conf /usr/local/etc/php-fpm.d/www.conf

# Chromium and ChromeDriver
ENV PANTHER_NO_SANDBOX 1
# Not mandatory, but recommended
ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'
ENV PANTHER_DEVTOOLS=0
RUN apk add --no-cache chromium chromium-chromedriver

RUN apk add --no-cache libzip-dev
RUN docker-php-ext-configure zip
RUN docker-php-ext-install opcache zip

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions sockets

WORKDIR /app

########################################################################################################################
####################################################    PROD   #########################################################
########################################################################################################################
FROM base AS prod

USER hostuser

########################################################################################################################
#####################################################    DEV   #########################################################
########################################################################################################################
FROM base AS dev

RUN apk --no-cache add pcre-dev linux-headers ${PHPIZE_DEPS} && \
    pecl install xdebug-3.4.5 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.max_nesting_level=300" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
ENV PHP_IDE_CONFIG="serverName=php"

USER hostuser


########################################################################################################################
####################################################    CRON   #########################################################
########################################################################################################################
FROM base AS cron

RUN apk update && apk add --no-cache \
    bash \
    busybox-suid \
    dcron

# Add crontab file in the cron directory
ADD crontab /etc/crontabs/root

# Ensure the crontab file has the right permissions
RUN chmod 0644 /etc/crontabs/root

# Add a script to run crond and php-fpm together
COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000 and set the entrypoint
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

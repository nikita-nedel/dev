#!/usr/bin/env bash

if [ -f "/var/www/php/php-fpm.sock" ]; then
  rm "/var/www/php/php-fpm.sock"
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

exec docker-php-entrypoint "$@"
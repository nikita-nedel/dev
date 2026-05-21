#!/bin/sh

# Start the cron daemon
crond -f -l 2 &

# Start PHP-FPM
php-fpm
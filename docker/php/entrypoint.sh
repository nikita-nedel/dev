#!/bin/sh

mkdir -p /app/var/prize_image
chown -R hostuser:www-data /app/var/prize_image
chmod -R 777 /app/var/prize_image

# Start the cron daemon
crond -f -l 2 &

# Start PHP-FPM
php-fpm
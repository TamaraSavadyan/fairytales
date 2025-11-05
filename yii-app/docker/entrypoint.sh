#!/bin/sh

# Создание необходимых директорий
mkdir -p /var/log/supervisor /var/log/nginx /var/run
chmod -R 755 /var/log/supervisor /var/log/nginx /var/run

# Создание директорий для Yii 2
mkdir -p /var/www/html/runtime /var/www/html/web/assets
chmod -R 777 /var/www/html/runtime /var/www/html/web/assets

# Запуск Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf


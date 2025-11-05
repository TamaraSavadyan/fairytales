#!/bin/sh

mkdir -p /var/log/supervisor /var/log/nginx /var/run
chmod -R 755 /var/log/supervisor /var/log/nginx /var/run

mkdir -p /var/www/html/runtime /var/www/html/web/assets
chmod -R 777 /var/www/html/runtime /var/www/html/web/assets

if [ ! -d "/var/www/html/vendor" ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    composer config --no-plugins allow-plugins.yiisoft/yii2-composer true 2>/dev/null || true
    composer install --no-interaction --ignore-platform-req=ext-mcrypt 2>/dev/null || true
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

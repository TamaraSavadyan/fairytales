FROM php:8.1-fpm-alpine

# Установка необходимых расширений PHP для Yii 2
RUN apk add --no-cache \
    curl \
    libzip-dev \
    zip \
    unzip \
    git \
    nginx \
    supervisor

# Установка расширений PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    opcache

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка рабочей директории
WORKDIR /var/www/html

# Создание директорий для runtime и assets
RUN mkdir -p runtime web/assets && \
    chmod -R 777 runtime web/assets

# Копирование composer файлов
COPY composer.json composer.lock* ./

# Установка зависимостей
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Конфигурация PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Конфигурация Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Конфигурация Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint скрипт (копируем до COPY . . чтобы избежать проблем)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && \
    test -f /usr/local/bin/entrypoint.sh && \
    test -x /usr/local/bin/entrypoint.sh

# Копирование приложения
COPY . .

# Установка прав
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/runtime \
    && chmod -R 755 /var/www/html/web/assets

# Создание директорий для логов и pid файлов
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/run && \
    chmod -R 755 /var/log/supervisor /var/log/nginx /var/run

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]


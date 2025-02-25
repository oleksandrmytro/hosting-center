FROM php:8.1-apache

# Обновление списка пакетов и установка зависимостей для PHP-расширений
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql

# Создаем папку для клиентских данных и выдаем права www-data
RUN mkdir -p /var/www/clients && chown -R www-data:www-data /var/www/clients

# (Опционально) Копируем файлы проекта, если не используете volume для ./www
# COPY ./www/ /var/www/html/

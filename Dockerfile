FROM php:8.1-apache

# Оновлення списку пакетів та встановлення залежностей для PHP-розширень
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql

# Включаємо необхідні модулі Apache 
RUN a2enmod vhost_alias rewrite

# Створюємо папки для клієнтських даних та надаємо права www-data
RUN mkdir -p /var/www/clients && chown -R www-data:www-data /var/www/clients
RUN mkdir -p /domains && chmod 777 /domains
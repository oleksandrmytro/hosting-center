FROM php:8.1-apache

# Обновление списка пакетов и установка зависимостей для PHP-расширений и Docker CLI
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg2 \
    lsb-release

# Добавляем ключ и репозиторий Docker для Debian/Ubuntu
RUN curl -fsSL https://download.docker.com/linux/debian/gpg | apt-key add - && \
    echo "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable" > /etc/apt/sources.list.d/docker.list

RUN apt-get update && apt-get install -y docker-ce-cli

# Устанавливаем необходимые PHP-расширения
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql

# Включаем модуль vhost_alias для динамических виртуальных хостов
RUN a2enmod vhost_alias rewrite

# Создаем папки для клиентских данных и настраиваем права
RUN mkdir -p /var/www/clients && chown -R www-data:www-data /var/www/clients
RUN mkdir -p /domains && chmod 777 /domains

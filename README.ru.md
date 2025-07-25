<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/lang-en-red.svg"></a>
  <a href="README.cs.md"><img src="https://img.shields.io/badge/lang-cs-blue.svg"></a>
  <a href="README.uk.md"><img src="https://img.shields.io/badge/lang-ua-green.svg"></a>
  <a href="README.ru.md"><img src="https://img.shields.io/badge/lang-ru-yellow.svg"></a>
</p>

# 🚀 Хостинг-Центр

Комплексное хостинг-решение на базе Docker, включающее сервисы Apache, MariaDB, FTP и SMTP.

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=Apache&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 📋 Содержание
- [Требования](#-требования)
- [Установка](#-установка)
- [Сервисы](#-сервисы)
- [Конфигурация](#-конфигурация)
- [Решение проблем](#-решение-проблем)
- [Лицензия](#-лицензия)

## 🛠 Требования

Перед началом убедитесь, что у вас установлено:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Composer](https://getcomposer.org/)
- [FileZilla](https://filezilla-project.org/) (для тестирования FTP)

## 💻 Установка

1. **Клонируйте репозиторий**
   ```bash
   git clone [repository-url]
   cd hosting-center
   ```

2. **Установите зависимости**
   ```bash
   composer install
   ```

3. **Запустите Docker-контейнеры**
   ```bash
   docker-compose up --build -d
   ```
   Чтобы остановить контейнеры:
   ```bash
   docker-compose down
   ```

4. **Запустите наблюдатель доменов**
   > ⚠️ **Важно**: Запускайте этот скрипт от имени администратора!
   ```bash
   start-domain-watcher.bat
   ```
   Этот скрипт автоматически управляет привязкой доменов к IP-адресам при создании новых доменов.

## 🔧 Сервисы

### 1. Веб-сервер Apache
- **Доступ**: http://localhost
- **Возможности**:
  - Динамическая конфигурация виртуальных хостов
  - Автоматическое создание доменов
  - Поддержка PHP 7.4+

### 2. База данных MariaDB
- **Конфигурация по умолчанию**:
  - Порт: 3306
  - База данных: mojafirma
  - Пользователь: mojafirma_user
  - Пароль: password
  - Пароль root: rootpassword

#### Настройка клиента БД в VS Code:
1. Установите расширение "Database Client"
2. Настройте соединение, используя указанные выше данные
3. Подключитесь и проверьте соединение с базой данных

### 3. FTP-сервер
- **Конфигурация**:
  - Порт: 21
  - Пассивные порты: 30000-30009
  - Директория: ./ftpdata

#### Тестирование с помощью FileZilla:
1. Запустите FileZilla
2. Введите данные, полученные при создании домена
3. Подключитесь, используя порт 21

### 4. SMTP-сервис
- **Доступ к веб-интерфейсу**: http://localhost:8025
- Идеально подходит для тестирования функциональности электронной почты
- Перехватывает все исходящие письма для разработки

## ⚙️ Конфигурация

### Сервисы Docker
Проект использует Docker Compose со следующими сервисами:
- `web`: веб-сервер Apache с PHP
- `db`: база данных MariaDB
- `ftp`: FTP-сервер для управления файлами

### Подключенные тома
- `./www`: корневая директория веб-сервера
- `./vendor`: зависимости Composer
- `./apache/vhost.conf`: конфигурация виртуальных хостов Apache
- `./clients`: директория с данными клиентов
- `./ftpdata`: домашняя директория пользователей FTP

## 🔍 Решение проблем

### Распространенные проблемы:
1. **Конфликты портов**
   - Убедитесь, что порты 80, 21, 3306 и 8025 не используются
   - Остановите конфликтующие сервисы или измените порты в `docker-compose.yml`

2. **Проблемы с правами доступа**
   - Запускайте наблюдатель доменов от имени администратора
   - Проверьте права доступа к подключенным томам

3. **Проблемы с запуском контейнеров**
   ```bash
   # Просмотреть логи контейнеров
   docker-compose logs
   
   # Перезапустить конкретный сервис
   docker-compose restart [service_name]
   ```

## 📝 Лицензия

Этот проект лицензирован по лицензии MIT - смотрите детали в файле [LICENSE](LICENSE).

---
⭐ Считаете этот проект полезным? Пожалуйста, поставьте ему звездочку!

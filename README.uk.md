<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/lang-en-red.svg"></a>
  <a href="README.cs.md"><img src="https://img.shields.io/badge/lang-cs-blue.svg"></a>
  <a href="README.uk.md"><img src="https://img.shields.io/badge/lang-ua-green.svg"></a>
  <a href="README.ru.md"><img src="https://img.shields.io/badge/lang-ru-yellow.svg"></a>
</p>

# 🚀 Хостинг-Центр

Комплексне хостинг-рішення на базі Docker, що включає Apache, MariaDB, FTP та SMTP сервіси.

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=Apache&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 📋 Зміст
- [Вимоги](#-вимоги)
- [Встановлення](#-встановлення)
- [Сервіси](#-сервіси)
- [Конфігурація](#-конфігурація)
- [Вирішення проблем](#-вирішення-проблем)
- [Ліцензія](#-ліцензія)

## 🛠 Вимоги

Перед початком переконайтеся, що у вас встановлено:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Composer](https://getcomposer.org/)
- [FileZilla](https://filezilla-project.org/) (для тестування FTP)

## 💻 Встановлення

1. **Клонуйте репозиторій**
   ```bash
   git clone [repository-url]
   cd hosting-center
   ```

2. **Встановіть залежності**
   ```bash
   composer install
   ```

3. **Запустіть Docker-контейнери**
   ```bash
   docker-compose up --build -d
   ```
   Щоб зупинити контейнери:
   ```bash
   docker-compose down
   ```

4. **Запустіть спостерігач доменів**
   > ⚠️ **Важливо**: Запускайте цей скрипт від імені адміністратора!
   ```bash
   start-domain-watcher.bat
   ```
   Цей скрипт автоматично керує прив'язкою доменів до IP-адрес при створенні нових доменів.

## 🔧 Сервіси

### 1. Веб-сервер Apache
- **Доступ**: http://localhost
- **Можливості**:
  - Динамічна конфігурація віртуальних хостів
  - Автоматичне створення доменів
  - Підтримка PHP 7.4+

### 2. База даних MariaDB
- **Конфігурація за замовчуванням**:
  - Порт: 3306
  - База даних: mojafirma
  - Користувач: mojafirma_user
  - Пароль: password
  - Пароль root: rootpassword

#### Налаштування клієнта БД у VS Code:
1. Встановіть розширення "Database Client"
2. Налаштуйте з'єднання, використовуючи наведені вище дані
3. Підключіться та перевірте з'єднання з базою даних

### 3. FTP-сервер
- **Конфігурація**:
  - Порт: 21
  - Пасивні порти: 30000-30009
  - Директорія: ./ftpdata

#### Тестування за допомогою FileZilla:
1. Запустіть FileZilla
2. Введіть дані, отримані при створенні домену
3. Підключіться, використовуючи порт 21

### 4. SMTP-сервіс
- **Доступ до веб-інтерфейсу**: http://localhost:8025
- Ідеально підходить для тестування функціональності електронної пошти
- Перехоплює всі вихідні листи для розробки

## ⚙️ Конфігурація

### Сервіси Docker
Проєкт використовує Docker Compose з наступними сервісами:
- `web`: веб-сервер Apache з PHP
- `db`: база даних MariaDB
- `ftp`: FTP-сервер для керування файлами

### Підключені томи
- `./www`: коренева директорія веб-сервера
- `./vendor`: залежності Composer
- `./apache/vhost.conf`: конфігурація віртуальних хостів Apache
- `./clients`: директорія з даними клієнтів
- `./ftpdata`: домашня директорія користувачів FTP

## 🔍 Вирішення проблем

### Поширені проблеми:
1. **Конфлікти портів**
   - Переконайтеся, що порти 80, 21, 3306 та 8025 не використовуються
   - Зупиніть конфліктуючі сервіси або змініть порти в `docker-compose.yml`

2. **Проблеми з правами доступу**
   - Запускайте спостерігач доменів від імені адміністратора
   - Перевірте права доступу до підключених томів

3. **Проблеми із запуском контейнерів**
   ```bash
   # Переглянути логи контейнерів
   docker-compose logs
   
   # Перезапустити конкретний сервіс
   docker-compose restart [service_name]
   ```

## 📝 Ліцензія

Цей проєкт ліцензовано за ліцензією MIT - дивіться деталі у файлі [LICENSE](LICENSE).

---
⭐ Вважаєте цей проєкт корисним? Будь ласка, поставте йому зірочку!

<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/language-English-blue.svg?style=for-the-badge"></a>
  <a href="README.uk.md"><img src="https://img.shields.io/badge/language-Українська-yellow.svg?style=for-the-badge"></a>
  <a href="README.ru.md"><img src="https://img.shields.io/badge/language-Русский-red.svg?style=for-the-badge"></a>
</p>

# 🚀 Hosting Center

A comprehensive hosting solution powered by Docker, featuring Apache, MariaDB, FTP, and SMTP services.

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=Apache&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 📋 Table of Contents
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Services](#-services)
- [Configuration](#-configuration)
- [Troubleshooting](#-troubleshooting)
- [License](#-license)

## 🛠 Prerequisites

Before you begin, ensure you have the following installed:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Composer](https://getcomposer.org/)
- [FileZilla](https://filezilla-project.org/) (for FTP testing)

## 💻 Installation

1. **Clone the Repository**
   ```bash
   git clone [repository-url]
   cd hosting-center
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Start Docker Containers**
   ```bash
   docker-compose up --build -d
   ```
   To stop the containers:
   ```bash
   docker-compose down
   ```

4. **Start Domain Watcher**
   > ⚠️ **Important**: Run this script as Administrator!
   ```bash
   start-domain-watcher.bat
   ```
   This script automatically manages domain-to-IP mappings when new domains are created.

## 🔧 Services

### 1. Apache Web Server
- **Access**: http://localhost
- **Features**:
  - Dynamic virtual host configuration
  - Automatic domain provisioning
  - PHP 7.4+ support

### 2. MariaDB Database
- **Default Configuration**:
  - Port: 3306
  - Database: mojafirma
  - User: mojafirma_user
  - Password: password
  - Root Password: rootpassword

#### Setting up Database Client in VS Code:
1. Install "Database Client" extension
2. Configure connection using the credentials above
3. Connect and verify the database connection

### 3. FTP Server
- **Configuration**:
  - Port: 21
  - Passive Ports: 30000-30009
  - Directory: ./ftpdata

#### Testing with FileZilla:
1. Launch FileZilla
2. Enter the credentials provided during domain creation
3. Connect using port 21

### 4. SMTP Service
- **Access Web Interface**: http://localhost:8025
- Perfect for testing email functionality
- Captures all outgoing emails for development

## ⚙️ Configuration

### Docker Services
The project uses Docker Compose with the following services:
- `web`: Apache web server with PHP
- `db`: MariaDB database
- `ftp`: FTP server for file management

### Volume Mappings
- `./www`: Web root directory
- `./vendor`: Composer dependencies
- `./apache/vhost.conf`: Apache virtual hosts configuration
- `./clients`: Client data directory
- `./ftpdata`: FTP user home directory

## 🔍 Troubleshooting

### Common Issues:
1. **Port Conflicts**
   - Ensure ports 80, 21, 3306, and 8025 are not in use
   - Stop conflicting services or modify port mappings in docker-compose.yml

2. **Permission Issues**
   - Run domain watcher as Administrator
   - Check folder permissions for mounted volumes

3. **Container Startup Problems**
   ```bash
   # View container logs
   docker-compose logs
   
   # Restart specific service
   docker-compose restart [service_name]
   ```

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
⭐ Found this project helpful? Please consider giving it a star!
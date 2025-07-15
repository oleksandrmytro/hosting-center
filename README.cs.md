<p align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/lang-en-red.svg"></a>
  <a href="README.cs.md"><img src="https://img.shields.io/badge/lang-cs-blue.svg"></a>
  <a href="README.uk.md"><img src="https://img.shields.io/badge/lang-ua-green.svg"></a>
  <a href="README.ru.md"><img src="https://img.shields.io/badge/lang-ru-yellow.svg"></a>
</p>

# 🚀 Hostingové Centrum

Komplexní hostingové řešení postavené na Dockeru, zahrnující služby Apache, MariaDB, FTP a SMTP.

![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=Apache&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 📋 Obsah
- [Předpoklady](#-předpoklady)
- [Instalace](#-instalace)
- [Služby](#-služby)
- [Konfigurace](#-konfigurace)
- [Řešení problémů](#-řešení-problémů)
- [Licence](#-licence)

## 🛠 Předpoklady

Než začnete, ujistěte se, že máte nainstalováno následující:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Composer](https://getcomposer.org/)
- [FileZilla](https://filezilla-project.org/) (pro testování FTP)

## 💻 Instalace

1. **Klonujte repozitář**
   ```bash
   git clone [repository-url]
   cd hosting-center
   ```

2. **Nainstalujte závislosti**
   ```bash
   composer install
   ```

3. **Spusťte Docker kontejnery**
   ```bash
   docker-compose up --build -d
   ```
   Pro zastavení kontejnerů:
   ```bash
   docker-compose down
   ```

4. **Spusťte sledování domén**
   > ⚠️ **Důležité**: Spusťte tento skript jako správce!
   ```bash
   start-domain-watcher.bat
   ```
   Tento skript automaticky spravuje mapování domén na IP adresy při vytváření nových domén.

## 🔧 Služby

### 1. Webový server Apache
- **Přístup**: http://localhost
- **Funkce**:
  - Dynamická konfigurace virtuálních hostitelů
  - Automatické zřizování domén
  - Podpora PHP 7.4+

### 2. Databáze MariaDB
- **Výchozí konfigurace**:
  - Port: 3306
  - Databáze: mojafirma
  - Uživatel: mojafirma_user
  - Heslo: password
  - Heslo root: rootpassword

#### Nastavení databázového klienta ve VS Code:
1. Nainstalujte rozšíření "Database Client"
2. Nakonfigurujte připojení pomocí výše uvedených údajů
3. Připojte se a ověřte připojení k databázi

### 3. FTP server
- **Konfigurace**:
  - Port: 21
  - Pasivní porty: 30000-30009
  - Adresář: ./ftpdata

#### Testování pomocí FileZilla:
1. Spusťte FileZilla
2. Zadejte přihlašovací údaje získané při vytváření domény
3. Připojte se pomocí portu 21

### 4. SMTP služba
- **Přístup k webovému rozhraní**: http://localhost:8025
- Ideální pro testování funkčnosti e-mailů
- Zachycuje všechny odchozí e-maily pro vývoj

## ⚙️ Konfigurace

### Služby Docker
Projekt používá Docker Compose s následujícími službami:
- `web`: webový server Apache s PHP
- `db`: databáze MariaDB
- `ftp`: FTP server pro správu souborів

### Mapování svazků
- `./www`: Kořenový adresář webu
- `./vendor`: Závislosti Composeru
- `./apache/vhost.conf`: Konfigurace virtuálních hostitelů Apache
- `./clients`: Adresář s klientskými daty
- `./ftpdata`: Domovský adresář uživatelů FTP

## 🔍 Řešení problémů

### Běžné problémy:
1. **Konflikty portů**
   - Ujistěte se, že porty 80, 21, 3306 a 8025 nejsou používány
   - Zastavte konfliktní služby nebo změňte mapování portů v `docker-compose.yml`

2. **Problémy s oprávněními**
   - Spusťte sledování domén jako správce
   - Zkontrolujte oprávnění složek pro připojené svazky

3. **Problémy se spouštěním kontejnerů**
   ```bash
   # Zobrazit logy kontejnerů
   docker-compose logs
   
   # Restartovat konkrétní službu
   docker-compose restart [service_name]
   ```

## 📝 Licence

Tento projekt je licencován pod licencí MIT - viz soubor [LICENSE](LICENSE) pro podrobnosti.

---
⭐ Považujete tento projekt za užitečný? Zvažte, prosím, udělení hvězdičky!

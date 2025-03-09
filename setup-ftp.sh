#!/bin/bash
# filepath: c:\Users\sawam\source\repos\hosting-center\setup-ftp.sh

# Функція для створення користувачів та оновлення бази користувачів
create_ftp_users() {
  echo "Checking for new FTP users..."
  
  # Підключаємось до MySQL та отримуємо список користувачів
  mysql -h db -u mojafirma_user -ppassword mojafirma -e "SELECT ftp_username, ftp_password, domain FROM users" | while read -r username password domain; do
    # Пропускаємо заголовок результату запиту
    if [ "$username" != "ftp_username" ]; then
      # Перевіряємо, чи користувач вже існує
      pure-pw show "$username" > /dev/null 2>&1
      if [ $? -ne 0 ]; then
        echo "Creating FTP user: $username for domain: $domain"
        # Передаємо пароль двічі через перенаправлення вводу
        (echo "$password"; echo "$password") | pure-pw useradd "$username" -u ftpuser -d "/var/www/clients/$domain" -m
      fi
    fi
  done
  
  # Оновлюємо базу даних користувачів
  pure-pw mkdb /etc/pure-ftpd/pureftpd.pdb
  echo "FTP users update completed"
}

# Початкове створення користувачів
create_ftp_users

# Запуск FTP-сервера в фоновому режимі
pure-ftpd -c 50 -C 10 -l puredb:/etc/pure-ftpd/pureftpd.pdb -E -j -R -P localhost -p 30000:30009 &

# Періодична перевірка нових користувачів
while true; do
  sleep 60
  create_ftp_users
done
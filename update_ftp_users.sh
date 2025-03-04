#!/bin/bash

echo "Starting update_ftp_users.sh..."
# Ждем, чтобы база и контейнеры запустились
sleep 10

# Проверяем наличие файла pureftpd.passwd, если его нет – создаем
if [ ! -f /etc/pure-ftpd/pureftpd.passwd ]; then
    echo "pureftpd.passwd not found. Creating..."
    touch /etc/pure-ftpd/pureftpd.passwd
    chmod 600 /etc/pure-ftpd/pureftpd.passwd
fi

# Получаем uid и gid базового пользователя FTP
uid=$(id -u ftpuser)
gid=$(id -g ftpuser)

# Получаем список FTP-учетных записей из базы данных
ftp_accounts=$(mysql -h db -u root -p'rootpassword' -D mojafirma -Bse "SELECT ftp_username, ftp_password, domain FROM users;")

if [ -n "$ftp_accounts" ]; then
  while IFS=$'\t' read -r ftp_username ftp_password domain; do
    home_dir="/var/www/clients/$domain"
    
    # Проверяем, существует ли запись для данного пользователя
    if ! grep -q "^$ftp_username:" /etc/pure-ftpd/pureftpd.passwd; then
      echo "Creating FTP user $ftp_username for domain $domain..."
      # Генерируем зашифрованный пароль с помощью Perl (MD5-crypt с солью "xx")
      encrypted_pass=$(perl -le 'print crypt($ARGV[0], "\$1\$xx\$")' "$ftp_password")
      # Формируем запись для файла pureftpd.passwd с корректными uid/gid
      echo "$ftp_username:$encrypted_pass:$uid:$gid::${home_dir}:/bin/false" >> /etc/pure-ftpd/pureftpd.passwd
    else
      echo "FTP user $ftp_username already exists in pureftpd.passwd."
    fi
  done <<< "$ftp_accounts"
  
  echo "Updating pure-ftpd database..."
  pure-pw mkdb /etc/pure-ftpd/pureftpd.pdb -f /etc/pure-ftpd/pureftpd.passwd
  
  # Смена владельца файлов базы на ftpuser:ftpgroup
  chown ftpuser:ftpgroup /etc/pure-ftpd/pureftpd.passwd
  chown ftpuser:ftpgroup /etc/pure-ftpd/pureftpd.pdb
  
  echo "FTP users update complete."
else
  echo "No FTP accounts found in database."
fi

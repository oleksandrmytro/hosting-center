Как запустить сервер на докере гайд для жестких программистов:

1) Запускаем Docker

2) В VScode клонируем проект, окрываем новый терминал

3) Качаем композер

        composer install

4) Запусуаем контейнеры (-d запускает в фоновом режиме чтобы вы еще могли консолью пользоваться)

        docker-compose up --build -d

    Чтобы остановить 

        docker-compose down

5) Запускаем файл ОТ ИМЕНИ АДМИНИСТРАТОРА!

        start-domain-watcher.bat

    Этот скрипт автоматически добавляет домены к ip при их создании

6) Чтобы затестить работает ли все

    Apache:

        - Заходим в браузере на http://localhost
        - Совершаем заказ
        - Заходим по домену который создали (или по ip)
        - Радуемся что все работает

    MariaDB:

        - Качаем Extention на VScode "Database client"
        - Там вводим всю инофрмацию которую мы получили при создании домена
        - Радуемся что все работает х2
    
    FTP:

        - Качаем и запускаем Filezilla
        - Заполняем всю инофрмацию которую мы получили при создании домена (порт 21)
        - Радуемся что все работает х3

    SMTP:

        - В браузере заходим на 

            http://localhost:8025

        - Тут можно проверить отправку писем
        - Радуемся что все работает х4
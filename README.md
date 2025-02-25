Как запустить сервер на докере гайд для жестких программистов:

1) Скачайте и запустите Docker

2) В cmd зайдите в папку с проектом 

        cd *путь к папке*

    или если есть git bash можете в папке нажать ПКМ и там будет открыть git bash в этой папке

3) Запустите контейнеры 

        docker-compose build
        docker-compose up -d

    (-d запускает в фоновом режиме чтобы вы еще могли консолью пользоваться)

    Чтобы остановить 

        docker-compose down

4) Чтобы затестить работает ли все

    Apache:

        Заходим в браузере на http://localhost
        Я уже накидал там немного фронта, увидите форму с кнопкой
        Потом когда хосты настроим можно будет по домену заходить

    MariaDB:

        В терминале прописать 
            docker exec -it hosting-center-db mysql -u mojafirma_user -p
        Веести пароль password
        И прописать 
            SHOW DATABASES;
        Оно покажет базы которые есть, чтобы выйти \q

        Чтобы изменять таблицу
            USE mojafirma;
    
    FTP:

        Скачайте Filezilla
            Хост: localhost
            Порт: 21
            Логин: ftpuser
            Пароль: ftppassword
        Фтп синхронизировано с папкой ftpdata 

    SMTP:

        Проверить можно через веб-интервейс
        В браузере заходите на 
            http://localhost:8025
        Тут можно проверить отправку писем
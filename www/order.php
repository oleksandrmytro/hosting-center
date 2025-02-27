<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/php_errors.log');

header('Content-Type: application/json');

// Функция для генерации случайной строки заданной длины
function generateRandomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $length > $i; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Получаем входные данные
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(["success" => false, "error" => "Invalid JSON input"]);
    exit;
}

$email = trim($data['email'] ?? '');
$domain = trim($data['domain'] ?? '');

// Базовая валидация
if (empty($email) || empty($domain)) {
    echo json_encode(["success" => false, "error" => "Email and domain are required"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Invalid email format"]);
    exit;
}
if (strpos($domain, '.') === false) {
    echo json_encode(["success" => false, "error" => "Invalid domain format"]);
    exit;
}

// Логіка для IP-адресації
// Використовуємо фіксований діапазон 127.0.0.X, починаючи з 2
// Знаходимо перше вільне IP шляхом перевірки бази даних
function getNextAvailableIp($pdo) {
    // Отримуємо всі вже використані IP з бази
    $stmt = $pdo->query("SELECT allocated_ip FROM users WHERE allocated_ip LIKE '127.0.0.%'");
    $usedIps = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Шукаємо перше вільне IP
    for ($i = 2; $i < 255; $i++) {
        $ip = "127.0.0.$i";
        if (!in_array($ip, $usedIps)) {
            return $ip;
        }
    }
    
    // Якщо всі зайняті - повторно використовуємо останній
    return "127.0.0.254";
}

// Подключение к базе данных (параметры берутся из docker-compose)
$host = 'db';
$dbname = 'mojafirma';
$db_user = 'root';
$db_pass = 'rootpassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Отримуємо наступний вільний IP
    $allocated_ip = getNextAvailableIp($pdo);

    // Генерация учетных данных для FTP и базы данных
    $ftp_username = "ftp_" . generateRandomString(5);
    $ftp_password = generateRandomString(8);
    $db_username  = "db_" . generateRandomString(5);
    $db_password  = generateRandomString(8);

    // Вставляем нового пользователя с данными для FTP и базы данных
    $stmt = $pdo->prepare("INSERT INTO users (email, domain, allocated_ip, ftp_username, ftp_password, db_username, db_password) 
                           VALUES (:email, :domain, :allocated_ip, :ftp_username, :ftp_password, :db_username, :db_password)");
    // Создаем пользователя базы данных
    $pdo->exec("CREATE USER '$db_username'@'%' IDENTIFIED BY '$db_password'");
    $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO '$db_username'@'%' WITH GRANT OPTION");

    $stmt->execute([
        ':email'         => $email,
        ':domain'        => $domain,
        ':allocated_ip'  => $allocated_ip,  // Тепер це 127.0.0.X
        ':ftp_username'  => $ftp_username,
        ':ftp_password'  => $ftp_password,
        ':db_username'   => $db_username,
        ':db_password'   => $db_password
    ]);

    // Создаем FTP-пользователя в контейнере pure-ftpd.
    // Важно: убедитесь, что в FTP-контейнере существует базовый пользователь (например, ftpuser) или настройте команду под вашу конфигурацию.
    $ftpCmd = "docker exec hosting-center-ftp pure-pw useradd {$ftp_username} -u ftpuser -d /var/www/clients/{$domain} -m";
    exec($ftpCmd, $output1, $ret1);
    // Обновляем базу данных FTP (пересобираем виртуальную базу)
    $ftpCmd2 = "docker exec hosting-center-ftp pure-pw mkdb /etc/pure-ftpd/pureftpd.pdb";
    exec($ftpCmd2, $output2, $ret2);


    // Создание отдельной папки для клиента
    $clientFolder = "/var/www/clients/" . $domain;
    if (!file_exists($clientFolder)) {
        if (!mkdir($clientFolder, 0755, true)) {
            // Если создать папку не удалось, можно залогировать ошибку
            error_log("Failed to create client folder: " . $clientFolder);
        }
    }
    
    // Створюємо додаткову папку для доступу за IP
    $ipFolder = "/var/www/clients/" . $allocated_ip;
    if (!file_exists($ipFolder)) {
        if (!symlink($clientFolder, $ipFolder)) {
            error_log("Failed to create symlink from IP to client folder: $ipFolder -> $clientFolder");
        }
    }
    
    // Створюємо унікальну сторінку для домену з випадковим кольором
    $colors = ['#4CAF50', '#2196F3', '#f44336', '#FF9800', '#9C27B0', '#3F51B5'];
    $randomColor = $colors[array_rand($colors)];
    
    // Генеруємо випадковий фон
    $bgPattern = rand(1, 5);
    
    $indexHtml = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>$domain</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            background-image: url('https://www.transparenttextures.com/patterns/diamond-upholstery.png');
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        h1 {
            color: $randomColor;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 5px solid $randomColor;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
        }
        .ip {
            font-family: monospace;
            background-color: #f1f1f1;
            padding: 3px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"header\">
            <h1>Welcome to $domain!</h1>
            <p>Your website is ready to be customized</p>
        </div>
        
        <div class=\"info-box\">
            <p>This is your new website hosted on IP: <span class=\"ip\">$allocated_ip</span></p>
            <p>Site created at: " . date('Y-m-d H:i:s') . "</p>
        </div>
        
        <p>You can now start building your website by uploading your files via FTP or by using our control panel.</p>
        <p>If you need any assistance, don't hesitate to contact our support team.</p>
        
        <div class=\"footer\">
            <p>&copy; " . date('Y') . " $domain | Powered by Hosting Center</p>
        </div>
    </div>
</body>
</html>";

    // Створюємо індексний файл для клієнта
    file_put_contents($clientFolder . "/index.html", $indexHtml);

    // Формируем контент письма с данными для подключения
    $emailContent = "Hello!\n\n"
    . "Your order has been processed. Below are the details for connecting to your resources:\n\n"
    . "Email: $email\n"
    . "Domain: $domain\n"
    . "Allocated IP: $allocated_ip\n\n"
    . "FTP Access:\n"
    . "    Server: ftp.$domain\n"
    . "    Username: $ftp_username\n"
    . "    Password: $ftp_password\n\n"
    . "Database Access:\n"
    . "    Server: db.$domain\n"
    . "    Username: $db_username\n"
    . "    Password: $db_password\n\n"
    . "Your client folder is created at: " . $clientFolder . "\n\n"
    . "Thank you for using our hosting center!";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp';  // имя сервиса SMTP из docker-compose
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->setFrom('no-reply@hostingcenter.com', 'Hosting Center');
        $mail->addAddress($email);
        $mail->Subject = 'Your Hosting Account Details';
        $mail->Body    = $emailContent;
        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }


    // Записываем письмо в лог-файл для тестирования (в дальнейшем можно интегрировать отправку email через SMTP)
    file_put_contents("email_log.txt", $emailContent . "\n\n", FILE_APPEND);

    // Write domain to the shared domains file for host processing
    $domainData = [
        'domain' => $domain,
        'ip' => $allocated_ip,
        'timestamp' => time()
    ];
    
    file_put_contents("/domains/pending_domains.json", 
        json_encode($domainData) . "\n", 
        FILE_APPEND);
    
    chmod("/domains/pending_domains.json", 0666); // Ensure it's writable by host script

    echo json_encode([
        "success" => true,
        "message" => "Order processed successfully. Domain will be available shortly.",
        "allocated_ip" => $allocated_ip,
        "ftp_username" => $ftp_username,
        "ftp_password" => $ftp_password,
        "db_username" => $db_username,
        "db_password" => $db_password,
        "client_folder" => $clientFolder
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
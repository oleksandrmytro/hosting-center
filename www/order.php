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

// Подключение к базе данных (параметры берутся из docker-compose)
$host = 'db';
$dbname = 'mojafirma';
$db_user = 'root';
$db_pass = 'rootpassword';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Фіксоване IP для всіх доменів
    $allocated_ip = '127.0.0.1';

    // Генерация учетных данных для FTP и базы данных
    $ftp_username = "ftp_" . generateRandomString(5);
    $ftp_password = generateRandomString(8);
    $db_username  = "db_" . generateRandomString(5);
    $db_password  = generateRandomString(8);
    $db_name      = "db_" . generateRandomString(5);  // Генерируем уникальное имя базы данных

    // Определяем домашний каталог для клиента
    $clientFolder = "/var/www/clients/" . $domain;
    $home_dir = $clientFolder;  // Используем как домашний каталог

    // Перевірка, чи домен вже зайнятий
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE domain = :domain");
    $stmt->execute([':domain' => $domain]);
    if ($stmt->fetchColumn() > 0) {
        die(json_encode(['success' => false, 'error' => 'This domain is already taken.']));
    }

    // Вставляем нового пользователя с данными для FTP и базы данных
    $stmt = $pdo->prepare("INSERT INTO users (email, domain, allocated_ip, ftp_username, ftp_password, home_dir, db_username, db_password, db_name) 
                        VALUES (:email, :domain, :allocated_ip, :ftp_username, :ftp_password, :home_dir, :db_username, :db_password, :db_name)");

    // Создаем базу данных для клиента
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");

    // Создаем пользователя базы данных и назначаем ему права только на эту базу
    $pdo->exec("CREATE USER '$db_username'@'%' IDENTIFIED BY '$db_password'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `$db_name`.* TO '$db_username'@'%'");
    $pdo->exec("FLUSH PRIVILEGES");

    // Выполняем вставку записи о клиенте в таблицу
    $stmt->execute([
        ':email'         => $email,
        ':domain'        => $domain,
        ':allocated_ip'  => $allocated_ip,
        ':ftp_username'  => $ftp_username,
        ':ftp_password'  => $ftp_password,
        ':home_dir'      => $home_dir,
        ':db_username'   => $db_username,
        ':db_password'   => $db_password,
        ':db_name'       => $db_name
    ]);

    // Создание отдельной папки для клиента
    $clientFolder = "/var/www/clients/" . $domain;
    if (!file_exists($clientFolder)) {
        if (!mkdir($clientFolder, 0755, true)) {
            // Если создать папку не удалось, можно залогировать ошибку
            error_log("Failed to create client folder: " . $clientFolder);
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
            <p>Thank you for using our services!</p>
        </div>
        
        <p>This is your new website hosted on IP: <span class=\"ip\">$allocated_ip</span></p>
        <div class=\"info-box\">
            <p>Site created at: <span class=\"ip\">" . date('Y-m-d H:i:s') . "</span></p>
            <p>Email: <span class=\"ip\">$email</span></p>
            <p><b>FTP Access:</b></p>
            <p>Username: <span class=\"ip\">$ftp_username</span></p>
            <p>Password: <span class=\"ip\">$ftp_password</span></p>
            <p><b>Database Access:</b></p>
            <p>Database name: <span class=\"ip\">$db_name</span></p>
            <p>Username: <span class=\"ip\">$db_username</span></p>
            <p>Password: <span class=\"ip\">$db_password</span></p>
        </div>
        <p>Your client folder is created at: <span class=\"ip\">$clientFolder</span></p>
        
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
    . "    Username: $ftp_username\n"
    . "    Password: $ftp_password\n\n"
    . "Database Access:\n"
    . "    Database name: $db_name\n"
    . "    Username: $db_username\n"
    . "    Password: $db_password\n\n"
    . "Your client folder is created at: " . $clientFolder . "\n\n"
    . "Thank you for using our hosting center!";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp';
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

    // Записываем письмо в лог-файл
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
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Функция для генерации случайной строки заданной длины
function generateRandomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
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

// Симуляция выделения IP
$allocated_ip = "192.168.100." . rand(2, 254);

// Генерация учетных данных для FTP и базы данных
$ftp_username = "ftp_" . generateRandomString(5);
$ftp_password = generateRandomString(8);
$db_username  = "db_" . generateRandomString(5);
$db_password  = generateRandomString(8);

// Подключение к базе данных (параметры берутся из docker-compose)
$host = 'db';
$dbname = 'mojafirma';
$db_user = 'mojafirma_user';
$db_pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Вставляем нового пользователя с данными для FTP и базы данных
    $stmt = $pdo->prepare("INSERT INTO users (email, domain, allocated_ip, ftp_username, ftp_password, db_username, db_password) 
                           VALUES (:email, :domain, :allocated_ip, :ftp_username, :ftp_password, :db_username, :db_password)");
    $stmt->execute([
        ':email'         => $email,
        ':domain'        => $domain,
        ':allocated_ip'  => $allocated_ip,
        ':ftp_username'  => $ftp_username,
        ':ftp_password'  => $ftp_password,
        ':db_username'   => $db_username,
        ':db_password'   => $db_password
    ]);

    // Создание отдельной папки для клиента
    $clientFolder = "/var/www/clients/" . $domain;
    if (!file_exists($clientFolder)) {
        if (!mkdir($clientFolder, 0755, true)) {
            // Если создать папку не удалось, можно залогировать ошибку
            error_log("Failed to create client folder: " . $clientFolder);
        }
    }

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


    // Записываем письмо в лог-файл для тестирования (в дальнейшем можно интегрировать отправку email через SMTP)
    file_put_contents("email_log.txt", $emailContent . "\n\n", FILE_APPEND);

    echo json_encode([
        "success" => true,
        "message" => "Order processed successfully.",
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

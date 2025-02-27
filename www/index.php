<?php
// Налаштування помилок
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/php_errors.log');

// Отримуємо домен з HTTP_HOST
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Якщо домен не localhost і не 127.0.0.1, перевіряємо чи він існує у клієнтських сайтах
if ($host !== 'localhost' && $host !== '127.0.0.1') {
    // Перевіряємо чи існує папка з доменом
    $clientFolder = "/var/www/clients/{$host}";
    if (file_exists($clientFolder) && file_exists($clientFolder . "/index.html")) {
        // Перенаправляємо на сайт клієнта
        include "{$clientFolder}/index.html";
        exit;
    }
    
    // Для IP-адрес з формату 127.0.0.X перевіряємо в базі даних
    if (preg_match('/^127\.0\.0\.[2-9][0-9]*$/', $host)) {
        // Підключення до БД
        $db_host = 'db';
        $db_name = 'mojafirma';
        $db_user = 'mojafirma_user';
        $db_pass = 'password';
        
        try {
            $pdo = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Шукаємо домен за IP
            $stmt = $pdo->prepare("SELECT domain FROM users WHERE allocated_ip = :ip");
            $stmt->execute([':ip' => $host]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['domain']) {
                $domainFolder = "/var/www/clients/{$result['domain']}";
                if (file_exists($domainFolder) && file_exists($domainFolder . "/index.html")) {
                    include "{$domainFolder}/index.html";
                    exit;
                }
            }
        } catch (PDOException $e) {
            // Просто логуємо помилку
            error_log("Database error when checking IP: " . $e->getMessage());
        }
    }
}

// Якщо код дійшов сюди, значить домен не знайдено або це localhost - показуємо панель керування
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Hosting Center</title>
</head>
<body>
  <div class="container">
    <h1>Welcome!</h1>
    <input type="text" name="email" id="email" placeholder="Enter your email">
    <input type="text" name="domen" id="domen" placeholder="Choose your domain">
    <button id="submitBtn">Submit</button>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const emailInput = document.getElementById("email");
      const domainInput = document.getElementById("domen");
      const submitBtn = document.getElementById("submitBtn");

      submitBtn.addEventListener("click", function (e) {
        e.preventDefault();

        const email = emailInput.value.trim();
        const domain = domainInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let errors = [];

        if (!email) {
          errors.push("Email field is required.");
        } else if (!emailRegex.test(email)) {
          errors.push("Please enter a valid email address.");
        }

        if (!domain) {
          errors.push("Domain field is required.");
        } else if (!domain.includes(".")) {
          errors.push("Please enter a valid domain (must contain a dot).");
        }

        if (errors.length > 0) {
          alert(errors.join("\n"));
        } else {
          // Отправка данных через AJAX на order.php
          fetch('order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, domain: domain })
          })
          .then(response => response.text())
          .then(text => {
            console.log('Raw response:', text);
            try {
              const data = JSON.parse(text);
              if (data.success) {
                alert('Order successful! Allocated IP: ' + data.allocated_ip);
              } else {
                alert('Order failed: ' + data.error);
              }
            } catch (e) {
              alert('Invalid JSON response');
              console.error(e);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert("An error occurred while processing your order.");
          });
        }
      });
    });
  </script>
</body>
</html>
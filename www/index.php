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

        // Получаем значения полей и удаляем лишние пробелы
        const email = emailInput.value.trim();
        const domain = domainInput.value.trim();

        // Регулярное выражение для проверки email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let errors = [];

        // Валидация email
        if (!email) {
          errors.push("Email field is required.");
        } else if (!emailRegex.test(email)) {
          errors.push("Please enter a valid email address.");
        }

        // Валидация домена
        if (!domain) {
          errors.push("Domain field is required.");
        } else if (!domain.includes(".")) {
          errors.push("Please enter a valid domain (must contain a dot).");
        }

        if (errors.length > 0) {
          // Выводим ошибки (можно заменить alert на вывод в документ)
          alert(errors.join("\n"));
        } else {
          // Если валидация пройдена, можно продолжать обработку формы
          alert("Validation successful! Form data is ready to be sent.");
          // Здесь можно, например, отправить данные через AJAX или выполнить другую логику.
        }
      });
    });
  </script>
</body>
</html>
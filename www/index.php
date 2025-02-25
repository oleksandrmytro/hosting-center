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

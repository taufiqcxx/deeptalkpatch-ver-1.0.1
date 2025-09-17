<?php
// forgot.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .logo {
      display: block;
      margin: 0 auto 8px; /* rapat dengan teks */
      max-width: 100px;   /* ukuran logo */
    }
    .card-header h2 {
      margin: 0;
      font-size: 22px;    /* lebih kecil agar seragam */
    }
    .card-header {
      padding: 20px;      /* supaya box tidak terlalu panjang */
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="card-header">
      <img src="assets/logo.png" alt="Logo" class="logo">
      <h2>Forgot Password</h2>
    </div>
    <form action="process_forgot.php" method="POST">
      <div class="input-group">
        <i class="fa fa-envelope"></i>
        <input type="email" name="email" placeholder="Enter your registered email" required>
      </div>
      <button type="submit" class="btn">Reset Password</button>
      <a href="login.php" class="forgot-link">Back to Login</a>
    </form>
  </div>
</body>
</html>

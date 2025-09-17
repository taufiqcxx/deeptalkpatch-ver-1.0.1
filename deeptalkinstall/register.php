<?php
// register.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .logo {
      display: block;
      margin: 0 auto 8px; /* rapat dengan tulisan */
      max-width: 100px;   /* ukuran logo */
    }
    .card-header h2 {
      margin: 0;
      font-size: 22px;    /* sedikit lebih kecil */
    }
    .card-header {
      padding: 20px;      /* lebih kecil agar box tidak terlalu panjang */
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="card-header">
      <img src="assets/logo.png" alt="Logo" class="logo">
      <h2>Register</h2>
    </div>
    <form action="process_register.php" method="POST">
      <div class="input-group">
        <i class="fa fa-user"></i>
        <input type="text" name="nama" placeholder="Full Name" required>
      </div>
      <div class="input-group">
        <i class="fa fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" required>
      </div>
      <div class="input-group">
        <i class="fa fa-phone"></i>
        <input type="text" name="phone" placeholder="Phone Number" required>
      </div>
      <div class="input-group">
        <i class="fa fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="btn">Register</button>
      <a href="login.php" class="forgot-link">Sudah punya akun terdaftar? Login</a>
    </form>
  </div>
</body>
</html>

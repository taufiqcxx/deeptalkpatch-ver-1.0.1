<?php
// appeal.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Appeal</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .login-card {
      width: 360px;
      background: #fff;
      border-radius: 16px;
      text-align: center;
      box-shadow: 0px 8px 25px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .login-card .card-header {
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      padding: 20px 15px;
      color: #fff;
    }
    .login-card .card-header img {
      width: 60px;
      display: block;
      margin: 0 auto 8px auto;
    }
    .login-card .card-header h2 {
      margin: 0;
      font-size: 20px;
    }
    .login-card form {
      padding: 20px;
    }
    textarea {
      font-family: inherit;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="card-header">
      <img src="assets/logo.png" alt="Deeptalk Logo">
      <h2>Account Appeal</h2>
    </div>
    <form action="process_appeal.php" method="POST">
      <div class="input-group">
        <i class="fa fa-envelope"></i>
        <input type="email" name="email" placeholder="Your banned email" required>
      </div>
      <div class="input-group" style="flex-direction: column; align-items: flex-start;">
        <i class="fa fa-comment" style="margin-bottom:6px; color:#0474BA;"></i>
        <textarea name="reason" placeholder="Jelaskan mengapa kami perlu membuka akses untuk anda"
                  style="width:100%; padding:12px; border:1px solid #E8E8E8; border-radius:10px; resize:vertical;"
                  rows="4" required></textarea>
      </div>
      <button type="submit" class="btn">Submit Appeal</button>
      <a href="index.php" class="forgot-link">Kembali Ke Halaman Utama</a>
    </form>
  </div>
</body>
</html>

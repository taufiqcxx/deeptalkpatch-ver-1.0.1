<?php
session_start();
include 'koneksi.php';

// Simpan log sebelum destroy session
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $activity = "Logout berhasil";
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $activity);
    $stmt->execute();
}

// Hapus session
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Logout - Deeptalk</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #00A7E1, #0474BA);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .logout-card {
      width: 360px;
      background: #ffffff;
      border-radius: 16px;
      text-align: center;
      box-shadow: 0px 8px 25px rgba(0,0,0,0.15);
      overflow: hidden;
      animation: fadeIn 0.5s ease-in-out;
    }
    .logout-card .card-header {
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      padding: 20px 15px;
      color: #fff;
    }
    .logout-card .card-header img {
      width: 60px;
      display: block;
      margin: 0 auto 8px auto;
    }
    .logout-card .card-header h2 {
      margin: 0;
      font-size: 20px;
    }
    .logout-card .card-body {
      padding: 25px 20px;
    }
    .logout-card .card-body p {
      font-size: 13px;
      color: #555;
      margin-bottom: 18px;
      line-height: 1.4;
    }
    .btn {
      display: inline-block;
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
      margin-bottom: 8px;
    }
    .btn-login { background: #F77D0E; color: #fff; }
    .btn-login:hover { background: #F9AA50; }
    .btn-home { background: #0474BA; color: #fff; }
    .btn-home:hover { background: #00A7E1; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="logout-card">
    <div class="card-header">
      <img src="assets/logo.png" alt="Deeptalk Logo">
      <h2>Anda Telah Logout</h2>
    </div>
    <div class="card-body">
      <p>Terima kasih sudah menggunakan Deeptalk.<br>Silakan login kembali atau kembali ke halaman utama.</p>
      <a href="login.php" class="btn btn-login">Kembali ke Login</a>
      <a href="index.php" class="btn btn-home">Ke Halaman Utama</a>
    </div>
  </div>
</body>
</html>

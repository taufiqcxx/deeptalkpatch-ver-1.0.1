<?php
session_start();
include 'koneksi.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['status_register'] === 'banned') {
            $error = "Akun Anda diblokir.";
        } elseif (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] === 'admin') {
                header("Location: admin/index.php");
            } elseif ($row['role'] === 'konselor') {
                header("Location: konselor/index.php");
            } else {
                header("Location: client/index.php");
            }
            exit;
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Email tidak terdaftar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .logo {
      display: block;
      margin: 0 auto 8px; /* lebih rapat dengan tulisan */
      max-width: 100px;   /* sedikit lebih kecil agar box tidak kepanjangan */
    }
    .card-header h2 {
      margin: 0;          /* hapus jarak default */
      font-size: 22px;    /* bisa diperkecil sedikit kalau mau */
    }
    .card-header {
      padding: 20px;      /* lebih kecil dari sebelumnya */
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="card-header">
      <img src="assets/logo.png" alt="Logo" class="logo">
      <h2>Login</h2>
    </div>
    <p class="divider">Masukkan Email dan Password</p>

    <?php if ($error != "") { ?>
      <p style="color: red; font-size:14px;"><?= $error; ?></p>
    <?php } ?>

    <form method="POST">
      <div class="input-group">
        <i class="fa fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" required>
      </div>
      <div class="input-group">
        <i class="fa fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="btn">Login</button>
      <button type="button" class="btn btn-register" onclick="window.location.href='register.php'">Register</button>
      <a href="forgot.php" class="forgot-link">Lupa password?</a>
    </form>
  </div>
</body>
</html>

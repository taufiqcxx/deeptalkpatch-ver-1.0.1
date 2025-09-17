<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbhost  = $_POST['dbhost'] ?? 'localhost';
    $dbuser  = $_POST['dbuser'] ?? 'root';
    $dbpass  = $_POST['dbpass'] ?? '';
    $dbname  = !empty($_POST['dbname']) ? $_POST['dbname'] : "deeptalk";

    // Koneksi awal
    $conn = new mysqli($dbhost, $dbuser, $dbpass);
    if ($conn->connect_error) {
        die("<p style='color:red;text-align:center;'>Koneksi gagal: " . $conn->connect_error . "</p>");
    }

    // Buat DB
    $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $conn->select_db($dbname);

    // Import struktur SQL
    $sql_file = __DIR__ . '/deeptalk.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        $queries = explode(";", $sql);
        foreach ($queries as $query) {
            $q = trim($query);
            if (!empty($q)) @$conn->query($q);
        }
    }

    // Admin default
    $cek_admin = $conn->query("SELECT * FROM user WHERE email='admin@deeptalk.id'");
    if ($cek_admin->num_rows == 0) {
        $password = password_hash("12345", PASSWORD_BCRYPT);
        $conn->query("INSERT INTO user (nama,email,password,role,status_register) 
                      VALUES ('Administrator','admin@deeptalk.id','$password','admin','approve')");
    }

    // Simpan koneksi.php
    $config = "<?php
\$host = '$dbhost';
\$user = '$dbuser';
\$pass = '$dbpass';
\$db   = '$dbname';
\$conn = new mysqli(\$host, \$user, \$pass, \$db);
if (\$conn->connect_error) { die('Koneksi gagal: ' . \$conn->connect_error); }
?>";
    file_put_contents(__DIR__ . '/../koneksi.php', $config);

    // Tampilkan halaman selesai
    $done = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Setup Wizard - Deeptalk</title>
  <style>
    body {
      margin: 0; display: flex; justify-content: center; align-items: center;
      height: 100vh; font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #00A7E1, #0474BA);
    }
    .card {
      width: 360px; background: #fff; border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      overflow: hidden; text-align: center;
    }
    .card-header {
      background: linear-gradient(to right, #00A7E1, #0474BA);
      padding: 20px; color: #fff;
    }
    .card-header img { width: 60px; margin-bottom: 8px; }
    .card-header h2 { margin: 0; font-size: 20px; }
    form, .card-body { padding: 20px; text-align: left; }
    .form-group { margin-bottom: 15px; }
    label { font-size: 13px; color: #333; }
    input {
      width: 100%; padding: 10px;
      border:1px solid #ccc; border-radius: 8px;
      margin-top: 5px; font-size: 14px;
    }
    button {
      width: 100%; padding: 12px;
      background: #f97316; border: none; margin-top: 10px;
      color: white; font-size: 15px;
      border-radius: 8px; cursor: pointer;
    }
    button:hover { background: #d46212; }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      <img src="../assets/logo.png" alt="Logo">
      <h2><?= isset($done) ? "Instalasi Selesai" : "Setup Database" ?></h2>
    </div>

    <?php if (!isset($done)): ?>
    <!-- Form instalasi -->
    <form method="post">
      <div class="form-group">
        <label>Host Database</label>
        <input type="text" name="dbhost" value="localhost" required>
      </div>
      <div class="form-group">
        <label>User Database</label>
        <input type="text" name="dbuser" value="root" required>
      </div>
      <div class="form-group">
        <label>Password Database</label>
        <input type="password" name="dbpass">
      </div>
      <div class="form-group">
        <label>Nama Database</label>
        <input type="text" name="dbname" value="deeptalk" required>
      </div>
      <button type="submit">Lanjutkan</button>
    </form>

    <?php else: ?>
    <!-- Halaman sukses -->
    <div class="card-body" style="text-align:center;">
      <p>Instalasi berhasil!<br>
      Login dengan <b>admin@deeptalk.id</b> / <b>12345</b></p>
      <form method="post" action="remove_install.php">
        <button type="submit">Selesai</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>

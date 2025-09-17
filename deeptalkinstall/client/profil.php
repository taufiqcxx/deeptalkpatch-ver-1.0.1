<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['id'];
$user = $conn->query("SELECT * FROM user WHERE id=$client_id")->fetch_assoc();

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    if ($nama && $email) {
        $stmt = $conn->prepare("UPDATE user SET nama=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $nama, $email, $client_id);
        $stmt->execute();
        $_SESSION['nama'] = $nama; // update sesi juga
        header("Location: profil.php?updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">Profil Saya</div>
    <div class="card-body">
      <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Profil berhasil diperbarui.</div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label>Nama</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>

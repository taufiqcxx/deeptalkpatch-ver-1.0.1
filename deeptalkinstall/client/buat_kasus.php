<?php
session_start();
include '../koneksi.php';

// Hanya client
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori = trim($_POST['kategori']);
    $deskripsi = trim($_POST['deskripsi']);
    $client_id = $_SESSION['id'];

    if ($kategori && $deskripsi) {
        $stmt = $conn->prepare("INSERT INTO `case` (case_category, case_description, case_client, case_status) VALUES (?,?,?,'pending')");
        $stmt->bind_param("ssi", $kategori, $deskripsi, $client_id);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Buat Kasus Baru</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">Buat Kasus Baru</div>
    <div class="card-body">
      <form method="POST">
        <div class="mb-3">
          <label for="kategori">Kategori</label>
          <select name="kategori" id="kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="aduan terkait guru">Aduan terkait Guru</option>
            <option value="aduan terkait kepala sekolah">Aduan terkait Kepala Sekolah</option>
            <option value="aduan terkait teman">Aduan terkait Teman</option>
            <option value="aduan pribadi">Aduan Pribadi</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="deskripsi">Deskripsi</label>
          <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Kirim</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>

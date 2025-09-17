<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['id'];
$riwayat = $conn->query("SELECT * FROM `case` WHERE case_client=$client_id ORDER BY case_id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Kasus</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">Riwayat Kasus</div>
    <div class="card-body">
      <?php if ($riwayat->num_rows > 0): ?>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Kategori</th>
              <th>Status</th>
              <th>Catatan Konselor</th>
            </tr>
          </thead>
          <tbody>
            <?php while($r = $riwayat->fetch_assoc()): ?>
              <tr>
                <td>#<?= $r['case_id'] ?></td>
                <td><?= htmlspecialchars($r['case_category']) ?></td>
                <td><?= htmlspecialchars($r['case_status']) ?></td>
                <td><?= htmlspecialchars($r['case_notes'] ?? '-') ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>Belum ada riwayat kasus.</p>
      <?php endif; ?>
      <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
</div>
</body>
</html>

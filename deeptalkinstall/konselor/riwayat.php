<?php
session_start();
include '../koneksi.php';

// Hanya konselor yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'konselor') {
    header("Location: ../login.php");
    exit;
}

$konselor_id = $_SESSION['id'];

// Ambil daftar kasus konselor
$sql = "SELECT c.*, u.nama AS client_name, u.email AS client_email 
        FROM `case` c
        LEFT JOIN user u ON c.case_client=u.id
        WHERE c.case_konselor = ?
        ORDER BY c.case_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $konselor_id);
$stmt->execute();
$cases = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Kasus - Konselor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f4f6f9; font-family:'Segoe UI',sans-serif; }
    .container { margin-top:30px; }
    .card { border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
    .card-header { background:linear-gradient(135deg,#00A7E1,#0474BA); color:#fff; border-radius:12px 12px 0 0; }
    .badge-process { background:#ffc107; color:#000; }
    .badge-finish { background:#28a745; }
    .btn-back { background:#6c757d; color:#fff; border-radius:6px; text-decoration:none; padding:8px 14px; }
    .btn-back:hover { background:#5a6268; color:#fff; }
  </style>
</head>
<body>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>📑 Riwayat Kasus</h3>
    <a href="index.php" class="btn-back">⬅ Kembali</a>
  </div>
  <div class="card">
    <div class="card-header">Daftar Semua Kasus</div>
    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Email</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($cases->num_rows > 0): ?>
            <?php while ($row = $cases->fetch_assoc()): ?>
              <tr>
                <td>#<?= $row['case_id'] ?></td>
                <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['client_email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['case_category']) ?></td>
                <td>
                  <?php if ($row['case_status']=='process'): ?>
                    <span class="badge badge-process">Proses</span>
                  <?php else: ?>
                    <span class="badge badge-finish">Selesai</span>
                  <?php endif; ?>
                </td>
                <td><?= !empty($row['case_notes']) ? nl2br(htmlspecialchars($row['case_notes'])) : '-' ?></td>
                <td><a href="dashboard.php?chat_case=<?= $row['case_id'] ?>" class="btn btn-sm btn-primary">💬 Lihat Chat</a></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">Belum ada kasus</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>

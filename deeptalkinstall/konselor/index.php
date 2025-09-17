<?php
session_start();
include '../koneksi.php';

// Hanya konselor yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'konselor') {
    header("Location: ../login.php");
    exit;
}

$konselor_id = $_SESSION['id'];
$nama_konselor = $_SESSION['nama'] ?? 'Konselor';

// === MODE API NOTIFIKASI ===
if (isset($_GET['check_notif'])) {
    header('Content-Type: application/json');
    $last_id = $_SESSION['last_case_id'] ?? 0;

    $row = $conn->query("SELECT case_id FROM `case` WHERE case_konselor=$konselor_id ORDER BY case_id DESC LIMIT 1")->fetch_assoc();

    if ($row && $row['case_id'] > $last_id) {
        $_SESSION['last_case_id'] = $row['case_id'];
        echo json_encode(['new_case' => true]);
    } else {
        echo json_encode(['new_case' => false]);
    }
    exit;
}

// === DASHBOARD UTAMA ===
// Statistik cepat
$total_active = $conn->query("SELECT COUNT(*) AS jml FROM `case` WHERE case_konselor=$konselor_id AND case_status='process'")->fetch_assoc()['jml'];
$total_finish = $conn->query("SELECT COUNT(*) AS jml FROM `case` WHERE case_konselor=$konselor_id AND case_status='finish'")->fetch_assoc()['jml'];
$total_clients = $conn->query("SELECT COUNT(DISTINCT case_client) AS jml FROM `case` WHERE case_konselor=$konselor_id")->fetch_assoc()['jml'];

// Kasus terbaru
$cases = $conn->query("SELECT c.*, u.nama AS client_name 
                       FROM `case` c
                       LEFT JOIN user u ON c.case_client=u.id
                       WHERE c.case_konselor=$konselor_id
                       ORDER BY c.case_id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Konselor Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background:#f4f6f9; }
    .sidebar { min-height: 100vh; background:#0474BA; color:#fff; }
    .sidebar a { color:#fff; text-decoration:none; display:block; padding:10px 15px; border-radius:6px; margin:4px 8px; }
    .sidebar a:hover { background:#00A7E1; }
    .stat-card { border-radius:12px; padding:20px; color:#fff; }
    .stat-active { background:#F77D0E; }
    .stat-finish { background:#28a745; }
    .stat-clients { background:#17a2b8; }
    .notif-toast { position:fixed; top:20px; right:20px; z-index:1055; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar p-3">
      <h4 class="mb-4">Halo, <?= htmlspecialchars($nama_konselor) ?></h4>
      <a href="index.php">🏠 Dashboard</a>
      <a href="dashboard.php">💬 Chat Kasus</a>
      <a href="riwayat.php">📑 Riwayat Kasus</a>
      <a href="../logout.php" class="bg-danger text-white mt-3">🚪 Logout</a>
    </div>

    <!-- Main content -->
    <div class="col-md-10 p-4">
      <h2 class="mb-4">Dashboard Konselor</h2>

      <!-- Statistik cepat -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="stat-card stat-active">
            <h4><?= $total_active ?></h4>
            <p>Kasus Aktif</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card stat-finish">
            <h4><?= $total_finish ?></h4>
            <p>Kasus Selesai</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card stat-clients">
            <h4><?= $total_clients ?></h4>
            <p>Total Client</p>
          </div>
        </div>
      </div>

      <!-- Kasus terbaru -->
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">Kasus Terbaru</div>
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($cases->num_rows > 0): ?>
                <?php while($c = $cases->fetch_assoc()): ?>
                <tr>
                  <td>#<?= $c['case_id'] ?></td>
                  <td><?= htmlspecialchars($c['client_name']) ?></td>
                  <td><?= htmlspecialchars($c['case_category']) ?></td>
                  <td>
                    <?php if($c['case_status']=='process'): ?>
                      <span class="badge bg-warning text-dark">Proses</span>
                    <?php else: ?>
                      <span class="badge bg-success">Selesai</span>
                    <?php endif; ?>
                  </td>
                  <td><a href="dashboard.php?chat_case=<?= $c['case_id'] ?>" class="btn btn-sm btn-primary">Buka Chat</a></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">Belum ada kasus</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notifikasi toast -->
<div class="toast align-items-center text-bg-info border-0 notif-toast" id="newCaseToast" role="alert">
  <div class="d-flex">
    <div class="toast-body">
      🔔 Ada kasus baru untuk ditangani!
    </div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Cek kasus baru setiap 10 detik
setInterval(() => {
  fetch("index.php?check_notif=1")
    .then(res => res.json())
    .then(data => {
      if (data.new_case) {
        const toastEl = document.getElementById('newCaseToast');
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
      }
    });
}, 10000);
</script>
</body>
</html>

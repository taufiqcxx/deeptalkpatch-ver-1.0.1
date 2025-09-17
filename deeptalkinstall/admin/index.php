<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

// Hitung data ringkasan
$total_pending = $conn->query("SELECT COUNT(*) as jml FROM `case` WHERE case_status='pending'")->fetch_assoc()['jml'];
$total_process = $conn->query("SELECT COUNT(*) as jml FROM `case` WHERE case_status='process'")->fetch_assoc()['jml'];
$total_finish  = $conn->query("SELECT COUNT(*) as jml FROM `case` WHERE case_status='finish'")->fetch_assoc()['jml'];
$user_aktif    = $conn->query("SELECT COUNT(*) as jml FROM user WHERE status_register='approve'")->fetch_assoc()['jml'];

// Ambil notifikasi terbaru (5 terakhir)
$notif_sql = "
  (SELECT 'Kasus Baru' as type, case_category as detail, created_at as waktu 
   FROM `case` WHERE case_status='pending')
  UNION ALL
  (SELECT 'Kasus Selesai', case_category, created_at 
   FROM `case` WHERE case_status='finish')
  UNION ALL
  (SELECT 'User Baru', nama, created_at FROM user WHERE status_register='pending')
  UNION ALL
  (SELECT 'Appeal Request', email, created_at FROM appeals)
  ORDER BY waktu DESC
  LIMIT 5
";
$notif_result = $conn->query($notif_sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .sidebar {
      height: 100vh;
      background: #1b2838;
      color: white;
      position: fixed;
      top: 0; left: 0;
      width: 220px;
      padding-top: 20px;
      text-align: center;
    }
    .sidebar img.logo {
      max-width: 120px;
      margin-bottom: 10px;
    }
    .sidebar h4 { color: #fff; margin-bottom: 20px; }
    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #ddd;
      text-decoration: none;
      margin: 4px 0;
      border-radius: 6px;
      cursor: pointer;
      text-align: left;
    }
    .sidebar a:hover { background: rgba(255,255,255,0.1); color: #fff; }

    .topbar {
      position: fixed;
      left: 220px; right: 0;
      height: 60px;
      background: linear-gradient(to right, #00A7E1, #0474BA);
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
      color: white;
    }
    .content { margin-left: 240px; padding: 80px 20px 20px 20px; }
    .card { border-radius: 16px; box-shadow: 0px 4px 12px rgba(0,0,0,0.08); }
    .stat-card { color: white; padding: 20px; }
    .bg-pending { background: linear-gradient(135deg, #ffb74d, #f57c00); }
    .bg-process { background: linear-gradient(135deg, #42a5f5, #0474BA); }
    .bg-finish  { background: linear-gradient(135deg, #66bb6a, #2e7d32); }
    .bg-user    { background: linear-gradient(135deg, #00A7E1, #0474BA); }
    .stat-icon { font-size: 32px; opacity: 0.8; }
  </style>
</head>
<body>
  <!-- Sidebar -->
	<div class="sidebar">
		<img src="http://deeptalk.test/assets/logo.png" alt="Logo" class="logo">
		<h4>Admin Panel</h4>
			<a href="user_activity.php">🏠 Jurnal Pengguna</a>
			<a href="admin.php">📋 Manajemen Kasus</a>
			<a onclick="loadPage('chat_monitor.php')">💬 Monitor Chat</a>
			<a onclick="loadPage('userman.php')">👥 User Manager</a>
			<a onclick="loadPage('statistic.php')">📊 Statistik</a>
			<a onclick="loadPage('notification.php')">🔔 Notifikasi</a>
			<a href="../logout.php">🚪 Logout</a>
	</div>


  <!-- Topbar -->
  <div class="topbar">
    <div class="dropdown">
      <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
        🔔 Notifikasi
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <?php if ($notif_result->num_rows > 0): ?>
          <?php while($n = $notif_result->fetch_assoc()): ?>
            <li><a class="dropdown-item" href="#">
              <strong><?= $n['type'] ?>:</strong> <?= htmlspecialchars($n['detail']) ?>
              <br><small class="text-muted"><?= $n['waktu'] ?></small>
            </a></li>
          <?php endwhile; ?>
        <?php else: ?>
          <li><span class="dropdown-item">Tidak ada notifikasi</span></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- Content -->
  <div class="content" id="contentArea">
    <h2 class="mb-4">Selamat datang di Dashboard Admin</h2>
    <p class="mb-4">Pilih menu di sidebar untuk mulai mengelola sistem.</p>

    <!-- Ringkasan Statistik -->
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card stat-card bg-pending">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4><?= $total_pending ?></h4>
              <p class="mb-0">Kasus Pending</p>
            </div>
            <div class="stat-icon">⏳</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-process">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4><?= $total_process ?></h4>
              <p class="mb-0">Kasus Aktif</p>
            </div>
            <div class="stat-icon">⚡</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-finish">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4><?= $total_finish ?></h4>
              <p class="mb-0">Kasus Selesai</p>
            </div>
            <div class="stat-icon">✅</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-user">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h4><?= $user_aktif ?></h4>
              <p class="mb-0">User Aktif</p>
            </div>
            <div class="stat-icon">👤</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    async function loadPage(page) {
      try {
        let response = await fetch(page);
        let html = await response.text();
        document.getElementById("contentArea").innerHTML = html;

        let scripts = document.getElementById("contentArea").querySelectorAll("script");
        scripts.forEach(scr => {
          let newScript = document.createElement("script");
          if (scr.src) {
            newScript.src = scr.src;
          } else {
            newScript.textContent = scr.textContent;
          }
          document.body.appendChild(newScript);
        });

      } catch (error) {
        document.getElementById("contentArea").innerHTML = "<p class='text-danger'>Gagal memuat halaman.</p>";
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

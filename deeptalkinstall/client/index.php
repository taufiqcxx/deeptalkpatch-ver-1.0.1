<?php
session_start();
include '../koneksi.php';

// Hanya client yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['id'];

// Hitung statistik
$total_cases = $conn->query("SELECT COUNT(*) AS jml FROM `case` WHERE case_client=$client_id")->fetch_assoc()['jml'];
$open_cases = $conn->query("SELECT COUNT(*) AS jml FROM `case` WHERE case_client=$client_id AND case_status='open'")->fetch_assoc()['jml'];
$finished_cases = $conn->query("SELECT COUNT(*) AS jml FROM `case` WHERE case_client=$client_id AND case_status='finish'")->fetch_assoc()['jml'];

// Ambil 5 kasus terbaru
$recent_cases = $conn->query("SELECT * FROM `case` WHERE case_client=$client_id ORDER BY case_id DESC LIMIT 5");

// AJAX cek notifikasi
if (isset($_GET['check_notif'])) {
    header("Content-Type: application/json");
    $sql = "SELECT COUNT(*) as jml 
            FROM messages m
            JOIN `case` c ON m.case_id = c.case_id
            WHERE c.case_client=? AND m.receiver_id=? AND m.is_read=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $client_id, $client_id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['jml'] ?? 0;
    echo json_encode(["count"=>$count]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
body { 
  margin: 0; 
  font-family: 'Segoe UI', sans-serif; 
  background: #f4f6f9; 
  display: flex; 
  height: 100vh; 
}

/* Sidebar */
.sidebar { 
  width: 260px; 
  background: linear-gradient(to bottom, #00A7E1, #0474BA); 
  display: flex; 
  flex-direction: column; 
  color: #fff;
}
.sidebar h3 { 
  text-align: center; 
  padding: 15px; 
  margin: 0; 
  font-weight: bold;
  background: rgba(0,0,0,0.1);
}
.nav-links { flex-grow: 1; }
.nav-links a { 
  display: block; 
  padding: 12px 20px; 
  color: #f0f0f0; 
  text-decoration: none; 
  border-bottom: 1px solid rgba(255,255,255,0.1); 
}
.nav-links a:hover { background: rgba(255,255,255,0.15); }
.nav-links a.active { 
  background: rgba(255,255,255,0.25); 
  color: #fff; 
  font-weight: bold; 
}
.logout { 
  padding: 12px 20px; 
  background: #dc3545; 
  color: #fff; 
  text-align: center; 
  text-decoration: none; 
}
.logout:hover { background: #b02a37; }

/* Content */
.content { 
  flex-grow: 1; 
  padding: 20px; 
  overflow-y: auto; 
}

/* Card */
.card { 
  border-radius: 12px; 
  box-shadow: 0 4px 10px rgba(0,0,0,0.05); 
  margin-bottom: 20px; 
}
.card-header { 
  background: linear-gradient(135deg, #00A7E1, #0474BA); 
  color: #fff; 
  border-radius: 12px 12px 0 0; 
  font-weight: bold;
}

/* Button */
.btn-primary {
  background: linear-gradient(to right, #00A7E1, #0474BA);
  border: none;
}
.btn-primary:hover {
  background: linear-gradient(to right, #0474BA, #00A7E1);
}

/* Toast */
#toast {
  visibility: hidden; 
  min-width: 280px; 
  margin-left: -140px;
  background: linear-gradient(to right, #00A7E1, #0474BA); 
  color: #fff; 
  text-align: center;
  border-radius: 8px; 
  padding: 14px; 
  position: fixed;
  z-index: 9999; 
  left: 50%; 
  bottom: 30px; 
  font-size: 15px;
}
/* Warna sama dengan admin */
.bg-pending { 
	background: linear-gradient(135deg, #ffb74d, #f57c00);
	border-radius: 12px; 
	}
.bg-process {
	background: linear-gradient(135deg, #42a5f5, #0474BA);
	border-radius: 12px;
	}
.bg-finish  {
	background: linear-gradient(135deg, #66bb6a, #2e7d32);
	border-radius: 12px;
	}
.stat-icon {
	font-size: 30px;
	margin-bottom: 10px;
	opacity: 0.9;
	}

.stat-card {
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0px 4px 12px rgba(0,0,0,0.08);
}
.stat-icon {
  font-size: 36px;
  opacity: 0.9;
}
.bg-pending { background: linear-gradient(135deg, #ffb74d, #f57c00); }
.bg-process { background: linear-gradient(135deg, #42a5f5, #0474BA); }
.bg-finish  { background: linear-gradient(135deg, #66bb6a, #2e7d32); }


#toast.show {
  visibility: visible; 
  animation: fadein 0.5s, fadeout 0.5s 3s;
}
@keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
@keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
  <h3>Client</h3>
  <div class="nav-links">
    <a href="index.php" class="active">🏠 Dashboard</a>
    <a href="dashboard.php">💬 Chat Kasus</a>
    <a href="buat_kasus.php">➕ Buat Kasus Baru</a>
    <a href="riwayat.php">📜 Riwayat Kasus</a>
    <a href="profil.php">👤 Profil Saya</a>
    <a href="../logout.php">🚪 Logout</a> <!-- pindah ke sini -->
  </div>
</div>
  </div>

  <!-- Main Content -->
  <div class="content">
    <h2 class="mb-4">Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</h2>

    <!-- Statistik -->
		<div class="row mb-4">
		  <div class="col-md-4">
			<div class="card stat-card bg-pending text-white">
			  <div class="d-flex justify-content-between align-items-center">
				<div>
				  <h3><?= $total_cases ?></h3>
				  <p class="mb-0">📜Kasus Total</p>
				</div>
				<div class="stat-icon"><i class="fas fa-folder-open"></i></div>
			  </div>
			</div>
		  </div>
		  
		  <div class="col-md-4">
			<div class="card stat-card bg-process text-white">
			  <div class="d-flex justify-content-between align-items-center">
				<div>
				  <h3><?= $open_cases ?></h3>
				  <p class="mb-0">⚡Kasus Aktif</p>
				</div>
				<div class="stat-icon"><i class="fas fa-bolt"></i></div>
			  </div>
			</div>
		  </div>
		  
		  <div class="col-md-4">
			<div class="card stat-card bg-finish text-white">
			  <div class="d-flex justify-content-between align-items-center">
				<div>
				  <h3><?= $finished_cases ?></h3>
				  <p class="mb-0">✅Kasus Selesai</p>
				</div>
				<div class="stat-icon"><i class="fas fa-check-circle"></i></div>
			  </div>
			</div>
		  </div>
		</div>


    <!-- Kasus terbaru -->
    <div class="card">
      <div class="card-header">Kasus Terbaru</div>
      <div class="card-body">
        <?php if ($recent_cases->num_rows > 0): ?>
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($rc = $recent_cases->fetch_assoc()): ?>
                <tr>
                  <td>#<?= $rc['case_id'] ?></td>
                  <td><?= htmlspecialchars($rc['case_category']) ?></td>
                  <td><?= htmlspecialchars($rc['case_status']) ?></td>
                  <td><a href="dashboard.php?chat_case=<?= $rc['case_id'] ?>" class="btn btn-sm btn-primary">Buka</a></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>Belum ada kasus yang dibuat.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast">Ada pesan baru dari konselor!</div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    function showToast(msg) {
      var x = document.getElementById("toast");
      x.innerText = msg;
      x.className = "show";
      setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3500);
    }

    let lastNotif = 0;
    function checkNotif() {
      $.get("index.php?check_notif=1", function(data) {
        if (data.count > lastNotif) {
          showToast("💬 Ada pesan baru dari konselor!");
        }
        lastNotif = data.count;
      }, "json");
    }
    setInterval(checkNotif, 5000);
  </script>
</body>
</html>

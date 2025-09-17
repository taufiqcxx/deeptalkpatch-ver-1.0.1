<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';

// Cek hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- Ambil filter ---
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$user_filter = isset($_GET['user']) ? $_GET['user'] : 'all';

// --- Pagination setup ---
$limit = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Query total data ---
$where = "WHERE 1";
if ($role_filter !== 'all') {
    $where .= " AND u.role = '".$conn->real_escape_string($role_filter)."'";
}
if ($user_filter !== 'all') {
    $where .= " AND u.id = '".$conn->real_escape_string($user_filter)."'";
}

$total_sql = "SELECT COUNT(*) as total 
              FROM user_activity ua 
              LEFT JOIN user u ON ua.user_id = u.id 
              $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// --- Query data aktivitas ---
$sql = "SELECT ua.id, u.nama, u.email, ua.activity, ua.created_at, u.role
        FROM user_activity ua
        LEFT JOIN user u ON ua.user_id = u.id
        $where
        ORDER BY ua.created_at DESC
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// --- Ambil daftar user untuk filter ---
$users = $conn->query("SELECT id, nama FROM user ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>User Activity Log</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .container { margin-top: 30px; }
    .card { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .card-header { background: linear-gradient(135deg, #00A7E1, #0474BA); color: #fff; border-radius: 12px 12px 0 0; }
    .btn-primary { background: #F77D0E; border: none; }
    .btn-primary:hover { background: #F9AA50; }
    .btn-success { background: #00A7E1; border: none; }
    .btn-success:hover { background: #0474BA; }
    .btn-danger { background: #dc3545; border: none; }
    .table thead { background: linear-gradient(135deg, #00A7E1, #0474BA); color: #fff; }
  </style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">📊 User Activity Log</h4>
      <div>
        <a href="index.php" class="btn btn-success btn-sm me-2">⬅️ Kembali</a>
        <a href="?hapus=all" onclick="return confirm('Yakin hapus semua log?')" class="btn btn-danger btn-sm">🗑 Hapus Semua</a>
      </div>
    </div>
    <div class="card-body">
      <!-- Filter -->
      <form method="get" class="row mb-3 g-2">
        <div class="col-md-3">
          <select name="role" class="form-select" onchange="this.form.submit()">
            <option value="all" <?= $role_filter=='all'?'selected':'' ?>>-- Semua Role --</option>
            <option value="client" <?= $role_filter=='client'?'selected':'' ?>>Client</option>
            <option value="konselor" <?= $role_filter=='konselor'?'selected':'' ?>>Konselor</option>
            <option value="admin" <?= $role_filter=='admin'?'selected':'' ?>>Admin</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="user" class="form-select" onchange="this.form.submit()">
            <option value="all" <?= $user_filter=='all'?'selected':'' ?>>-- Semua User --</option>
            <?php while($u = $users->fetch_assoc()): ?>
              <option value="<?= $u['id'] ?>" <?= $user_filter==$u['id']?'selected':'' ?>>
                <?= htmlspecialchars($u['nama']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
      </form>

      <!-- Tabel -->
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aktivitas</th>
            <th>Waktu</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['role']) ?></td>
              <td><?= htmlspecialchars($row['activity']) ?></td>
              <td><?= $row['created_at'] ?></td>
              <td>
                <a href="?hapus=<?= $row['id'] ?>" 
                   onclick="return confirm('Hapus log ini?')" 
                   class="btn btn-sm btn-danger">Hapus</a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center">Belum ada aktivitas</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <nav>
        <ul class="pagination justify-content-center">
          <?php for($i=1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i==$page)?'active':'' ?>">
              <a class="page-link" href="?page=<?= $i ?>&role=<?= $role_filter ?>&user=<?= $user_filter ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
  </div>
</div>
</body>
</html>

<?php
// --- Hapus log ---
if (isset($_GET['hapus'])) {
    if ($_GET['hapus'] === 'all') {
        $conn->query("TRUNCATE TABLE user_activity");
    } else {
        $id = (int) $_GET['hapus'];
        $conn->query("DELETE FROM user_activity WHERE id=$id");
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

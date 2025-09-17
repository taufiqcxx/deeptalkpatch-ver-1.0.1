<?php
include '../koneksi.php';

// Ambil daftar konselor
$konselor_sql = "SELECT id, nama FROM user WHERE role='konselor' AND status_register='approve'";
$konselor = $conn->query($konselor_sql);

// Tambah kasus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $kategori = trim($_POST['case_category']);
    $client_id = (int)$_POST['case_client'];
    $status = $_POST['case_status'];

    $stmt = $conn->prepare("INSERT INTO `case` (case_category, case_client, case_status) VALUES (?,?,?)");
    $stmt->bind_param("sis", $kategori, $client_id, $status);
    $stmt->execute();
    echo "<div class='alert alert-success'>✅ Kasus baru berhasil ditambahkan.</div>";
}

// Update kasus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $case_id = (int)$_POST['case_id'];
    $konselor_id = $_POST['case_konselor'] ? (int)$_POST['case_konselor'] : NULL;
    $case_status = $_POST['case_status'];

    $stmt = $conn->prepare("UPDATE `case` SET case_konselor=?, case_status=? WHERE case_id=?");
    $stmt->bind_param("isi", $konselor_id, $case_status, $case_id);
    $stmt->execute();
    echo "<div class='alert alert-success'>✅ Kasus #$case_id berhasil diperbarui.</div>";
}

// Hapus kasus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $case_id = (int)$_POST['case_id'];
    $stmt = $conn->prepare("DELETE FROM `case` WHERE case_id=?");
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    echo "<div class='alert alert-danger'>🗑️ Kasus #$case_id berhasil dihapus.</div>";
}

// Ambil daftar kasus
$sql = "SELECT c.*, u1.nama AS client_name, u2.nama AS konselor_name 
        FROM `case` c
        LEFT JOIN user u1 ON c.case_client = u1.id
        LEFT JOIN user u2 ON c.case_konselor = u2.id
        ORDER BY c.case_id DESC";
$cases = $conn->query($sql);

// Deteksi standalone
$standalone = basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__);
?>

<?php if ($standalone): ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Kasus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }
    h3 {
      color: #0474BA;
      font-weight: bold;
    }
    .btn-primary {
      background: #F77D0E;
      border: none;
    }
    .btn-primary:hover {
      background: #F9AA50;
    }
    .btn-success {
      background: #00A7E1;
      border: none;
    }
    .btn-success:hover {
      background: #0474BA;
    }
    .btn-danger {
      background: #dc3545;
    }
    .card-header {
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      color: #fff;
      font-weight: bold;
    }
    .table thead {
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      color: #fff;
    }
  </style>
</head>
<body class="p-4">
<div class="container">
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>📋 Manajemen Kasus</h3>
  <a href="index.php" class="btn btn-success">⬅️ Kembali ke Beranda</a>
</div>

<!-- Tombol Tambah Kasus -->
<button class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#formTambah">➕ Tambah Kasus</button>
<div id="formTambah" class="collapse mb-4">
  <div class="card">
    <div class="card-header">Tambah Kasus Baru</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="mb-3">
          <label>Kategori</label>
          <input type="text" name="case_category" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>ID Client</label>
          <input type="number" name="case_client" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Status</label>
          <select name="case_status" class="form-select">
            <option value="pending">Menunggu</option>
            <option value="process">Proses</option>
            <option value="finish">Selesai</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </form>
    </div>
  </div>
</div>

<table class="table table-bordered table-hover align-middle">
  <thead>
    <tr>
      <th>ID Kasus</th>
      <th>Kategori</th>
      <th>Client</th>
      <th>Konselor</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($c = $cases->fetch_assoc()): ?>
      <tr>
        <td><?= $c['case_id'] ?></td>
        <td><?= htmlspecialchars($c['case_category']) ?></td>
        <td><?= htmlspecialchars($c['client_name'] ?: '-') ?></td>
        <td>
          <form method="POST" class="d-flex">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="case_id" value="<?= $c['case_id'] ?>">
            <select name="case_konselor" class="form-select form-select-sm me-2">
              <option value="">-- Pilih Konselor --</option>
              <?php
              $konselor->data_seek(0);
              while ($k = $konselor->fetch_assoc()):
              ?>
                <option value="<?= $k['id'] ?>" <?= ($c['case_konselor'] == $k['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($k['nama']) ?>
                </option>
              <?php endwhile; ?>
            </select>
        </td>
        <td>
            <select name="case_status" class="form-select form-select-sm me-2">
              <option value="pending" <?= ($c['case_status']=='pending')?'selected':'' ?>>Menunggu</option>
              <option value="process" <?= ($c['case_status']=='process')?'selected':'' ?>>Proses</option>
              <option value="finish" <?= ($c['case_status']=='finish')?'selected':'' ?>>Selesai</option>
            </select>
        </td>
        <td>
            <button type="submit" class="btn btn-primary btn-sm me-2">💾 Update</button>
          </form>
          <form method="POST" onsubmit="return confirm('Yakin hapus kasus ini?')" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="case_id" value="<?= $c['case_id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php if ($standalone): ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>

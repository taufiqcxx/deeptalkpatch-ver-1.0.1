<?php
include '../koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle request AJAX (CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO user (nama, email, phone, password, role, status_register, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $_POST['nama'], $_POST['email'], $_POST['phone'], $password, $_POST['role'], $_POST['status_register']);
        $stmt->execute();
        echo "success";
        exit;
    }

    if ($action === 'update') {
        $stmt = $conn->prepare("UPDATE user SET nama=?, email=?, phone=?, role=?, status_register=? WHERE id=?");
        $stmt->bind_param("sssssi", $_POST['nama'], $_POST['email'], $_POST['phone'], $_POST['role'], $_POST['status_register'], $_POST['id']);
        $stmt->execute();
        echo "success";
        exit;
    }

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM user WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        echo "success";
        exit;
    }
}

// Ambil semua user
$result = $conn->query("SELECT * FROM user ORDER BY id DESC");

// Deteksi standalone
$standalone = basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__);
?>

<?php if ($standalone): ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>User Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .card-header {
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      color: #fff;
      font-weight: 600;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-primary {
      background: #0474BA;
      border: none;
    }
    .btn-primary:hover {
      background: #00A7E1;
    }
    .table thead {
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      color: #fff;
    }
  </style>
</head>
<body class="p-4">
<div class="container">
  <div class="card">
    <div class="card-header">
      <span>👥 User Manager</span>
      <a href="index.php" class="btn btn-light btn-sm">⬅ Kembali ke Dashboard</a>
    </div>
    <div class="card-body">
<?php endif; ?>

  <h3 class="mb-3">User Manager</h3>
  <button class="btn btn-primary mb-3" onclick="showAddForm()">+ Tambah User</button>

  <div class="row mb-3">
    <div class="col-md-4">
      <input type="text" id="searchInput" class="form-control" placeholder="Cari nama/email/phone..." onkeyup="filterTable()">
    </div>
    <div class="col-md-3">
      <select id="roleFilter" class="form-control" onchange="filterTable()">
        <option value="">-- Semua Role --</option>
        <option value="admin">Admin</option>
        <option value="konselor">Konselor</option>
        <option value="client">Client</option>
      </select>
    </div>
    <div class="col-md-3">
      <select id="statusFilter" class="form-control" onchange="filterTable()">
        <option value="">-- Semua Status --</option>
        <option value="pending">Pending</option>
        <option value="approve">Approve</option>
        <option value="banned">Banned</option>
      </select>
    </div>
  </div>

  <table class="table table-bordered table-striped" id="userTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Status Register</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= $row['role'] ?></td>
        <td><?= $row['status_register'] ?></td>
        <td>
          <button class="btn btn-sm btn-warning" onclick='showEditForm(<?= json_encode($row) ?>)'>Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $row['id'] ?>)">Hapus</button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

<?php if ($standalone): ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>

<!-- Modal Form -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="userForm">
        <div class="modal-header">
          <h5 class="modal-title">Form User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="user_id">
          <input type="hidden" name="action" id="action">

          <div class="mb-2">
            <label>Nama</label>
            <input type="text" class="form-control" name="nama" id="nama" required>
          </div>
          <div class="mb-2">
            <label>Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
          </div>
          <div class="mb-2">
            <label>Phone</label>
            <input type="text" class="form-control" name="phone" id="phone">
          </div>
          <div class="mb-2 password-field">
            <label>Password</label>
            <input type="password" class="form-control" name="password" id="password">
          </div>
          <div class="mb-2">
            <label>Role</label>
            <select class="form-control" name="role" id="role">
              <option value="client">Client</option>
              <option value="konselor">Konselor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Status Register</label>
            <select class="form-control" name="status_register" id="status_register">
              <option value="pending">Pending</option>
              <option value="approve">Approve</option>
              <option value="banned">Banned</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let userModal = new bootstrap.Modal(document.getElementById('userModal'));

function showAddForm() {
  document.getElementById("action").value = "create";
  document.getElementById("userForm").reset();
  document.querySelector(".password-field").style.display = "block";
  userModal.show();
}

function showEditForm(user) {
  document.getElementById("action").value = "update";
  document.getElementById("user_id").value = user.id;
  document.getElementById("nama").value = user.nama;
  document.getElementById("email").value = user.email;
  document.getElementById("phone").value = user.phone;
  document.getElementById("role").value = user.role;
  document.getElementById("status_register").value = user.status_register;
  document.querySelector(".password-field").style.display = "none";
  userModal.show();
}

document.getElementById("userForm").addEventListener("submit", async function(e){
  e.preventDefault();
  let formData = new FormData(this);
  let res = await fetch("userman.php", { method: "POST", body: formData });
  let text = await res.text();
  if (text.includes("success")) {
    alert("Data berhasil disimpan!");
    loadPage('userman.php');
    userModal.hide();
  } else {
    alert("Gagal menyimpan data!");
  }
});

async function deleteUser(id) {
  if (confirm("Yakin ingin menghapus user ini?")) {
    let formData = new FormData();
    formData.append("action", "delete");
    formData.append("id", id);
    let res = await fetch("userman.php", { method: "POST", body: formData });
    let text = await res.text();
    if (text.includes("success")) {
      alert("User berhasil dihapus!");
      loadPage('userman.php');
    } else {
      alert("Gagal menghapus user!");
    }
  }
}

// Filtering table (client-side)
function filterTable() {
  let search = document.getElementById("searchInput").value.toLowerCase();
  let role = document.getElementById("roleFilter").value;
  let status = document.getElementById("statusFilter").value;

  let rows = document.querySelectorAll("#userTable tbody tr");
  rows.forEach(row => {
    let nama = row.cells[1].innerText.toLowerCase();
    let email = row.cells[2].innerText.toLowerCase();
    let phone = row.cells[3].innerText.toLowerCase();
    let roleCell = row.cells[4].innerText;
    let statusCell = row.cells[5].innerText;

    let matchSearch = nama.includes(search) || email.includes(search) || phone.includes(search);
    let matchRole = (role === "" || roleCell === role);
    let matchStatus = (status === "" || statusCell === status);

    if (matchSearch && matchRole && matchStatus) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}
</script>

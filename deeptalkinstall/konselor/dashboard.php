<?php
session_start();
include '../koneksi.php';

// Hanya konselor yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'konselor') {
    header("Location: ../login.php");
    exit;
}

$konselor_id = $_SESSION['id'];

// Ambil daftar kasus milik konselor
$cases = $conn->query("SELECT c.*, u.nama AS client_name 
                       FROM `case` c
                       LEFT JOIN user u ON c.case_client=u.id
                       WHERE c.case_konselor=$konselor_id 
                       ORDER BY c.case_id DESC");

// Kasus aktif untuk chat
$active_case_id = isset($_GET['chat_case']) ? (int)$_GET['chat_case'] : 0;

// Ambil data kasus aktif
$active_case = null;
if ($active_case_id) {
    $active_case = $conn->query("SELECT * FROM `case` WHERE case_id=$active_case_id")->fetch_assoc();
}

// Ambil pesan chat
$messages = [];
if ($active_case_id) {
    $msg_sql = "SELECT m.*, u.nama, u.role 
                FROM messages m 
                LEFT JOIN user u ON m.sender_id=u.id 
                WHERE m.case_id=? ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($msg_sql);
    $stmt->bind_param("i", $active_case_id);
    $stmt->execute();
    $messages = $stmt->get_result();
}

// Kirim pesan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '' && $active_case_id) {
        $get_client = $conn->query("SELECT case_client FROM `case` WHERE case_id=$active_case_id");
        $client_row = $get_client->fetch_assoc();
        $receiver_id = $client_row ? $client_row['case_client'] : NULL;

        $stmt = $conn->prepare("INSERT INTO messages (case_id, sender_id, receiver_id, message) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $active_case_id, $konselor_id, $receiver_id, $msg);
        $stmt->execute();
    }
    header("Location: dashboard.php?chat_case=$active_case_id");
    exit;
}

// Selesaikan kasus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_case'])) {
    $stmt = $conn->prepare("UPDATE `case` SET case_status='finish' WHERE case_id=?");
    $stmt->bind_param("i", $active_case_id);
    $stmt->execute();
    header("Location: dashboard.php?chat_case=$active_case_id");
    exit;
}

// Simpan catatan kasus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notes'])) {
    $notes = $_POST['case_notes'];
    $stmt = $conn->prepare("UPDATE `case` SET case_notes=? WHERE case_id=?");
    $stmt->bind_param("si", $notes, $active_case_id);
    $stmt->execute();
    header("Location: dashboard.php?chat_case=$active_case_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Konselor Dashboard</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }

    /* Sidebar */
    .sidebar { width: 280px; background: #fff; border-right: 1px solid #ddd; display: flex; flex-direction: column; }
    .sidebar h3 { color: #0474BA; text-align: center; padding: 15px; border-bottom: 1px solid #ddd; margin:0; }
    .case-list { flex: 1; overflow-y: auto; }
    .case-item { padding: 12px; border-bottom: 1px solid #eee; display:block; text-decoration:none; color:inherit; transition: background 0.2s; }
    .case-item:hover { background:#f1f1f1; }
    .case-item.active { background:#00A7E1; color:#fff; }
    .btn-nav { display:block; text-align:center; margin:10px; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold; }
    .btn-back { background:#F77D0E; color:#fff; }
    .btn-logout { background:#dc3545; color:#fff; }

    /* Chat area */
    .chat-area { flex: 1; display: flex; flex-direction: column; height: 100vh; }
    .chat-header { padding: 12px 15px; background: #0474BA; color: #fff; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
    .chat-header .actions { display: flex; gap: 10px; }
    .btn-finish { background:#28a745; border:none; padding:6px 12px; color:#fff; border-radius:6px; cursor:pointer; }

    .chat-box { flex: 1; overflow-y: auto; padding: 12px; background: #fefefe; }
    .msg { margin-bottom: 10px; max-width: 70%; padding: 8px 12px; border-radius: 10px; font-size:14px; }
    .msg.konselor { background: #F77D0E; color:#fff; margin-left:auto; }
    .msg.client { background: #e1e1e1; color:#000; margin-right:auto; }

    .chat-input { display:flex; padding:8px; border-top:1px solid #ddd; background:#fff; gap:6px; }
    .chat-input input { flex:1; padding:8px 10px; border:1px solid #ddd; border-radius:6px; }
    .chat-input button { background:#0474BA; color:#fff; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; }

    .notes-box { padding:6px 10px; border-top:1px solid #ddd; background:#fff; }
    .notes-box textarea { width:100%; height:50px; resize:vertical; font-size:13px; padding:6px; border-radius:6px; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h3>Kasus Saya</h3>
    <a href="index.php" class="btn-nav btn-back">← Kembali ke Beranda</a>
    <div class="case-list">
      <?php if ($cases->num_rows > 0): ?>
        <?php while ($c = $cases->fetch_assoc()): ?>
          <a href="dashboard.php?chat_case=<?= $c['case_id'] ?>" 
             class="case-item <?= ($active_case_id == $c['case_id']) ? 'active' : '' ?>">
            <strong>#<?= $c['case_id'] ?> - <?= $c['case_category'] ?></strong><br>
            Client: <?= $c['client_name'] ?: '-' ?><br>
            <small>Status: <?= $c['case_status'] ?></small>
          </a>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="padding:10px; text-align:center;">Belum ada kasus</p>
      <?php endif; ?>
    </div>
    <a href="../logout.php" class="btn-nav btn-logout">Logout</a>
  </div>

  <!-- Chat Area -->
  <div class="chat-area">
    <?php if ($active_case_id && $active_case): ?>
      <div class="chat-header">
        <span>Chat Kasus #<?= $active_case_id ?> (<?= $active_case['case_status'] ?>)</span>
        <div class="actions">
          <form method="POST">
            <button type="submit" name="finish_case" class="btn-finish">Selesai Kasus</button>
          </form>
        </div>
      </div>
      <div class="chat-box" id="chatBox">
        <?php if ($messages && $messages->num_rows > 0): ?>
          <?php while($msg = $messages->fetch_assoc()): ?>
            <div class="msg <?= $msg['role']=='konselor' ? 'konselor' : 'client' ?>">
              <?= htmlspecialchars($msg['message']) ?><br>
              <small><?= $msg['nama'] ?> - <?= $msg['created_at'] ?></small>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>Belum ada pesan.</p>
        <?php endif; ?>
      </div>
      <form class="chat-input" method="POST">
        <input type="text" name="message" placeholder="Tulis pesan..." required>
        <button type="submit" name="send_message">Kirim</button>
      </form>
      <div class="notes-box">
        <form method="POST">
          <label>Catatan Kasus:</label><br>
          <textarea name="case_notes"><?= htmlspecialchars($active_case['case_notes'] ?? '') ?></textarea><br><br>
          <button type="submit" name="save_notes" class="btn-finish">Simpan Catatan</button>
        </form>
      </div>
    <?php else: ?>
      <div class="chat-header">Pilih Kasus untuk Memulai Chat</div>
    <?php endif; ?>
  </div>

  <script>
    const chatBox = document.getElementById("chatBox");
    if(chatBox){ chatBox.scrollTop = chatBox.scrollHeight; }
  </script>
</body>
</html>

<?php
session_start();
include '../koneksi.php';

// Hanya client yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit;
}

$client_id = (int)$_SESSION['id'];

// Ambil daftar kasus milik client (prepared)
$stmt_cases = $conn->prepare("SELECT case_id, case_category, case_status FROM `case` WHERE case_client = ? ORDER BY case_id DESC");
$stmt_cases->bind_param("i", $client_id);
$stmt_cases->execute();
$cases = $stmt_cases->get_result();

// Kasus aktif untuk chat (dari GET)
$active_case_id = isset($_GET['chat_case']) ? (int)$_GET['chat_case'] : 0;

// Ambil pesan chat (prepared) - urut berdasarkan id agar aman jika created_at tidak ada
$messages = null;
if ($active_case_id) {
    $stmt_msgs = $conn->prepare(
        "SELECT m.*, u.nama, u.role 
         FROM messages m 
         LEFT JOIN `user` u ON m.sender_id = u.id 
         WHERE m.case_id = ? 
         ORDER BY m.id ASC"
    );
    $stmt_msgs->bind_param("i", $active_case_id);
    $stmt_msgs->execute();
    $messages = $stmt_msgs->get_result();
}

// Kirim pesan baru (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg = trim($_POST['message'] ?? '');
    // Ensure we have an active case and message not empty
    if ($active_case_id && $msg !== '') {
        // pastikan kasus milik client
        $stmt_check = $conn->prepare("SELECT case_konselor, case_client FROM `case` WHERE case_id = ? LIMIT 1");
        $stmt_check->bind_param("i", $active_case_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($res_check && $res_check->num_rows === 1) {
            $row_case = $res_check->fetch_assoc();
            // pastikan client pemilik kasus
            if ((int)$row_case['case_client'] === $client_id) {
                $receiver_id = $row_case['case_konselor'] !== null ? (int)$row_case['case_konselor'] : null;

                // Insert pesan — jika receiver_id ada, sertakan; jika null, gunakan query tanpa receiver_id
                if ($receiver_id === null) {
                    $stmt_ins = $conn->prepare("INSERT INTO messages (case_id, sender_id, message) VALUES (?,?,?)");
                    $stmt_ins->bind_param("iis", $active_case_id, $client_id, $msg);
                } else {
                    $stmt_ins = $conn->prepare("INSERT INTO messages (case_id, sender_id, receiver_id, message) VALUES (?,?,?,?)");
                    $stmt_ins->bind_param("iiis", $active_case_id, $client_id, $receiver_id, $msg);
                }
                $stmt_ins->execute();
                $stmt_ins->close();
            }
        }
        $stmt_check->close();
    }

    // PRG pattern: redirect agar tidak resend form saat refresh
    header("Location: dashboard.php?chat_case=" . $active_case_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard - Chat Room</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #00A7E1, #0474BA);
      margin: 0;
      display: flex;
      height: 100vh;
    }
    .sidebar {
      width: 260px;
      background: #fff;
      box-shadow: 2px 0 6px rgba(0,0,0,0.1);
      overflow-y: auto;
    }
    .sidebar h3 {
      color: #0474BA;
      text-align: center;
      padding: 15px;
      border-bottom: 1px solid #ddd;
      margin: 0;
    }
    .case-item {
      padding: 12px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
      transition: background 0.2s;
      display: block;
      color: inherit;
      text-decoration: none;
    }
    .case-item:hover { background: #f1f1f1; }
    .case-item.active { background: #00A7E1; color: #fff; }
    .chat-area {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      background: #f8f9fa;
    }
    .chat-header {
      padding: 15px;
      background: #0474BA;
      color: #fff;
      font-weight: bold;
    }
    .chat-box {
      flex-grow: 1;
      padding: 20px;
      overflow-y: auto;
      background: #fefefe;
    }
    .msg {
      margin-bottom: 12px;
      max-width: 70%;
      padding: 10px 14px;
      border-radius: 12px;
      clear: both;
      word-wrap: break-word;
    }
    .msg.client {
      background: #F77D0E;
      color: #fff;
      margin-left: auto;
      border-bottom-right-radius: 2px;
    }
    .msg.konselor {
      background: #e1e1e1;
      color: #000;
      margin-right: auto;
      border-bottom-left-radius: 2px;
    }
    .meta {
      display:block;
      font-size: 11px;
      color: #666;
      margin-top: 6px;
    }
    .chat-input {
      padding: 12px;
      border-top: 1px solid #ddd;
      background: #fff;
      display: flex;
    }
    .chat-input input {
      flex-grow: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      outline: none;
    }
    .chat-input button {
      margin-left: 10px;
      background: #0474BA;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 8px;
      cursor: pointer;
    }
    .chat-input button:hover { background: #00A7E1; }
    /* responsive */
    @media (max-width: 768px) {
      .sidebar { display: none; }
      body { flex-direction: column; height: auto; }
      .chat-area { height: calc(100vh - 60px); }
    }
	.sidebar {
  width: 260px;
  background: #fff;
  box-shadow: 2px 0 6px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  height: 100vh;
}
.chat-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
  height: 100vh;
}
.chat-box {
  flex-grow: 1;
  padding: 20px;
  overflow-y: auto;
  background: #fefefe;
}

  </style>
</head>
<body>
  <!-- Sidebar daftar kasus -->
  <!-- Sidebar daftar kasus -->
<div class="sidebar">
  <h3>Kasus Saya</h3>
  <a href="index.php" class="case-item" style="background:#f77d0e;color:#fff;font-weight:bold;text-align:center;">
    ⬅ Kembali ke Beranda
  </a>
  <div style="max-height:calc(100vh - 100px); overflow-y:auto;">
    <?php if ($cases && $cases->num_rows > 0): ?>
      <?php while ($c = $cases->fetch_assoc()): ?>
        <?php $activeClass = ($active_case_id == $c['case_id']) ? 'active' : ''; ?>
        <a href="dashboard.php?chat_case=<?= (int)$c['case_id'] ?>" 
           class="case-item <?= $activeClass ?>">
          <strong>#<?= (int)$c['case_id'] ?></strong><br>
          <?= htmlspecialchars($c['case_category']) ?><br>
          <small>Status: <?= htmlspecialchars($c['case_status']) ?></small>
        </a>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="padding:10px; text-align:center;">Belum ada kasus</p>
    <?php endif; ?>
  </div>
</div>

<!-- Chat area -->
<div class="chat-area">
  <?php if ($active_case_id): ?>
    <div class="chat-header">Chat Kasus #<?= (int)$active_case_id ?></div>
    <div class="chat-box" id="chatBox">
      <?php if ($messages && $messages->num_rows > 0): ?>
        <?php while($msg = $messages->fetch_assoc()): ?>
          <?php
            $senderRole = isset($msg['role']) ? $msg['role'] : '';
            $class = ($senderRole === 'client') ? 'client' : 'konselor';
            $time = isset($msg['created_at']) ? $msg['created_at'] : '';
          ?>
          <div class="msg <?= $class ?>">
            <?= nl2br(htmlspecialchars($msg['message'])) ?>
            <span class="meta"><?= htmlspecialchars($msg['nama'] ?? 'Unknown') ?> · <?= htmlspecialchars($time) ?></span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="color:#666">Belum ada pesan.</p>
      <?php endif; ?>
    </div>

    <!-- Form kirim pesan -->
    <form class="chat-input" method="POST" action="dashboard.php?chat_case=<?= (int)$active_case_id ?>">
      <input type="text" name="message" placeholder="Tulis pesan..." required autocomplete="off">
      <button type="submit" name="send_message">Kirim</button>
    </form>

  <?php else: ?>
    <div class="chat-header">Pilih Kasus untuk Memulai Chat</div>
    <div style="padding:20px;color:#fff;">
      <p style="color:#fff">Pilih salah satu kasus di sisi kiri untuk membuka ruang chat.</p>
    </div>
  <?php endif; ?>
</div>


  <script>
    // Auto scroll ke bawah chat setelah load
    const chatBox = document.getElementById("chatBox");
    if (chatBox) { chatBox.scrollTop = chatBox.scrollHeight; }
  </script>
</body>
</html>

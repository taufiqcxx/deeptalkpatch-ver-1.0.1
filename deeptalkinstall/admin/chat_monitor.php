<?php
include '../koneksi.php';

// Ambil daftar kasus
$cases = $conn->query("SELECT c.*, u1.nama AS client_name, u2.nama AS konselor_name 
                       FROM `case` c
                       LEFT JOIN user u1 ON c.case_client=u1.id
                       LEFT JOIN user u2 ON c.case_konselor=u2.id
                       ORDER BY c.case_id DESC");

$active_case_id = isset($_GET['view_case']) ? (int)$_GET['view_case'] : 0;
$active_case = null;
$messages = [];

if ($active_case_id) {
    $active_case = $conn->query("SELECT c.*, u1.nama AS client_name, u2.nama AS konselor_name 
                                 FROM `case` c
                                 LEFT JOIN user u1 ON c.case_client=u1.id
                                 LEFT JOIN user u2 ON c.case_konselor=u2.id
                                 WHERE c.case_id=$active_case_id")->fetch_assoc();

    $stmt = $conn->prepare("SELECT m.*, u.nama, u.role 
                            FROM messages m
                            LEFT JOIN user u ON m.sender_id=u.id
                            WHERE m.case_id=? 
                            ORDER BY m.created_at ASC");
    $stmt->bind_param("i", $active_case_id);
    $stmt->execute();
    $messages = $stmt->get_result();
}
?>

<div class="d-flex" style="height:100vh;">

  <!-- Sidebar Daftar Kasus -->
  <div class="border-end bg-white" style="width:300px; display:flex; flex-direction:column;">
    <h5 class="p-3 border-bottom bg-light">Daftar Kasus</h5>
    <div style="flex-grow:1; overflow-y:auto;">
      <?php if ($cases->num_rows > 0): ?>
        <?php while ($c = $cases->fetch_assoc()): ?>
          <a href="javascript:void(0)" 
             onclick="loadPage('chat_monitor.php?view_case=<?= $c['case_id'] ?>')" 
             class="d-block p-2 border-bottom <?= ($active_case_id == $c['case_id']) ? 'bg-primary text-white' : '' ?>">
            <strong>#<?= $c['case_id'] ?> - <?= $c['case_category'] ?></strong><br>
            <small>Client: <?= $c['client_name'] ?: '-' ?></small><br>
            <small>Konselor: <?= $c['konselor_name'] ?: '-' ?></small><br>
            <span class="badge bg-secondary"><?= $c['case_status'] ?></span>
          </a>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="p-3 text-muted">Belum ada kasus</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chat Area -->
  <div class="flex-grow-1 d-flex flex-column">
    <?php if ($active_case): ?>
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center bg-primary text-white p-2">
        <div>
          Kasus #<?= $active_case['case_id'] ?> - <?= $active_case['case_category'] ?>
          <span class="badge bg-light text-dark"><?= $active_case['case_status'] ?></span>
        </div>
        <div>
          <button class="btn btn-sm btn-success" onclick="loadPage('chat_monitor.php?view_case=<?= $active_case['case_id'] ?>')">🔄 Refresh</button>
        </div>
      </div>

      <!-- Chat Box -->
      <div class="flex-grow-1 bg-white p-3" style="overflow-y:auto;">
        <?php if ($messages->num_rows > 0): ?>
          <?php while($msg = $messages->fetch_assoc()): ?>
            <div class="mb-2 <?= $msg['role']=='konselor' ? 'text-end' : '' ?>">
              <div class="d-inline-block p-2 rounded <?= $msg['role']=='konselor' ? 'bg-warning text-dark' : 'bg-light' ?>">
                <?= htmlspecialchars($msg['message']) ?>
              </div><br>
              <small class="text-muted"><?= $msg['nama'] ?> - <?= $msg['created_at'] ?></small>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-muted">Belum ada percakapan.</p>
        <?php endif; ?>
      </div>

      <!-- Catatan Konselor -->
      <div class="bg-white p-2 border-top" style="max-height:120px; overflow-y:auto;">
        <h6>Catatan Konselor:</h6>
        <p class="mb-0"><?= !empty($active_case['case_notes']) ? nl2br(htmlspecialchars($active_case['case_notes'])) : '<i>Belum ada catatan.</i>' ?></p>
      </div>
    <?php else: ?>
      <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted">
        Pilih kasus dari daftar untuk melihat percakapan
      </div>
    <?php endif; ?>
  </div>

</div>
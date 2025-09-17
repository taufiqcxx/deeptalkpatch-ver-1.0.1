<?php
include '../koneksi.php';

// Ambil 20 notifikasi terbaru
$notif_sql = "
  (SELECT 'Kasus Baru' as type, case_category as detail, created_at as waktu 
   FROM `case` WHERE case_status='pending')
  UNION ALL
  (SELECT 'Kasus Selesai', case_category, created_at 
   FROM `case` WHERE case_status='finish')
  UNION ALL
  (SELECT 'User Baru', nama, created_at FROM user WHERE status_register='pending')
  UNION ALL
  (SELECT 'appeals Request', email, created_at FROM appeals)
  ORDER BY waktu DESC
  LIMIT 20
";
$notif_result = $conn->query($notif_sql);
?>

<div class="container">
  <h3 class="mb-3">🔔 Notifikasi Terbaru</h3>
  <div class="list-group">
    <?php if ($notif_result->num_rows > 0): ?>
      <?php while($n = $notif_result->fetch_assoc()): ?>
        <div class="list-group-item">
          <strong><?= $n['type'] ?>:</strong> <?= htmlspecialchars($n['detail']) ?>
          <br><small class="text-muted"><?= $n['waktu'] ?></small>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="list-group-item text-muted">Belum ada notifikasi</div>
    <?php endif; ?>
  </div>
</div>

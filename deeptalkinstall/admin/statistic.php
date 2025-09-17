<?php
include '../koneksi.php';

// Kasus per bulan (tahun berjalan)
$bulan = [];
$jumlah_bulan = [];
$res = $conn->query("SELECT MONTH(created_at) as bulan, COUNT(*) as total 
                     FROM `case` 
                     WHERE YEAR(created_at) = YEAR(CURDATE()) 
                     GROUP BY MONTH(created_at)");
for ($i=1; $i<=12; $i++) {
    $bulan[] = date("F", mktime(0,0,0,$i,1));
    $jumlah_bulan[] = 0;
}
while($row = $res->fetch_assoc()){
    $jumlah_bulan[$row['bulan']-1] = $row['total'];
}

// Kasus per tahun
$tahun = [];
$jumlah_tahun = [];
$res = $conn->query("SELECT YEAR(created_at) as tahun, COUNT(*) as total 
                     FROM `case` 
                     GROUP BY YEAR(created_at)");
while($row = $res->fetch_assoc()){
    $tahun[] = $row['tahun'];
    $jumlah_tahun[] = $row['total'];
}

// Kasus per kategori
$kategori = [];
$jumlah_kategori = [];
$res = $conn->query("SELECT case_category, COUNT(*) as total 
                     FROM `case` 
                     GROUP BY case_category");
while($row = $res->fetch_assoc()){
    $kategori[] = $row['case_category'];
    $jumlah_kategori[] = $row['total'];
}

// Kasus per konselor
$konselor = [];
$jumlah_konselor = [];
$res = $conn->query("SELECT u.nama, COUNT(*) as total 
                     FROM `case` c
                     LEFT JOIN user u ON c.case_konselor=u.id
                     WHERE u.role='konselor'
                     GROUP BY u.id");
while($row = $res->fetch_assoc()){
    $konselor[] = $row['nama'];
    $jumlah_konselor[] = $row['total'];
}
?>

<div class="row">
  <div class="col-md-6 mb-4">
    <div class="chart-box">
      <h5>Kasus per Bulan</h5>
      <canvas id="chartBulan"></canvas>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="chart-box">
      <h5>Kasus per Tahun</h5>
      <canvas id="chartTahun"></canvas>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="chart-box">
      <h5>Kategori Kasus</h5>
      <canvas id="chartKategori"></canvas>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="chart-box">
      <h5>Kasus per Konselor</h5>
      <canvas id="chartKonselor"></canvas>
    </div>
  </div>
</div>
<style>
  #chartKategori, #chartKonselor {
    max-width: 300px;
    max-height: 300px;
    margin: 0 auto;
  }
</style>

<script>
new Chart(document.getElementById('chartBulan'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($bulan) ?>,
        datasets: [{ 
            label: 'Jumlah Kasus',
            data: <?= json_encode($jumlah_bulan) ?>,
            backgroundColor: '#0474BA'
        }]
    }
});

new Chart(document.getElementById('chartTahun'), {
    type: 'line',
    data: {
        labels: <?= json_encode($tahun) ?>,
        datasets: [{ 
            label: 'Jumlah Kasus',
            data: <?= json_encode($jumlah_tahun) ?>,
            backgroundColor: '#F77D0E',
            borderColor: '#F77D0E'
        }]
    }
});

new Chart(document.getElementById('chartKategori'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($kategori) ?>,
        datasets: [{
            data: <?= json_encode($jumlah_kategori) ?>,
            backgroundColor: ['#0474BA','#00A7E1','#F77D0E','#28a745','#6f42c1']
        }]
    }
});

new Chart(document.getElementById('chartKonselor'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($konselor) ?>,
        datasets: [{
            data: <?= json_encode($jumlah_konselor) ?>,
            backgroundColor: ['#00A7E1','#F77D0E','#ffc107','#28a745','#6f42c1']
        }]
    }
});
</script>

<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email  = $_POST['email'];
    $reason = $_POST['reason'];

    $cek = $conn->query("SELECT * FROM user WHERE email='$email' AND status_register='banned'");
    if ($cek->num_rows > 0) {
        // simpan appeal ke tabel baru misalnya 'appeals'
        $sql = "INSERT INTO appeals (email, reason, created_at) VALUES ('$email', '$reason', NOW())";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Permohonan banding telah dikirim, tunggu respon admin.'); window.location='index.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Email tidak ditemukan atau tidak berstatus banned.'); window.location='appeal.php';</script>";
    }
}
$conn->close();
?>

<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $cek = $conn->query("SELECT * FROM user WHERE email='$email'");
    if ($cek->num_rows > 0) {
        // di sini bisa ditambahkan fitur kirim email token reset password
        echo "<script>alert('Link reset password telah dikirim ke email Anda (dummy).'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Email tidak terdaftar!'); window.location='forgot.php';</script>";
    }
}
$conn->close();
?>

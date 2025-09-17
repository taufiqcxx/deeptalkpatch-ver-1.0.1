<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // cek apakah email sudah terdaftar
    $cek = $conn->query("SELECT * FROM user WHERE email='$email'");
    if ($cek->num_rows > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
    } else {
        $sql = "INSERT INTO user (nama, email, phone, password, role, status_register) 
                VALUES ('$nama', '$email', '$phone', '$password', 'client', 'pending')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registrasi berhasil, menunggu persetujuan admin'); window.location='index.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
$conn->close();
?>

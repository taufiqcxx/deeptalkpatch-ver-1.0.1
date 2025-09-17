<?php
// install/index.php

// Jika koneksi.php sudah ada, langsung ke login
if (file_exists(__DIR__ . '/../koneksi.php')) {
    header("Location: ../index.php");
    exit;
} else {
    // Kalau belum ada, arahkan ke wizard
    header("Location: install.php");
    exit;
}
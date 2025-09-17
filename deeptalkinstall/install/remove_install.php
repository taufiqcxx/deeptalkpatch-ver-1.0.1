<?php
// Hapus folder install setelah selesai
function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if (is_dir($file)) rrmdir($file); else unlink($file);
    }
    rmdir($dir);
}

$install_dir = __DIR__;
rrmdir($install_dir);

// Redirect ke index root
header("Location: ../index.php");
exit;
?>

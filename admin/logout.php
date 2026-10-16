<?php
session_start();
// Hapus semua data sesi admin yang sedang aktif
session_unset();
// Hancurkan sesi
session_destroy();

// Arahkan kembali ke halaman login admin di luar folder
header("Location: ../index.php");
exit;
?>
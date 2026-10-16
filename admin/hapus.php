<?php
session_start();
include '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login_admin.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_alat = (int)$_GET['id'];
    
    $query = "DELETE FROM alat WHERE id_alat = '$id_alat'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Alat berhasil dihapus dari sistem!'); window.location='dashboard_admin.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus alat: " . mysqli_error($koneksi) . "'); window.location='dashboard_admin.php';</script>";
        exit;
    }
} else {
    header("Location: dashboard_admin.php");
    exit;
}
?>
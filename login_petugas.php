<?php
session_start();
include 'koneksi.php';

// Jika petugas sudah terlanjur login, langsung lempar ke dashboard
if (isset($_SESSION['id_petugas']) && $_SESSION['role'] == 'petugas') {
    header("Location: petugas/dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $cek = mysqli_query($koneksi, "SELECT * FROM petugas WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $row = mysqli_fetch_assoc($cek);
        
        // Pengecekan password (mendukung teks biasa maupun MD5 sesuai database)
        if ($password == $row['password'] || md5($password) == $row['password']) {
            $_SESSION['id_petugas'] = $row['id_petugas'];
            $_SESSION['nama'] = $row['nama_petugas'];
            $_SESSION['role'] = 'petugas';
            
            header("Location: petugas/dashboard.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username petugas tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - Sarpras Sekolah</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: { 50: '#FDFBF7', 100: '#F7F3E9', 200: '#EDE4D3' },
                        rosegold: { 300: '#E8B4B8', 400: '#D99B9E', 500: '#C88386', 600: '#B06C70' },
                        chocolate: '#5C3A31'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-cream-50 text-chocolate min-h-screen flex items-center justify-center font-sans px-4">

    <div class="bg-white rounded-2xl max-w-md w-full p-8 border border-cream-200 shadow-xl">
        <!-- Header Login -->
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-rosegold-400 rounded-full flex items-center justify-center text-white font-bold text-xl mx-auto mb-3 shadow-sm">
                👨‍💼
            </div>
            <h2 class="text-2xl font-bold text-chocolate">Login Petugas</h2>
            <p class="text-xs text-rosegold-600 mt-1">Panel Verifikasi & Pengelolaan Peminjaman Alat</p>
        </div>

        <!-- Pesan Error -->
        <?php if ($error) : ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl text-center">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Username Petugas</label>
                <input type="text" name="username" required placeholder="Masukkan username" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
            </div>

            <button type="submit" name="login" class="w-full bg-rosegold-500 hover:bg-rosegold-600 text-white font-medium py-2.5 rounded-xl text-sm transition shadow-sm mt-2">
                Masuk ke Panel Petugas
            </button>
        </form>

        <!-- Navigasi Kembali -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-xs text-rosegold-600 hover:underline font-medium">
                &larr; Kembali ke Beranda Peminjaman
            </a>
        </div>
    </div>

</body>
</html>
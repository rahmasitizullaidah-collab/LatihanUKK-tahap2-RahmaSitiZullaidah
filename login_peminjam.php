<?php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Menggunakan MD5 sesuai standar sistem

    $query = mysqli_query($koneksi, "SELECT * FROM peminjam WHERE username = '$username' AND password = '$password'");
    
    if (mysqli_num_rows($query) == 1) {
        $peminjam = mysqli_fetch_assoc($query);
        $_SESSION['id_peminjam'] = $peminjam['id_peminjam'];
        $_SESSION['nama_peminjam'] = $peminjam['nama_peminjam'];
        $_SESSION['role'] = 'peminjam';

        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Peminjam - Sarpras Sekolah</title>
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
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-cream-100">
            <div class="w-12 h-12 bg-rosegold-500 text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-sm">
                👤
            </div>
            <div>
                <h2 class="text-xl font-bold text-chocolate">Login Peminjam</h2>
                <p class="text-xs text-rosegold-600 mt-0.5">Masuk untuk meminjam fasilitas sarpras.</p>
            </div>
        </div>

        <?php if ($error) : ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl text-center">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
            </div>

            <div class="pt-2">
                <button type="submit" name="login" class="w-full bg-rosegold-500 hover:bg-rosegold-600 text-white font-medium py-2.5 rounded-xl text-sm transition shadow-sm">
                    Login Masuk
                </button>
            </div>
        </form>
    </div>

</body>
</html>
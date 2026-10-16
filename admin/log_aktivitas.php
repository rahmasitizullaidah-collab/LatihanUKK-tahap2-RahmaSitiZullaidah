<?php
session_start();
include '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login_admin.php");
    exit;
}

// Ambil riwayat log aktivitas dijoin dengan tabel petugas/admin (opsional, sesuaikan jika tabel admin berbeda)
// Atau ambil langsung dari tabel log_aktivitas:
$query_log = mysqli_query($koneksi, "
    SELECT l.*, p.nama_petugas AS nama_admin 
    FROM log_aktivitas l 
    LEFT JOIN petugas p ON l.id_admin = p.id_petugas 
    ORDER BY l.waktu DESC LIMIT 100
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Sarpras Sekolah</title>
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
<body class="bg-cream-50 text-chocolate min-h-screen font-sans pb-12">

    <!-- Header Navbar -->
    <header class="bg-chocolate text-cream-50 border-b border-cream-200 sticky top-0 z-20 shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-rosegold-400 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    📜
                </div>
                <div>
                    <h1 class="text-xl font-bold">Log Aktivitas Sistem</h1>
                    <p class="text-xs text-rosegold-300">Riwayat Perubahan & Aktivitas Admin</p>
                </div>
            </div>
            <a href="dashboard_admin.php" class="text-xs font-semibold bg-cream-100 hover:bg-cream-200 text-chocolate px-4 py-2 rounded-xl transition shadow-sm">
                ← Kembali ke Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
        <section class="bg-white rounded-2xl border border-cream-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-cream-100">
                <div>
                    <h2 class="text-lg font-bold text-chocolate">Catatan Aktivitas Terbaru</h2>
                    <p class="text-xs text-chocolate/70">Menampilkan 100 riwayat aktivitas admin pada sistem.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-cream-200">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-cream-100 text-chocolate text-xs uppercase tracking-wider border-b border-cream-200">
                            <th class="p-3 w-16">No</th>
                            <th class="p-3 w-48">Waktu</th>
                            <th class="p-3 w-40">ID / Admin</th>
                            <th class="p-3">Aktivitas / Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($query_log) > 0) :
                            while ($log = mysqli_fetch_assoc($query_log)) :
                        ?>
                            <tr class="hover:bg-cream-50 transition">
                                <td class="p-3 font-mono text-xs text-chocolate/60"><?= $no++; ?></td>
                                <td class="p-3 text-xs text-chocolate/80 whitespace-nowrap">
                                    <?= date('d/m/Y H:i:s', strtotime($log['waktu'])); ?> WIB
                                </td>
                                <td class="p-3 font-bold text-chocolate">
                                    Admin #<?= $log['id_admin'] ? $log['id_admin'] : 'System'; ?>
                                </td>
                                <td class="p-3 text-chocolate/90">
                                    <?= htmlspecialchars($log['aktivitas']); ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else : 
                        ?>
                            <tr>
                                <td colspan="4" class="p-6 text-center text-chocolate/50 text-sm">Belum ada log aktivitas tercatat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>
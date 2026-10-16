<?php
include 'koneksi.php';

$keyword = "";
$result_peminjaman = null;

// Proses Batalkan Peminjaman
if (isset($_POST['batalkan_id'])) {
    $id_peminjaman = (int)$_POST['batalkan_id'];
    $keyword = mysqli_real_escape_string($koneksi, $_POST['keyword_asal']);

    // Pastikan statusnya masih 'Menunggu' sebelum dihapus agar aman
    $cek_status = mysqli_query($koneksi, "SELECT status FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    $data_status = mysqli_fetch_assoc($cek_status);

    if ($data_status && $data_status['status'] == 'Menunggu') {
        // Hapus detail peminjaman dulu (karena ada foreign key)
        mysqli_query($koneksi, "DELETE FROM detail_peminjaman WHERE id_peminjaman = '$id_peminjaman'");
        // Hapus data peminjaman utamanya
        mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");

        echo "<script>alert('Pengajuan peminjaman berhasil dibatalkan.'); window.location='status_pinjam.php?keyword=" . urlencode($keyword) . "&cari=1';</script>";
        exit;
    }
}

if (isset($_GET['cari']) || isset($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    // Query untuk mencari data peminjaman berdasarkan nama peminjam
    $query = "SELECT p.*, pm.nama_peminjam, pm.jenis_peminjam, pm.kelas, a.nama_alat, a.kode_alat, dp.jumlah_pinjam 
              FROM peminjaman p
              JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
              JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
              JOIN alat a ON dp.id_alat = a.id_alat
              WHERE pm.nama_peminjam LIKE '%$keyword%'
              ORDER BY p.id_peminjaman DESC";
    $result_peminjaman = mysqli_query($koneksi, $query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Peminjaman - Sarpras Sekolah</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: {
                            50: '#FDFBF7',
                            100: '#F7F3E9',
                            200: '#EDE4D3',
                        },
                        rosegold: {
                            300: '#E8B4B8',
                            400: '#D99B9E',
                            500: '#C88386',
                            600: '#B06C70',
                        },
                        chocolate: '#5C3A31'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-cream-50 text-chocolate min-h-screen font-sans">

    <!-- Header / Navibar -->
    <header class="bg-cream-100 border-b border-cream-200 sticky top-0 z-10 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-rosegold-400 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    S
                </div>
                <div>
                    <h1 class="text-xl font-bold text-chocolate">Sarpras Sekolah</h1>
                    <p class="text-xs text-rosegold-600">Cek Status Pengajuan Peminjaman</p>
                </div>
            </div>
            
            <a href="index.php" class="text-xs font-semibold border border-rosegold-400 text-rosegold-600 hover:bg-rosegold-500 hover:text-white px-3 py-2 rounded-xl transition shadow-sm bg-white">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="max-w-4xl mx-auto px-6 py-10">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-chocolate mb-2">Cek Status Peminjamanmu</h2>
            <p class="text-sm text-chocolate/70">Masukkan nama lengkap kamu untuk melihat daftar pengajuan dan status alat sarpras.</p>
        </div>

        <!-- Form Pencarian -->
        <div class="bg-white p-6 rounded-2xl border border-cream-200 shadow-sm mb-8">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-3">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword); ?>" required placeholder="Ketik nama lengkap kamu di sini..." 
                       class="flex-1 px-4 py-3 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                <button type="submit" name="cari" class="bg-rosegold-500 hover:bg-rosegold-600 text-white font-medium px-6 py-3 rounded-xl transition text-sm shadow-sm">
                    Cari Status
                </button>
            </form>
        </div>

        <!-- Hasil Pencarian -->
        <?php if (isset($_GET['cari']) || isset($_GET['keyword'])) : ?>
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-chocolate">Hasil Pencarian untuk: "<span class="text-rosegold-600"><?= htmlspecialchars($keyword); ?></span>"</h3>

                <?php if ($result_peminjaman && mysqli_num_rows($result_peminjaman) > 0) : ?>
                    <div class="space-y-4">
                        <?php while ($row = mysqli_fetch_assoc($result_peminjaman)) : ?>
                            <div class="bg-white rounded-2xl p-6 border border-cream-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-cream-100 text-rosegold-600 border border-rosegold-300/30">
                                            <?= $row['jenis_peminjam']; ?> <?php if($row['kelas']) echo " - " . $row['kelas']; ?>
                                        </span>
                                        <span class="text-xs text-chocolate/50 font-mono">ID Pinjam: #<?= $row['id_peminjaman']; ?></span>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-lg font-bold text-chocolate"><?= $row['nama_alat']; ?> <span class="text-sm font-normal text-rosegold-600">(<?= $row['jumlah_pinjam']; ?> Unit)</span></h4>
                                        <p class="text-xs text-chocolate/70 mt-1"><strong>Tujuan:</strong> <?= $row['tujuan']; ?></p>
                                    </div>

                                    <div class="text-xs text-chocolate/60 flex flex-wrap gap-4 pt-1">
                                        <span>📅 Pinjam: <?= $row['tanggal_pinjam']; ?> (<?= $row['jam_pinjam']; ?>)</span>
                                        <span>📅 Rencana Kembali: <?= $row['tanggal_rencana_kembali']; ?> (<?= $row['jam_rencana_kembali']; ?>)</span>
                                    </div>
                                </div>

                                <!-- Badge Status & Tombol Batalkan -->
                                <div class="flex flex-col sm:flex-row items-center gap-2">
                                    <?php 
                                        $status = $row['status'];
                                        $badge_class = "bg-cream-100 text-chocolate border-cream-200";
                                        if ($status == 'Menunggu') $badge_class = "bg-amber-50 text-amber-700 border-amber-200";
                                        else if ($status == 'Disetujui') $badge_class = "bg-emerald-50 text-emerald-700 border-emerald-200";
                                        else if ($status == 'Ditolak') $badge_class = "bg-rose-50 text-rose-700 border-rose-200";
                                        else if ($status == 'Selesai') $badge_class = "bg-sky-50 text-sky-700 border-sky-200";
                                    ?>
                                    <span class="px-4 py-2 rounded-xl text-xs font-bold border block text-center <?= $badge_class; ?>">
                                        <?= $status; ?>
                                    </span>

                                    <!-- Tombol Batalkan hanya muncul jika status masih 'Menunggu' -->
                                    <?php if ($status == 'Menunggu') : ?>
                                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan pinjam ini?');">
                                            <input type="hidden" name="batalkan_id" value="<?= $row['id_peminjaman']; ?>">
                                            <input type="hidden" name="keyword_asal" value="<?= htmlspecialchars($keyword); ?>">
                                            <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold bg-rose-100 text-rose-700 hover:bg-rose-200 border border-rose-300 transition shadow-sm">
                                                Batalkan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-cream-200">
                        <p class="text-chocolate/60 text-sm">Tidak ditemukan data peminjaman dengan nama tersebut. Pastikan ejaan nama sudah benar.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

</body>
</html>
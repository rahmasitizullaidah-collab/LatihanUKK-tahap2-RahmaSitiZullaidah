<?php
session_start();
// Karena berada di dalam folder petugas, naik 1 level untuk memanggil koneksi.php
include '../koneksi.php';

// Cek apakah petugas sudah login
if (!isset($_SESSION['id_petugas']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../login_petugas.php");
    exit;
}

$id_petugas = $_SESSION['id_petugas'];
$nama_petugas = $_SESSION['nama'];

// PROSES AKSI (SETUJUI / TOLAK / SELESAI)
if (isset($_POST['aksi'])) {
    $id_peminjaman = (int)$_POST['id_peminjaman'];
    $aksi = $_POST['aksi'];

    if ($aksi == 'Setujui') {
        // 1. Ambil data alat & jumlah pinjam dari detail peminjaman
        $q_detail = mysqli_query($koneksi, "SELECT * FROM detail_peminjaman WHERE id_peminjaman = '$id_peminjaman'");
        $d_detail = mysqli_fetch_assoc($q_detail);
        $id_alat = $d_detail['id_alat'];
        $jumlah_pinjam = $d_detail['jumlah_pinjam'];

        // 2. Cek stok alat saat ini
        $q_alat = mysqli_query($koneksi, "SELECT jumlah FROM alat WHERE id_alat = '$id_alat'");
        $d_alat = mysqli_fetch_assoc($q_alat);
        
        if ($d_alat['jumlah'] >= $jumlah_pinjam) {
            // Kurangi stok alat
            mysqli_query($koneksi, "UPDATE alat SET jumlah = jumlah - $jumlah_pinjam WHERE id_alat = '$id_alat'");
            
            // Update status peminjaman menjadi Disetujui dan catat id_petugas yang memproses
            mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Disetujui', id_petugas = '$id_petugas' WHERE id_peminjaman = '$id_peminjaman'");
            
            echo "<script>alert('Peminjaman berhasil disetujui, stok alat dikurangi.'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Gagal! Stok alat tidak mencukupi.'); window.location='dashboard.php';</script>";
        }

    } elseif ($aksi == 'Tolak') {
        // Update status menjadi Ditolak
        mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Ditolak', id_petugas = '$id_petugas' WHERE id_peminjaman = '$id_peminjaman'");
        echo "<script>alert('Peminjaman ditolak.'); window.location='dashboard.php';</script>";

    } elseif ($aksi == 'Selesai') {
        // 1. Ambil data alat & jumlah pinjam untuk dikembalikan stoknya
        $q_detail = mysqli_query($koneksi, "SELECT * FROM detail_peminjaman WHERE id_peminjaman = '$id_peminjaman'");
        $d_detail = mysqli_fetch_assoc($q_detail);
        $id_alat = $d_detail['id_alat'];
        $jumlah_pinjam = $d_detail['jumlah_pinjam'];

        // 2. Kembalikan stok alat
        mysqli_query($koneksi, "UPDATE alat SET jumlah = jumlah + $jumlah_pinjam WHERE id_alat = '$id_alat'");

        // 3. Update status peminjaman menjadi Selesai
        mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Selesai', id_petugas = '$id_petugas' WHERE id_peminjaman = '$id_peminjaman'");
        
        echo "<script>alert('Barang telah dikembalikan, stok alat bertambah kembali.'); window.location='dashboard.php';</script>";
    }
}

// Ambil data semua peminjaman masuk
$query_peminjaman = mysqli_query($koneksi, "
    SELECT p.*, pm.nama_peminjam, pm.jenis_peminjam, pm.kelas, a.nama_alat, a.kode_alat, dp.jumlah_pinjam, pt.nama_petugas 
    FROM peminjaman p
    JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN alat a ON dp.id_alat = a.id_alat
    LEFT JOIN petugas pt ON p.id_petugas = pt.id_petugas
    ORDER BY FIELD(p.status, 'Menunggu', 'Disetujui', 'Ditolak', 'Selesai'), p.id_peminjaman DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Sarpras Sekolah</title>
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
<body class="bg-cream-50 text-chocolate min-h-screen font-sans">

    <!-- Header / Navbar Petugas -->
    <header class="bg-cream-100 border-b border-cream-200 sticky top-0 z-10 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-rosegold-400 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    P
                </div>
                <div>
                    <h1 class="text-xl font-bold text-chocolate">Dashboard Petugas</h1>
                    <p class="text-xs text-rosegold-600">Selamat datang, <?= htmlspecialchars($nama_petugas); ?></p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Tombol Cetak Laporan -->
                <a href="cetak_laporan.php" target="_blank" class="text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-4 py-2 rounded-xl transition shadow-sm border border-emerald-200 flex items-center gap-1">
                    Cetak Laporan
                </a>
                
                <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari sistem?');" class="text-xs font-semibold bg-rose-100 text-rose-700 hover:bg-rose-200 px-4 py-2 rounded-xl transition shadow-sm border border-rose-200">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-chocolate">Daftar Pengajuan Peminjaman</h2>
                <p class="text-xs text-chocolate/70 mt-1">Kelola dan verifikasi pengajuan peminjaman alat sarana prasarana sekolah.</p>
            </div>
        </div>

        <!-- Tabel Peminjaman -->
        <div class="bg-white rounded-2xl border border-cream-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-cream-100 text-chocolate text-xs uppercase tracking-wider border-b border-cream-200">
                            <th class="p-4">No</th>
                            <th class="p-4">Peminjam</th>
                            <th class="p-4">Alat & Jumlah</th>
                            <th class="p-4">Jadwal Pinjam</th>
                            <th class="p-4">Tujuan</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Aksi / Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($query_peminjaman) > 0) :
                            while ($row = mysqli_fetch_assoc($query_peminjaman)) :
                                // Format jam agar selalu ada detiknya (jika dari database hanya HH:MM)
                                $jam_pinjam = (strlen($row['jam_pinjam']) == 5) ? $row['jam_pinjam'] . ':00' : $row['jam_pinjam'];
                                $jam_kembali = (strlen($row['jam_rencana_kembali']) == 5) ? $row['jam_rencana_kembali'] . ':00' : $row['jam_rencana_kembali'];
                        ?>
                            <tr class="hover:bg-cream-50 transition">
                                <td class="p-4 font-mono text-xs text-chocolate/60"><?= $no++; ?></td>
                                <td class="p-4">
                                    <p class="font-bold text-chocolate"><?= htmlspecialchars($row['nama_peminjam']); ?></p>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-cream-100 text-rosegold-600 font-medium">
                                        <?= $row['jenis_peminjam']; ?> <?php if($row['kelas']) echo " - " . $row['kelas']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <p class="font-semibold text-chocolate"><?= htmlspecialchars($row['nama_alat']); ?></p>
                                    <span class="text-xs text-rosegold-600 font-medium"><?= $row['jumlah_pinjam']; ?> Unit</span>
                                </td>
                                <td class="p-4 text-xs text-chocolate/80">
                                    <p><strong>Mulai:</strong> <?= $row['tanggal_pinjam']; ?> (<?= $jam_pinjam; ?>)</p>
                                    <p><strong>Kembali:</strong> <?= $row['tanggal_rencana_kembali']; ?> (<?= $jam_kembali; ?>)</p>
                                </td>
                                <td class="p-4 text-xs text-chocolate/70 max-w-xs"><?= htmlspecialchars($row['tujuan']); ?></td>
                                <td class="p-4 text-center">
                                    <?php 
                                        $status = $row['status'];
                                        $badge = "bg-cream-100 text-chocolate";
                                        if ($status == 'Menunggu') $badge = "bg-amber-50 text-amber-700 border border-amber-200";
                                        else if ($status == 'Disetujui') $badge = "bg-emerald-50 text-emerald-700 border border-emerald-200";
                                        else if ($status == 'Ditolak') $badge = "bg-rose-50 text-rose-700 border border-rose-200";
                                        else if ($status == 'Selesai') $badge = "bg-sky-50 text-sky-700 border border-sky-200";
                                    ?>
                                    <span class="px-3 py-1 rounded-xl text-xs font-bold inline-block <?= $badge; ?>">
                                        <?= $status; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <?php if ($status == 'Menunggu') : ?>
                                            <form action="" method="POST" class="flex gap-1">
                                                <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                <button type="submit" name="aksi" value="Setujui" onclick="return confirm('Setujui peminjaman ini? Stok alat akan berkurang.');" 
                                                        class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                                    ACC
                                                </button>
                                                <button type="submit" name="aksi" value="Tolak" onclick="return confirm('Tolak peminjaman ini?');" 
                                                        class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                                    Tolak
                                                </button>
                                            </form>
                                        <?php elseif ($status == 'Disetujui') : ?>
                                            <form action="" method="POST">
                                                <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman']; ?>">
                                                <button type="submit" name="aksi" value="Selesai" onclick="return confirm('Selesaikan peminjaman ini dan kembalikan stok alat?');" 
                                                        class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                                    Barang Kembali (Selesai)
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($row['nama_petugas']) : ?>
                                            <span class="text-[10px] text-chocolate/50">Oleh: <?= htmlspecialchars($row['nama_petugas']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else :
                        ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-chocolate/50 text-sm">Belum ada pengajuan peminjaman alat masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
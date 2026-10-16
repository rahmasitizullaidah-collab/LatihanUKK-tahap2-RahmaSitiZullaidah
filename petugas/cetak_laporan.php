<?php
session_start();
// Naik 1 level untuk memanggil koneksi.php
include '../koneksi.php';

// Cek apakah petugas sudah login
if (!isset($_SESSION['id_petugas']) || $_SESSION['role'] != 'petugas') {
    header("Location: ../login_petugas.php");
    exit;
}

$nama_petugas = $_SESSION['nama'];

// Ambil data laporan HANYA untuk peminjaman yang masuk (Menunggu, Disetujui, Selesai) 
// dan mengecualikan yang Ditolak jika ingin fokus pada orang yang meminjam saja
$query_laporan = mysqli_query($koneksi, "
    SELECT p.*, pm.nama_peminjam, pm.jenis_peminjam, pm.kelas, a.nama_alat, a.kode_alat, dp.jumlah_pinjam, pt.nama_petugas 
    FROM peminjaman p
    JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN alat a ON dp.id_alat = a.id_alat
    LEFT JOIN petugas pt ON p.id_petugas = pt.id_petugas
    WHERE p.status != 'Ditolak'
    ORDER BY p.id_peminjaman DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Peminjam Alat - Sarpras Sekolah</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        chocolate: '#5C3A31'
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="bg-white text-chocolate font-sans p-8">

    <!-- Tombol Aksi Cetak (Hilang saat diprint) -->
    <div class="no-print max-w-5xl mx-auto mb-6 flex justify-between items-center bg-amber-50 p-4 rounded-xl border border-amber-200">
        <p class="text-sm font-medium text-amber-800">Pratinjau Laporan Data Peminjam Alat</p>
        <div class="space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow transition">
                Cetak / Simpan PDF
            </button>
            <a href="dashboard.php" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-xs font-semibold shadow transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Header Laporan -->
    <div class="max-w-5xl mx-auto text-center border-b-2 border-chocolate pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase tracking-wide">Laporan Data Peminjam Alat Sarpras</h1>
        <h2 class="text-lg font-semibold text-chocolate/80">Sekolah</h2>
        <p class="text-xs text-chocolate/60 mt-1">Dicetak oleh: <?= htmlspecialchars($nama_petugas); ?> | Tanggal: <?= date('d-m-Y H:i:s'); ?></p>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="max-w-5xl mx-auto">
        <table class="w-full text-left border-collapse text-xs border border-chocolate/30">
            <thead>
                <tr class="bg-chocolate text-white uppercase tracking-wider">
                    <th class="p-2.5 border border-chocolate/30 text-center">No</th>
                    <th class="p-2.5 border border-chocolate/30">Nama Peminjam</th>
                    <th class="p-2.5 border border-chocolate/30">Kelas / Jenis</th>
                    <th class="p-2.5 border border-chocolate/30">Alat Dipinjam & Jumlah</th>
                    <th class="p-2.5 border border-chocolate/30">Jadwal Pinjam</th>
                    <th class="p-2.5 border border-chocolate/30 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($query_laporan) > 0) :
                    while ($row = mysqli_fetch_assoc($query_laporan)) :
                ?>
                    <tr class="border-b border-chocolate/20">
                        <td class="p-2.5 border border-chocolate/20 text-center font-mono"><?= $no++; ?></td>
                        <td class="p-2.5 border border-chocolate/20 font-bold">
                            <?= htmlspecialchars($row['nama_peminjam']); ?>
                        </td>
                        <td class="p-2.5 border border-chocolate/20">
                            <?= $row['jenis_peminjam']; ?> <?php if($row['kelas']) echo " - " . $row['kelas']; ?>
                        </td>
                        <td class="p-2.5 border border-chocolate/20">
                            <p class="font-semibold"><?= htmlspecialchars($row['nama_alat']); ?></p>
                            <span class="text-[10px] text-chocolate/70"><?= $row['jumlah_pinjam']; ?> Unit</span>
                        </td>
                        <td class="p-2.5 border border-chocolate/20">
                            <p><strong>Pinjam:</strong> <?= $row['tanggal_pinjam']; ?></p>
                            <p><strong>Rencana Kembali:</strong> <?= $row['tanggal_rencana_kembali']; ?></p>
                        </td>
                        <td class="p-2.5 border border-chocolate/20 text-center font-semibold">
                            <?= $row['status']; ?>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else :
                ?>
                    <tr>
                        <td colspan="6" class="p-6 text-center text-chocolate/50 italic">Tidak ada data peminjam aktif saat ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tanda Tangan / Footer Pengesahan -->
        <div class="mt-12 flex justify-end">
            <div class="text-center w-64">
                <p class="text-xs">Mengetahui,</p>
                <p class="text-xs font-bold mb-16">Petugas Sarpras</p>
                <p class="text-xs font-bold underline"><?= htmlspecialchars($nama_petugas); ?></p>
            </div>
        </div>
    </div>

</body>
</html>
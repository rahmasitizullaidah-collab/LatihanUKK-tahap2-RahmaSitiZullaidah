<?php
session_start();
include '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login_admin.php");
    exit;
}

$error = "";

// Ambil data kategori terlebih dahulu untuk validasi
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
$jumlah_kategori = mysqli_num_rows($query_kategori);

// Proses Simpan Data Barang Baru
if (isset($_POST['tambah'])) {
    $kode_alat   = mysqli_real_escape_string($koneksi, $_POST['kode_alat']);
    $nama_alat   = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $id_kategori = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : 0;
    $jumlah      = (int)$_POST['jumlah'];

    if ($id_kategori <= 0) {
        $error = "Silakan pilih kategori alat terlebih dahulu! (Pastikan data kategori sudah dibuat di database).";
    } else {
        // Cek apakah kode alat sudah terdaftar
        $cek_kode = mysqli_query($koneksi, "SELECT * FROM alat WHERE kode_alat = '$kode_alat'");
        if (mysqli_num_rows($cek_kode) > 0) {
            $error = "Kode alat '{$kode_alat}' sudah terdaftar! Gunakan kode yang berbeda.";
        } else {
            $query = "INSERT INTO alat (kode_alat, nama_alat, id_kategori, jumlah) VALUES ('$kode_alat', '$nama_alat', '$id_kategori', '$jumlah')";
            if (mysqli_query($koneksi, $query)) {
                echo "<script>alert('Alat/Produk baru berhasil ditambahkan!'); window.location='dashboard_admin.php';</script>";
                exit;
            } else {
                $error = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk / Alat - Admin Sarpras</title>
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
<body class="bg-cream-50 text-chocolate min-h-screen flex items-center justify-center font-sans px-4 py-8">

    <div class="bg-white rounded-2xl max-w-lg w-full p-8 border border-cream-200 shadow-xl">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-cream-100">
            <div class="w-12 h-12 bg-chocolate text-cream-50 rounded-xl flex items-center justify-center font-bold text-xl shadow-sm">
                📦
            </div>
            <div>
                <h2 class="text-xl font-bold text-chocolate">Tambah Produk / Alat Baru</h2>
                <p class="text-xs text-rosegold-600 mt-0.5">Masukkan detail inventaris sarana prasarana sekolah.</p>
            </div>
        </div>

        <?php if ($error) : ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl text-center">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($jumlah_kategori == 0) : ?>
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl">
                <strong>Perhatian:</strong> Belum ada data kategori di database kamu. Silakan masukkan data ke tabel <code>kategori</code> melalui phpMyAdmin terlebih dahulu agar produk dapat disimpan.
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Kode Alat / Produk</label>
                <input type="text" name="kode_alat" required placeholder="Contoh: ALT-001" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Alat / Produk</label>
                <input type="text" name="nama_alat" required placeholder="Contoh: Proyektor Epson EB-X450" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Kategori Alat</label>
                <select name="id_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                    // Reset pointer query kategori
                    mysqli_data_seek($query_kategori, 0);
                    while ($kat = mysqli_fetch_assoc($query_kategori)) : 
                    ?>
                        <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Jumlah Stok Awal (Unit)</label>
                <input type="number" name="jumlah" required min="0" placeholder="Contoh: 15" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="dashboard_admin.php" class="px-5 py-2.5 bg-cream-100 hover:bg-cream-200 text-chocolate rounded-xl text-xs font-semibold transition">
                    Batal & Kembali
                </a>
                <button type="submit" name="tambah" class="px-5 py-2.5 bg-chocolate hover:bg-chocolate/90 text-cream-50 font-medium rounded-xl text-xs transition shadow-sm">
                    Simpan Produk Baru
                </button>
            </div>
        </form>
    </div>

</body>
</html>
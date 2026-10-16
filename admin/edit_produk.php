<?php
session_start();
include '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login_admin.php");
    exit;
}

$error = "";
$id_alat = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data alat berdasarkan ID
$query_data = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_alat'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    echo "<script>alert('Data alat tidak ditemukan!'); window.location='dashboard_admin.php';</script>";
    exit;
}

// Proses Update Data
if (isset($_POST['update'])) {
    $kode_alat   = mysqli_real_escape_string($koneksi, $_POST['kode_alat']);
    $nama_alat   = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $id_kategori = (int)$_POST['id_kategori'];
    $jumlah      = (int)$_POST['jumlah'];

    $query = "UPDATE alat SET kode_alat = '$kode_alat', nama_alat = '$nama_alat', id_kategori = '$id_kategori', jumlah = '$jumlah' WHERE id_alat = '$id_alat'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data alat berhasil diperbarui!'); window.location='dashboard_admin.php';</script>";
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}

$data_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk / Alat - Admin Sarpras</title>
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
            <div class="w-12 h-12 bg-amber-500 text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-sm">
                ✏️
            </div>
            <div>
                <h2 class="text-xl font-bold text-chocolate">Edit Data Alat / Produk</h2>
                <p class="text-xs text-rosegold-600 mt-0.5">Perbarui informasi inventaris sarana prasarana sekolah.</p>
            </div>
        </div>

        <?php if ($error) : ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl text-center">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Kode Alat</label>
                <input type="text" name="kode_alat" value="<?= htmlspecialchars($data['kode_alat']); ?>" required 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Alat</label>
                <input type="text" name="nama_alat" value="<?= htmlspecialchars($data['nama_alat']); ?>" required 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Kategori Alat</label>
                <select name="id_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($kat = mysqli_fetch_assoc($data_kategori)) : ?>
                        <option value="<?= $kat['id_kategori']; ?>" <?= ($kat['id_kategori'] == $data['id_kategori']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-chocolate/80 mb-1">Jumlah Stok (Unit)</label>
                <input type="number" name="jumlah" value="<?= $data['jumlah']; ?>" required min="0" 
                       class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-chocolate bg-cream-50 text-sm">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="dashboard_admin.php" class="px-5 py-2.5 bg-cream-100 hover:bg-cream-200 text-chocolate rounded-xl text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" name="update" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-xl text-xs transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</body>
</html>
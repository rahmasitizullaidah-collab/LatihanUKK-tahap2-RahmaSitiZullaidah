<?php
session_start();
include '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login_admin.php");
    exit;
}

$error = "";

// --- FUNGSI PENCATATAN LOG AKTIVITAS ---
function catatLog($koneksi, $id_admin, $aktivitas) {
    $id_admin_val = $id_admin ? "'$id_admin'" : "NULL";
    $aktivitas = mysqli_real_escape_string($koneksi, $aktivitas);
    
    $q_log = "INSERT INTO log_aktivitas (id_admin, aktivitas) VALUES ($id_admin_val, '$aktivitas')";
    mysqli_query($koneksi, $q_log);
}

// --- 1. PROSES CRUD BARANG (ALAT) ---
if (isset($_POST['aksi_barang'])) {
    $aksi = $_POST['aksi_barang'];

    if ($aksi == 'tambah') {
        $kode_alat   = mysqli_real_escape_string($koneksi, $_POST['kode_alat']);
        $nama_alat   = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
        $id_kategori = (int)$_POST['id_kategori'];
        $jumlah      = (int)$_POST['jumlah'];

        $query = "INSERT INTO alat (kode_alat, nama_alat, id_kategori, jumlah) VALUES ('$kode_alat', '$nama_alat', '$id_kategori', '$jumlah')";
        if (mysqli_query($koneksi, $query)) {
            catatLog($koneksi, $_SESSION['id_admin'], "Menambahkan alat baru: $nama_alat ($kode_alat)");
            echo "<script>alert('Alat berhasil ditambahkan!'); window.location='dashboard_admin.php';</script>";
            exit;
        } else {
            $error = "Gagal menambah alat: " . mysqli_error($koneksi);
        }
    } elseif ($aksi == 'edit') {
        $id_alat     = (int)$_POST['id_alat'];
        $kode_alat   = mysqli_real_escape_string($koneksi, $_POST['kode_alat']);
        $nama_alat   = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
        $id_kategori = (int)$_POST['id_kategori'];
        $jumlah      = (int)$_POST['jumlah'];

        $query = "UPDATE alat SET kode_alat = '$kode_alat', nama_alat = '$nama_alat', id_kategori = '$id_kategori', jumlah = '$jumlah' WHERE id_alat = '$id_alat'";
        if (mysqli_query($koneksi, $query)) {
            catatLog($koneksi, $_SESSION['id_admin'], "Memperbarui data alat: $nama_alat ($kode_alat)");
            echo "<script>alert('Alat berhasil diperbarui!'); window.location='dashboard_admin.php';</script>";
            exit;
        } else {
            $error = "Gagal memperbarui alat: " . mysqli_error($koneksi);
        }
    }
}

if (isset($_GET['hapus_alat'])) {
    $id_alat = (int)$_GET['hapus_alat'];
    // Ambil nama alat untuk log sebelum dihapus
    $get_nama = mysqli_query($koneksi, "SELECT nama_alat FROM alat WHERE id_alat = '$id_alat'");
    $row_nama = mysqli_fetch_assoc($get_nama);
    $nama_dihapus = $row_nama['nama_alat'] ?? 'ID: ' . $id_alat;

    mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat = '$id_alat'");
    catatLog($koneksi, $_SESSION['id_admin'], "Menghapus alat: $nama_dihapus");
    echo "<script>alert('Alat berhasil dihapus!'); window.location='dashboard_admin.php';</script>";
    exit;
}

// --- 2. PROSES CRUD KATEGORI ---
if (isset($_POST['aksi_kategori'])) {
    $aksi_kat = $_POST['aksi_kategori'];
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);

    if ($aksi_kat == 'tambah') {
        mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')");
        catatLog($koneksi, $_SESSION['id_admin'], "Menambahkan kategori baru: $nama_kategori");
        echo "<script>alert('Kategori berhasil ditambahkan!'); window.location='dashboard_admin.php';</script>";
        exit;
    } elseif ($aksi_kat == 'edit') {
        $id_kategori = (int)$_POST['id_kategori'];
        mysqli_query($koneksi, "UPDATE kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = '$id_kategori'");
        catatLog($koneksi, $_SESSION['id_admin'], "Memperbarui kategori menjadi: $nama_kategori");
        echo "<script>alert('Kategori berhasil diperbarui!'); window.location='dashboard_admin.php';</script>";
        exit;
    }
}

if (isset($_GET['hapus_kategori'])) {
    $id_kat = (int)$_GET['hapus_kategori'];
    $get_kat = mysqli_query($koneksi, "SELECT nama_kategori FROM kategori WHERE id_kategori = '$id_kat'");
    $row_kat = mysqli_fetch_assoc($get_kat);
    $nama_kat_dihapus = $row_kat['nama_kategori'] ?? 'ID: ' . $id_kat;

    mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id_kat'");
    catatLog($koneksi, $_SESSION['id_admin'], "Menghapus kategori: $nama_kat_dihapus");
    echo "<script>alert('Kategori berhasil dihapus!'); window.location='dashboard_admin.php';</script>";
    exit;
}

// --- 3. PROSES CRUD USER / PETUGAS ---
if (isset($_POST['aksi_user'])) {
    $aksi_user = $_POST['aksi_user'];
    $nama_petugas = mysqli_real_escape_string($koneksi, $_POST['nama_petugas']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);

    if ($aksi_user == 'tambah') {
        $password = md5($_POST['password']);
        mysqli_query($koneksi, "INSERT INTO petugas (nama_petugas, username, password) VALUES ('$nama_petugas', '$username', '$password')");
        catatLog($koneksi, $_SESSION['id_admin'], "Menambahkan petugas baru: $nama_petugas ($username)");
        echo "<script>alert('Petugas berhasil ditambahkan!'); window.location='dashboard_admin.php';</script>";
        exit;
    } elseif ($aksi_user == 'edit') {
        $id_petugas = (int)$_POST['id_petugas'];
        
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $query_user = "UPDATE petugas SET nama_petugas = '$nama_petugas', username = '$username', password = '$password' WHERE id_petugas = '$id_petugas'";
        } else {
            $query_user = "UPDATE petugas SET nama_petugas = '$nama_petugas', username = '$username' WHERE id_petugas = '$id_petugas'";
        }

        if (mysqli_query($koneksi, $query_user)) {
            catatLog($koneksi, $_SESSION['id_admin'], "Memperbarui data petugas: $nama_petugas");
            echo "<script>alert('Data petugas berhasil diperbarui!'); window.location='dashboard_admin.php';</script>";
            exit;
        } else {
            $error = "Gagal memperbarui data petugas: " . mysqli_error($koneksi);
        }
    }
}

if (isset($_GET['hapus_petugas'])) {
    $id_petugas = (int)$_GET['hapus_petugas'];
    $get_ptg = mysqli_query($koneksi, "SELECT nama_petugas FROM petugas WHERE id_petugas = '$id_petugas'");
    $row_ptg = mysqli_fetch_assoc($get_ptg);
    $nama_ptg_dihapus = $row_ptg['nama_petugas'] ?? 'ID: ' . $id_petugas;

    mysqli_query($koneksi, "DELETE FROM petugas WHERE id_petugas = '$id_petugas'");
    catatLog($koneksi, $_SESSION['id_admin'], "Menghapus petugas: $nama_ptg_dihapus");
    echo "<script>alert('Petugas berhasil dihapus!'); window.location='dashboard_admin.php';</script>";
    exit;
}

// --- 4. PROSES CRUD PEMINJAMAN & PENGEMBALIAN ---
if (isset($_GET['aksi_pinjam']) && isset($_GET['id'])) {
    $id_peminjaman = (int)$_GET['id'];
    $status_baru = ($_GET['aksi_pinjam'] == 'setujui') ? 'Disetujui' : 'Ditolak';

    mysqli_query($koneksi, "UPDATE peminjaman SET status = '$status_baru' WHERE id_peminjaman = '$id_peminjaman'");
    catatLog($koneksi, $_SESSION['id_admin'], "Mengubah status peminjaman ID #$id_peminjaman menjadi $status_baru");
    echo "<script>alert('Status peminjaman diubah menjadi {$status_baru}!'); window.location='dashboard_admin.php';</script>";
    exit;
}

if (isset($_GET['aksi_kembali']) && isset($_GET['id'])) {
    $id_peminjaman = (int)$_GET['id'];
    mysqli_query($koneksi, "UPDATE peminjaman SET status = 'Selesai' WHERE id_peminjaman = '$id_peminjaman'");
    catatLog($koneksi, $_SESSION['id_admin'], "Mencatat pengembalian selesai untuk peminjaman ID #$id_peminjaman");
    echo "<script>alert('Status pengembalian dicatat Selesai!'); window.location='dashboard_admin.php';</script>";
    exit;
}

if (isset($_GET['hapus_peminjaman'])) {
    $id_peminjaman = (int)$_GET['hapus_peminjaman'];
    mysqli_query($koneksi, "DELETE FROM detail_peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    catatLog($koneksi, $_SESSION['id_admin'], "Menghapus riwayat transaksi peminjaman ID #$id_peminjaman");
    echo "<script>alert('Data transaksi berhasil dihapus!'); window.location='dashboard_admin.php';</script>";
    exit;
}

// Ambil Data dari Database
$data_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$data_petugas = mysqli_query($koneksi, "SELECT * FROM petugas ORDER BY id_petugas DESC");
$data_peminjaman = mysqli_query($koneksi, "
    SELECT p.*, pm.nama_peminjam, pm.jenis_peminjam, pm.kelas, a.nama_alat, dp.jumlah_pinjam, pt.nama_petugas 
    FROM peminjaman p
    JOIN peminjam pm ON p.id_peminjam = pm.id_peminjam
    JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
    JOIN alat a ON dp.id_alat = a.id_alat
    LEFT JOIN petugas pt ON p.id_petugas = pt.id_petugas
    ORDER BY p.id_peminjaman DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sarpras Sekolah</title>
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

    <!-- Navbar Admin -->
    <header class="bg-chocolate text-cream-50 border-b border-cream-200 sticky top-0 z-20 shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-rosegold-400 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    🛡️
                </div>
                <div>
                    <h1 class="text-xl font-bold">Dashboard Admin</h1>
                    <p class="text-xs text-rosegold-300">Panel Utama Manajemen Fitur Lengkap Admin</p>
                </div>
            </div>

            <!-- Tombol Aksi di Samping Kanan (Log Aktivitas & Logout) -->
            <div class="flex items-center gap-3">
                <a href="log_aktivitas.php" class="text-xs font-semibold bg-rosegold-500 hover:bg-rosegold-600 text-white px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5">
                    📜 <span>Log Aktivitas</span>
                </a>
                <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari sistem?');" class="text-xs font-semibold bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-xl transition shadow-sm">
                    Logout Admin
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 space-y-10">

        <?php if ($error) : ?>
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: CRUD KATEGORI -->
        <section class="bg-white rounded-2xl border border-cream-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-cream-100">
                <div>
                    <h2 class="text-lg font-bold text-chocolate">CRUD Kategori Barang</h2>
                    <p class="text-xs text-chocolate/70">Kelola kelompok kategori jenis alat sarpras.</p>
                </div>
                <button onclick="openModalKategori()" class="bg-chocolate hover:bg-chocolate/90 text-cream-50 px-3 py-1.5 rounded-xl text-xs font-semibold transition">
                    + Tambah Kategori
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php 
                mysqli_data_seek($data_kategori, 0);
                while ($kat = mysqli_fetch_assoc($data_kategori)) : 
                ?>
                    <div class="flex items-center gap-2 bg-cream-100 px-3 py-1.5 rounded-xl border border-cream-200 text-xs font-semibold">
                        <span><?= htmlspecialchars($kat['nama_kategori']); ?></span>
                        <a href="dashboard_admin.php?hapus_kategori=<?= $kat['id_kategori']; ?>" onclick="return confirm('Hapus kategori ini?');" class="text-rose-600 hover:text-rose-800 font-bold">&times;</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- SECTION 2: CRUD BARANG PER KATEGORI -->
        <section class="bg-white rounded-2xl border border-cream-200 shadow-sm p-6 space-y-8">
            <div class="pb-4 border-b border-cream-100 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-chocolate">CRUD Data Barang (Alat) Per Kategori</h2>
                    <p class="text-xs text-chocolate/70 mt-0.5">Daftar inventaris alat sekolah.</p>
                </div>
                <button onclick="openModalTambah()" class="bg-chocolate hover:bg-chocolate/90 text-cream-50 px-4 py-2 rounded-xl text-xs font-semibold transition shadow-sm">
                    + Tambah Barang Baru
                </button>
            </div>

            <?php 
            mysqli_data_seek($data_kategori, 0);
            $ada_barang_total = false;

            while ($kat = mysqli_fetch_assoc($data_kategori)) :
                $id_kat = $kat['id_kategori'];
                $nama_kat = $kat['nama_kategori'];

                $query_alat_kat = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_kategori = '$id_kat' ORDER BY id_alat DESC");
                $jumlah_alat = mysqli_num_rows($query_alat_kat);
                
                if ($jumlah_alat > 0) $ada_barang_total = true;
            ?>
                <div class="space-y-3">
                    <div class="flex items-center justify-between bg-cream-100 px-4 py-2 rounded-xl border border-cream-200">
                        <span class="font-bold text-chocolate text-xs uppercase tracking-wide">📂 <?= htmlspecialchars($nama_kat); ?></span>
                        <span class="text-xs px-2 py-0.5 bg-white text-rosegold-600 font-semibold rounded-full border border-cream-200"><?= $jumlah_alat; ?> Item</span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-cream-200">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-cream-50 text-chocolate text-xs uppercase tracking-wider border-b border-cream-200">
                                    <th class="p-3 w-16">No</th>
                                    <th class="p-3">Kode Alat</th>
                                    <th class="p-3">Nama Alat</th>
                                    <th class="p-3 text-center">Jumlah Stok</th>
                                    <th class="p-3 text-center w-40">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                <?php 
                                $no = 1;
                                if ($jumlah_alat > 0) :
                                    while ($b = mysqli_fetch_assoc($query_alat_kat)) :
                                ?>
                                    <tr class="hover:bg-cream-50/50 transition">
                                        <td class="p-3 font-mono text-xs text-chocolate/60"><?= $no++; ?></td>
                                        <td class="p-3 font-semibold text-chocolate"><?= htmlspecialchars($b['kode_alat']); ?></td>
                                        <td class="p-3 text-chocolate"><?= htmlspecialchars($b['nama_alat']); ?></td>
                                        <td class="p-3 text-center font-bold text-rosegold-600"><?= $b['jumlah']; ?> Unit</td>
                                        <td class="p-3 text-center">
                                            <div class="flex justify-center gap-2">
                                                <button onclick="openModalEdit('<?= $b['id_alat']; ?>', '<?= htmlspecialchars($b['kode_alat'], ENT_QUOTES); ?>', '<?= htmlspecialchars($b['nama_alat'], ENT_QUOTES); ?>', '<?= $b['id_kategori']; ?>', '<?= $b['jumlah']; ?>')" 
                                                        class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition">Edit</button>
                                                <a href="dashboard_admin.php?hapus_alat=<?= $b['id_alat']; ?>" onclick="return confirm('Hapus alat ini?');" 
                                                   class="px-3 py-1 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold transition">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; else : ?>
                                    <tr><td colspan="5" class="p-4 text-center text-chocolate/40 text-xs italic">Belum ada barang pada kategori ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>

        <!-- SECTION 3: CRUD USER / PETUGAS -->
        <section class="bg-white rounded-2xl border border-cream-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-cream-100">
                <div>
                    <h2 class="text-lg font-bold text-chocolate">CRUD User / Petugas Sistem</h2>
                    <p class="text-xs text-chocolate/70">Kelola akun petugas yang berhak menyetujui peminjaman.</p>
                </div>
                <button onclick="openModalTambahUser()" class="bg-chocolate hover:bg-chocolate/90 text-cream-50 px-3 py-1.5 rounded-xl text-xs font-semibold transition">+ Tambah Petugas</button>
            </div>
            <div class="overflow-x-auto rounded-xl border border-cream-200">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-cream-100 text-chocolate text-xs uppercase tracking-wider border-b border-cream-200">
                            <th class="p-3 w-16">No</th>
                            <th class="p-3">Nama Petugas</th>
                            <th class="p-3">Username</th>
                            <th class="p-3 text-center w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        <?php 
                        $no_u = 1;
                        while ($u = mysqli_fetch_assoc($data_petugas)) : 
                        ?>
                            <tr class="hover:bg-cream-50">
                                <td class="p-3 text-xs text-chocolate/60"><?= $no_u++; ?></td>
                                <td class="p-3 font-bold text-chocolate"><?= htmlspecialchars($u['nama_petugas']); ?></td>
                                <td class="p-3 font-mono text-xs"><?= htmlspecialchars($u['username']); ?></td>
                                <td class="p-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="openModalEditUser('<?= $u['id_petugas']; ?>', '<?= htmlspecialchars($u['nama_petugas'], ENT_QUOTES); ?>', '<?= htmlspecialchars($u['username'], ENT_QUOTES); ?>')" 
                                                class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold transition shadow-sm">
                                            Edit
                                        </button>
                                        <a href="dashboard_admin.php?hapus_petugas=<?= $u['id_petugas']; ?>" onclick="return confirm('Hapus petugas ini?');" class="px-3 py-1 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold transition shadow-sm">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION 4: CRUD DATA PEMINJAMAN & PENGEMBALIAN -->
        <section class="bg-white rounded-2xl border border-cream-200 shadow-sm p-6 space-y-4">
            <div class="mb-2">
                <h2 class="text-xl font-bold text-chocolate">CRUD Data Peminjaman & Pengembalian</h2>
                <p class="text-xs text-chocolate/70">Kelola status persetujuan dan pengembalian barang pinjaman.</p>
            </div>

            <div class="overflow-x-auto rounded-xl border border-cream-200">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-cream-100 text-chocolate text-xs uppercase tracking-wider border-b border-cream-200">
                            <th class="p-3">No</th>
                            <th class="p-3">Peminjam</th>
                            <th class="p-3">Alat & Jumlah</th>
                            <th class="p-3">Jadwal Pinjam</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi (Peminjaman & Pengembalian)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        <?php 
                        $no_p = 1;
                        if (mysqli_num_rows($data_peminjaman) > 0) :
                            while ($p = mysqli_fetch_assoc($data_peminjaman)) :
                        ?>
                            <tr class="hover:bg-cream-50 transition">
                                <td class="p-3 font-mono text-xs text-chocolate/60"><?= $no_p++; ?></td>
                                <td class="p-3 font-bold text-chocolate"><?= htmlspecialchars($p['nama_peminjam']); ?></td>
                                <td class="p-3 text-chocolate"><?= htmlspecialchars($p['nama_alat']); ?> (<?= $p['jumlah_pinjam']; ?> Unit)</td>
                                <td class="p-3 text-xs text-chocolate/80"><?= $p['tanggal_pinjam']; ?></td>
                                <td class="p-3 text-center font-bold text-xs">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] 
                                        <?= $p['status'] == 'Disetujui' ? 'bg-emerald-100 text-emerald-800' : ($p['status'] == 'Selesai' ? 'bg-blue-100 text-blue-800' : ($p['status'] == 'Ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')); ?>">
                                        <?= $p['status']; ?>
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex justify-center gap-1.5">
                                        <?php if ($p['status'] == 'Menunggu') : ?>
                                            <a href="dashboard_admin.php?aksi_pinjam=setujui&id=<?= $p['id_peminjaman']; ?>" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold">Setujui</a>
                                            <a href="dashboard_admin.php?aksi_pinjam=tolak&id=<?= $p['id_peminjaman']; ?>" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold">Tolak</a>
                                        <?php elseif ($p['status'] == 'Disetujui') : ?>
                                            <a href="dashboard_admin.php?aksi_kembali=selesai&id=<?= $p['id_peminjaman']; ?>" class="px-2.5 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-semibold">Catat Selesai (Kembali)</a>
                                        <?php endif; ?>
                                        <a href="dashboard_admin.php?hapus_peminjaman=<?= $p['id_peminjaman']; ?>" onclick="return confirm('Hapus riwayat transaksi ini?');" class="px-2.5 py-1 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; else : ?>
                            <tr><td colspan="6" class="p-6 text-center text-chocolate/50 text-sm">Belum ada data peminjaman.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- MODAL TAMBAH / EDIT BARANG -->
    <div id="modalBarang" class="fixed inset-0 bg-chocolate/50 backdrop-blur-sm hidden items-center justify-center z-30 px-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-cream-200 shadow-xl">
            <h3 id="modalTitle" class="text-lg font-bold text-chocolate mb-4">Tambah Barang Baru</h3>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="aksi_barang" id="formAksi" value="tambah">
                <input type="hidden" name="id_alat" id="idAlat">
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Kode Alat</label>
                    <input type="text" name="kode_alat" id="kodeAlat" required placeholder="Contoh: ALT-001" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Alat</label>
                    <input type="text" name="nama_alat" id="namaAlat" required placeholder="Contoh: Proyektor Epson" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Kategori Alat</label>
                    <select name="id_kategori" id="idKategori" required class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm">
                        <option value="">-- Pilih Kategori --</option>
                        <?php 
                        mysqli_data_seek($data_kategori, 0);
                        while ($kat_m = mysqli_fetch_assoc($data_kategori)) : 
                        ?>
                            <option value="<?= $kat_m['id_kategori']; ?>"><?= htmlspecialchars($kat_m['nama_kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Jumlah Stok</label>
                    <input type="number" name="jumlah" id="jumlahAlat" required min="0" placeholder="Contoh: 10" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalBarang')" class="px-4 py-2 bg-cream-100 text-chocolate rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-chocolate text-cream-50 rounded-xl text-xs font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH KATEGORI -->
    <div id="modalKategori" class="fixed inset-0 bg-chocolate/50 backdrop-blur-sm hidden items-center justify-center z-30 px-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 border border-cream-200 shadow-xl">
            <h3 class="text-lg font-bold text-chocolate mb-4">Tambah Kategori Baru</h3>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="aksi_kategori" value="tambah">
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Kategori</label>
                    <input type="text" name="nama_kategori" required placeholder="Contoh: Elektronik" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalKategori')" class="px-4 py-2 bg-cream-100 text-chocolate rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-chocolate text-cream-50 rounded-xl text-xs font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH / EDIT USER (PETUGAS) -->
    <div id="modalUser" class="fixed inset-0 bg-chocolate/50 backdrop-blur-sm hidden items-center justify-center z-30 px-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-cream-200 shadow-xl">
            <h3 id="modalUserTitle" class="text-lg font-bold text-chocolate mb-4">Tambah Akun Petugas</h3>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="aksi_user" id="formUserAksi" value="tambah">
                <input type="hidden" name="id_petugas" id="idPetugas">

                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Petugas</label>
                    <input type="text" name="nama_petugas" id="namaPetugas" required placeholder="Nama lengkap petugas" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm focus:outline-none focus:border-chocolate">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Username</label>
                    <input type="text" name="username" id="usernamePetugas" required placeholder="Username login" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm focus:outline-none focus:border-chocolate">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Password</label>
                    <input type="password" name="password" id="passwordPetugas" placeholder="Password login" class="w-full px-4 py-2 rounded-xl border border-cream-200 bg-cream-50 text-sm focus:outline-none focus:border-chocolate">
                    <p id="passHelp" class="text-[10px] text-chocolate/60 mt-1 hidden">*Kosongkan jika tidak ingin mengubah password</p>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalUser')" class="px-4 py-2 bg-cream-100 text-chocolate rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-chocolate text-cream-50 rounded-xl text-xs font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Modal -->
    <script>
        function openModalTambah() {
            document.getElementById('modalTitle').innerText = "Tambah Barang Baru";
            document.getElementById('formAksi').value = "tambah";
            document.getElementById('idAlat').value = "";
            document.getElementById('kodeAlat').value = "";
            document.getElementById('namaAlat').value = "";
            document.getElementById('idKategori').value = "";
            document.getElementById('jumlahAlat').value = "";
            document.getElementById('modalBarang').classList.remove('hidden');
            document.getElementById('modalBarang').classList.add('flex');
        }

        function openModalEdit(id, kode, nama, katId, jumlah) {
            document.getElementById('modalTitle').innerText = "Edit Data Barang";
            document.getElementById('formAksi').value = "edit";
            document.getElementById('idAlat').value = id;
            document.getElementById('kodeAlat').value = kode;
            document.getElementById('namaAlat').value = nama;
            document.getElementById('idKategori').value = katId;
            document.getElementById('jumlahAlat').value = jumlah;
            document.getElementById('modalBarang').classList.remove('hidden');
            document.getElementById('modalBarang').classList.add('flex');
        }

        function openModalKategori() {
            document.getElementById('modalKategori').classList.remove('hidden');
            document.getElementById('modalKategori').classList.add('flex');
        }

        function openModalTambahUser() {
            document.getElementById('modalUserTitle').innerText = "Tambah Akun Petugas";
            document.getElementById('formUserAksi').value = "tambah";
            document.getElementById('idPetugas').value = "";
            document.getElementById('namaPetugas').value = "";
            document.getElementById('usernamePetugas').value = "";
            document.getElementById('passwordPetugas').value = "";
            document.getElementById('passwordPetugas').required = true;
            document.getElementById('passHelp').classList.add('hidden');
            document.getElementById('modalUser').classList.remove('hidden');
            document.getElementById('modalUser').classList.add('flex');
        }

        function openModalEditUser(id, nama, username) {
            document.getElementById('modalUserTitle').innerText = "Edit Data Petugas";
            document.getElementById('formUserAksi').value = "edit";
            document.getElementById('idPetugas').value = id;
            document.getElementById('namaPetugas').value = nama;
            document.getElementById('usernamePetugas').value = username;
            document.getElementById('passwordPetugas').value = "";
            document.getElementById('passwordPetugas').required = false;
            document.getElementById('passHelp').classList.remove('hidden');
            document.getElementById('modalUser').classList.remove('hidden');
            document.getElementById('modalUser').classList.add('flex');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('flex');
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</body>
</html>
<?php
date_default_timezone_set('Asia/Jakarta');
include 'koneksi.php';

session_start();

// Cek apakah peminjam sudah login
if (!isset($_SESSION['id_peminjam']) || $_SESSION['role'] != 'peminjam') {
    header("Location: login_peminjam.php");
    exit;
}

$id_peminjam_aktif = $_SESSION['id_peminjam'];

// Proses Simpan Peminjaman
if (isset($_POST['submit_pinjam'])) {
    $id_alat         = $_POST['id_alat'];
    $nama_peminjam   = mysqli_real_escape_string($koneksi, $_POST['nama_peminjam']);
    $jumlah_pinjam   = (int)$_POST['jumlah_pinjam'];
    $tgl_pinjam      = $_POST['tanggal_pinjam'];
    $jam_pinjam      = $_POST['jam_pinjam'];
    $tgl_kembali     = $_POST['tanggal_rencana_kembali'];
    $jam_kembali     = $_POST['jam_rencana_kembali'];
    $tujuan          = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $jenis_peminjam  = mysqli_real_escape_string($koneksi, $_POST['jenis_peminjam']);
    $kelas           = ($jenis_peminjam == 'Siswa') ? mysqli_real_escape_string($koneksi, $_POST['kelas']) : '';

    // Update data nama_peminjam, jenis_peminjam, dan kelas ke tabel peminjam
    mysqli_query($koneksi, "UPDATE peminjam SET nama_peminjam = '$nama_peminjam', jenis_peminjam = '$jenis_peminjam', kelas = '$kelas' WHERE id_peminjam = '$id_peminjam_aktif'");

    // Update session nama
    $_SESSION['nama_peminjam'] = $nama_peminjam;

    // Simpan Data Peminjaman Utama
    $query_peminjaman = "INSERT INTO peminjaman (id_peminjam, tanggal_pinjam, jam_pinjam, tanggal_rencana_kembali, jam_rencana_kembali, tujuan, status) 
                         VALUES ('$id_peminjam_aktif', '$tgl_pinjam', '$jam_pinjam', '$tgl_kembali', '$jam_kembali', '$tujuan', 'Menunggu')";

    if (mysqli_query($koneksi, $query_peminjaman)) {
        $id_peminjaman_baru = mysqli_insert_id($koneksi);

        // Simpan Detail Peminjaman
        $query_detail = "INSERT INTO detail_peminjaman (id_peminjaman, id_alat, jumlah_pinjam) 
                         VALUES ('$id_peminjaman_baru', '$id_alat', '$jumlah_pinjam')";
        
        if (mysqli_query($koneksi, $query_detail)) {
            echo "<script>alert('Pengajuan peminjaman berhasil dikirim! Menunggu persetujuan petugas.'); window.location='index.php';</script>";
            exit;
        }
    }
}

// Ambil Data Kategori
$data_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Sengaja dikosongkan agar navbar & modal form bersih dari nama lama
$nama_peminjam_aktif = '';
$default_nama  = '';
$default_jenis = 'Siswa';
$default_kelas = '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Sarana Prasarana Sekolah</title>
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
<body class="bg-cream-50 text-chocolate min-h-screen font-sans pb-16">

    <!-- Header / Navibar Lengkap -->
    <header class="bg-cream-100 border-b border-cream-200 sticky top-0 z-20 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-rosegold-400 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    S
                </div>
                <div>
                    <h1 class="text-xl font-bold text-chocolate">Sarpras Sekolah</h1>
                    <p class="text-xs text-rosegold-600">Silakan pilih alat untuk meminjam</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <a href="status_pinjam.php" class="text-xs font-semibold border border-rosegold-400 text-rosegold-600 hover:bg-rosegold-500 hover:text-white px-3 py-2 rounded-xl transition shadow-sm bg-white">
                    Cek Status
                </a>
                <a href="login_petugas.php" class="text-xs font-semibold border border-rosegold-400 text-rosegold-600 hover:bg-rosegold-500 hover:text-white px-3 py-2 rounded-xl transition shadow-sm bg-white">
                    Login Petugas
                </a>
                <a href="login_admin.php" class="text-xs font-semibold bg-chocolate text-white hover:bg-chocolate/90 px-3 py-2 rounded-xl transition shadow-sm">
                    Login Admin
                </a>
                <a href="logout.php" onclick="return confirm('Yakin ingin keluar?');" class="text-xs font-semibold bg-rose-500 hover:bg-rose-600 text-white px-3 py-2 rounded-xl transition shadow-sm">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Banner Informasi -->
    <section class="max-w-6xl mx-auto px-6 mt-8">
        <div class="bg-gradient-to-r from-cream-200 to-rosegold-300/30 rounded-2xl p-6 border border-rosegold-300/40">
            <h2 class="text-2xl font-bold mb-2">Pinjam Alat Sarpras Cepat & Mudah</h2>
            <p class="text-sm text-chocolate/80">Pilih peralatan yang kamu butuhkan berdasarkan kategori di bawah ini untuk mengajukan peminjaman.</p>
        </div>
    </section>

    <!-- Katalog Alat Dikelompokkan Per Kategori -->
    <main class="max-w-6xl mx-auto px-6 py-8 space-y-12">
        <?>
        <?php 
        $ada_alat_total = false;
        while ($kat = mysqli_fetch_assoc($data_kategori)) :
            $id_kat = $kat['id_kategori'];
            $nama_kat = $kat['nama_kategori'];

            $query_alat_kat = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_kategori = '$id_kat' ORDER BY nama_alat ASC");
            $jumlah_alat = mysqli_num_rows($query_alat_kat);

            if ($jumlah_alat > 0) {
                $ada_alat_total = true;
            } else {
                continue;
            }
        ?>
            <div class="space-y-4">
                <div class="flex items-center space-x-3 border-b-2 border-cream-200 pb-2">
                    <span class="text-xl">📂</span>
                    <h3 class="text-xl font-bold text-chocolate uppercase tracking-wide"><?= htmlspecialchars($nama_kat); ?></h3>
                    <span class="text-xs px-3 py-1 bg-cream-100 text-rosegold-600 font-bold rounded-full border border-cream-200 ml-auto">
                        <?= $jumlah_alat; ?> Alat Tersedia
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($row = mysqli_fetch_assoc($query_alat_kat)) : ?>
                        <div class="bg-white rounded-2xl p-5 border border-cream-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-cream-100 text-rosegold-600 border border-rosegold-300/30">
                                        <?= htmlspecialchars($nama_kat); ?>
                                    </span>
                                    <span class="text-xs text-chocolate/60 font-mono">
                                        <?= htmlspecialchars($row['kode_alat']); ?>
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold mb-1"><?= htmlspecialchars($row['nama_alat']); ?></h4>
                                <p class="text-xs text-chocolate/70 mb-4">Lokasi: <?= isset($row['lokasi']) ? htmlspecialchars($row['lokasi']) : 'Gudang Sarpras'; ?></p>
                                
                                <div class="flex items-center justify-between text-sm py-2 px-3 bg-cream-50 rounded-xl mb-4">
                                    <span class="text-chocolate/80">Stok Tersedia:</span>
                                    <span class="font-bold text-rosegold-600"><?= $row['jumlah']; ?> unit</span>
                                </div>
                            </div>

                            <?php if ($row['jumlah'] > 0) : ?>
                                <button onclick="bukaModal('<?= $row['id_alat']; ?>', '<?= htmlspecialchars($row['nama_alat'], ENT_QUOTES); ?>', <?= $row['jumlah']; ?>)" 
                                        class="w-full bg-rosegold-500 hover:bg-rosegold-600 text-white font-medium py-2.5 rounded-xl transition text-sm shadow-sm">
                                    Pinjam Barang Ini
                                </button>
                            <?php else : ?>
                                <button disabled class="w-full bg-cream-200 text-chocolate/40 font-medium py-2.5 rounded-xl text-sm cursor-not-allowed">
                                    Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </main>

    <!-- Modal Form Peminjaman -->
    <div id="modalPinjam" class="fixed inset-0 bg-chocolate/35 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 border border-cream-200 shadow-xl relative max-h-[90vh] overflow-y-auto">
            
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-cream-100">
                <h3 class="text-lg font-bold text-chocolate">Form Pengajuan Peminjaman</h3>
                <button onclick="tutupModal()" class="text-chocolate/50 hover:text-chocolate font-bold text-xl">&times;</button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="id_alat" id="modal_id_alat">
                
                <div class="p-3 bg-cream-100 rounded-xl text-sm">
                    <p class="text-xs text-chocolate/70">Barang yang dipinjam:</p>
                    <p class="font-bold text-rosegold-600 text-base" id="modal_nama_alat">-</p>
                </div>

                <!-- Input Nama Peminjam (Dipastikan Kosong) -->
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" id="input_nama_peminjam" value="" placeholder="Masukkan nama lengkap" required
                           class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                </div>

                <!-- Status Peminjam -->
                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-2">Status Peminjam</label>
                    <div class="flex items-center space-x-6 bg-cream-50 px-4 py-2.5 rounded-xl border border-cream-200">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="jenis_peminjam" value="Siswa" id="radio_siswa" checked onchange="toggleKelas()" class="accent-rosegold-500 w-4 h-4">
                            <span class="text-sm font-medium text-chocolate">Siswa</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="jenis_peminjam" value="Guru" id="radio_guru" onchange="toggleKelas()" class="accent-rosegold-500 w-4 h-4">
                            <span class="text-sm font-medium text-chocolate">Guru</span>
                        </label>
                    </div>
                </div>

                <!-- Input Kelas -->
                <div id="wrapper_kelas">
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Nama Kelas</label>
                    <input type="text" name="kelas" id="input_kelas" value="" placeholder
                           class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-chocolate/80 mb-1">Jumlah Pinjam</label>
                        <input type="text" name="jumlah_pinjam" id="modal_jumlah" min="1" value="1" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-chocolate/80 mb-1">Tgl Pinjam</label>
                        <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d'); ?>" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-chocolate/80 mb-1">Jam Pinjam</label>
                        <input type="time" name="jam_pinjam" value="<?= date('H:i'); ?>" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-chocolate/80 mb-1">Jam Rencana Kembali</label>
                        <input type="time" name="jam_rencana_kembali" required 
                               class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Tgl Rencana Kembali</label>
                    <input type="date" name="tanggal_rencana_kembali" value="<?= date('Y-m-d'); ?>" required 
                           class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-chocolate/80 mb-1">Tujuan / Keperluan</label>
                    <textarea name="tujuan" rows="2" placeholder required 
                              class="w-full px-4 py-2.5 rounded-xl border border-cream-200 focus:outline-none focus:border-rosegold-400 bg-cream-50 text-sm"></textarea>
                </div>

                <div class="pt-3 flex gap-3">
                    <button type="button" onclick="tutupModal()" class="w-1/2 py-2.5 rounded-xl border border-cream-200 text-chocolate/70 font-medium text-sm hover:bg-cream-100">
                        Batal
                    </button>
                    <button type="submit" name="submit_pinjam" class="w-1/2 bg-rosegold-500 hover:bg-rosegold-600 text-white font-medium py-2.5 rounded-xl text-sm shadow-sm">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleKelas() {
            const isSiswa = document.getElementById('radio_siswa').checked;
            const wrapperKelas = document.getElementById('wrapper_kelas');
            const inputKelas = document.getElementById('input_kelas');

            if (isSiswa) {
                wrapperKelas.style.display = 'block';
                inputKelas.required = true;
            } else {
                wrapperKelas.style.display = 'none';
                inputKelas.required = false;
                inputKelas.value = ''; 
            }
        }

        window.addEventListener('DOMContentLoaded', toggleKelas);

        function bukaModal(id, nama, stokMaksimal) {
            document.getElementById('modal_id_alat').value = id;
            document.getElementById('modal_nama_alat').innerText = nama;
            
            // Kosongkan form input nama dan kelas setiap kali modal dibuka
            document.getElementById('input_nama_peminjam').value = '';
            document.getElementById('input_kelas').value = '';

            const inputJumlah = document.getElementById('modal_jumlah');
            inputJumlah.max = stokMaksimal;
            inputJumlah.value = 1;

            document.getElementById('modalPinjam').classList.remove('hidden');
            document.getElementById('modalPinjam').classList.add('flex');
        }

        function tutupModal() {
            document.getElementById('modalPinjam').classList.add('hidden');
            document.getElementById('modalPinjam').classList.remove('flex');
        }
    </script>
</body>
</html>
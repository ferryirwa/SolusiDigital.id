<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}

// Ambil ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'ID layanan tidak valid!');
    redirect(ADMIN_URL . 'layanan.php');
}

// Ambil data layanan untuk konfirmasi nama
$stmt = $koneksi->prepare("SELECT nama FROM layanan WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Layanan tidak ditemukan!');
    redirect(ADMIN_URL . 'layanan.php');
}

$layanan = $result->fetch_assoc();

// Cek apakah layanan sedang dipakai di pesanan
$cek_pesanan = $koneksi->prepare("SELECT COUNT(*) as jml FROM pesanan WHERE layanan_id = ?");
$cek_pesanan->bind_param('i', $id);
$cek_pesanan->execute();
$jumlah_pesanan = $cek_pesanan->get_result()->fetch_assoc()['jml'];

if ($jumlah_pesanan > 0) {
    // Layanan masih dipakai — kita set jadi NULL di pesanan, atau tolak?
    // Opsi: set NULL di pesanan (karena ada ON DELETE SET NULL) lalu hapus layanan
    // Untuk safety, kita beri peringatan dan tetap hapus
    // (karena FK sudah ON DELETE SET NULL)
}

// Hapus layanan
$stmt_del = $koneksi->prepare("DELETE FROM layanan WHERE id = ?");
$stmt_del->bind_param('i', $id);

if ($stmt_del->execute()) {
    $pesan = '✅ Layanan <strong>' . htmlspecialchars($layanan['nama']) . '</strong> berhasil dihapus.';
    if ($jumlah_pesanan > 0) {
        $pesan .= ' <br><small class="text-muted">(' . $jumlah_pesanan . ' pesanan terkait sekarang tidak memiliki referensi layanan)</small>';
    }
    set_flash('success', $pesan);
} else {
    set_flash('danger', '❌ Gagal menghapus: ' . $stmt_del->error);
}

redirect(ADMIN_URL . 'layanan.php');
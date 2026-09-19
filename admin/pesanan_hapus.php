<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('danger', 'ID tidak valid!');
    redirect(ADMIN_URL . 'pesanan.php');
}

// Ambil kode pesanan untuk notif
$stmt = $koneksi->prepare("SELECT kode_pesanan FROM pesanan WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Pesanan tidak ditemukan!');
    redirect(ADMIN_URL . 'pesanan.php');
}

$pesanan = $result->fetch_assoc();

// Hapus file lampiran kalau ada
$cek_lampiran = $koneksi->query("SELECT file_lampiran FROM pesanan WHERE id = $id")->fetch_assoc();
if (!empty($cek_lampiran['file_lampiran'])) {
    $path = UPLOAD_PATH . 'pesanan/' . $cek_lampiran['file_lampiran'];
    if (file_exists($path)) @unlink($path);
}

// Hapus dari DB
$stmt_del = $koneksi->prepare("DELETE FROM pesanan WHERE id = ?");
$stmt_del->bind_param('i', $id);

if ($stmt_del->execute()) {
    set_flash('success', "✅ Pesanan <strong>{$pesanan['kode_pesanan']}</strong> berhasil dihapus.");
} else {
    set_flash('danger', '❌ Gagal menghapus: ' . $stmt_del->error);
}

redirect(ADMIN_URL . 'pesanan.php');
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
    redirect(ADMIN_URL . 'testimoni.php');
}

$stmt = $koneksi->prepare("SELECT nama, foto FROM testimoni WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Testimoni tidak ditemukan!');
    redirect(ADMIN_URL . 'testimoni.php');
}

$testi = $result->fetch_assoc();

// Hapus file foto
if (!empty($testi['foto'])) {
    $path = PORTFOLIO_PATH . 'testimoni/' . $testi['foto'];
    if (file_exists($path)) @unlink($path);
}

// Hapus dari DB
$stmt_del = $koneksi->prepare("DELETE FROM testimoni WHERE id = ?");
$stmt_del->bind_param('i', $id);

if ($stmt_del->execute()) {
    set_flash('success', '✅ Testimoni dari <strong>' . htmlspecialchars($testi['nama']) . '</strong> berhasil dihapus.');
} else {
    set_flash('danger', '❌ Gagal menghapus: ' . $stmt_del->error);
}

redirect(ADMIN_URL . 'testimoni.php');
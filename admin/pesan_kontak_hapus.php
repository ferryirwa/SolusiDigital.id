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
    redirect(ADMIN_URL . 'pesan_kontak.php');
}

$stmt = $koneksi->prepare("SELECT nama FROM pesan_kontak WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Pesan tidak ditemukan!');
    redirect(ADMIN_URL . 'pesan_kontak.php');
}

$pesan = $result->fetch_assoc();

$stmt_del = $koneksi->prepare("DELETE FROM pesan_kontak WHERE id = ?");
$stmt_del->bind_param('i', $id);

if ($stmt_del->execute()) {
    set_flash('success', '✅ Pesan dari <strong>' . htmlspecialchars($pesan['nama']) . '</strong> berhasil dihapus.');
} else {
    set_flash('danger', '❌ Gagal menghapus: ' . $stmt_del->error);
}

redirect(ADMIN_URL . 'pesan_kontak.php');
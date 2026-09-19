<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}

// Hanya super admin
if ($_SESSION['admin_role'] !== 'super') {
    set_flash('danger', 'Anda tidak memiliki akses.');
    redirect(ADMIN_URL . 'dashboard.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('danger', 'ID tidak valid!');
    redirect(ADMIN_URL . 'admin.php');
}

// Cegah hapus diri sendiri
if ($id == $_SESSION['admin_id']) {
    set_flash('danger', 'Anda tidak bisa menghapus akun sendiri!');
    redirect(ADMIN_URL . 'admin.php');
}

// Ambil data admin
$stmt = $koneksi->prepare("SELECT nama, role FROM admin WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Admin tidak ditemukan!');
    redirect(ADMIN_URL . 'admin.php');
}

$admin = $result->fetch_assoc();

// Cegah hapus super admin
if ($admin['role'] === 'super') {
    set_flash('danger', 'Super admin tidak bisa dihapus!');
    redirect(ADMIN_URL . 'admin.php');
}

// Hapus
$stmt_del = $koneksi->prepare("DELETE FROM admin WHERE id = ?");
$stmt_del->bind_param('i', $id);

if ($stmt_del->execute()) {
    set_flash('success', '✅ Admin <strong>' . htmlspecialchars($admin['nama']) . '</strong> berhasil dihapus.');
} else {
    set_flash('danger', '❌ Gagal menghapus: ' . $stmt_del->error);
}

redirect(ADMIN_URL . 'admin.php');
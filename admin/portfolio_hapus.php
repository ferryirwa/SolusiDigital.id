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
    set_flash('danger', 'ID portfolio tidak valid!');
    redirect(ADMIN_URL . 'portfolio.php');
}

// Ambil data portfolio
$stmt = $koneksi->prepare("SELECT judul, gambar FROM portfolio WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Portfolio tidak ditemukan!');
    redirect(ADMIN_URL . 'portfolio.php');
}

$portfolio = $result->fetch_assoc();

// Hapus file gambar dari server
if (!empty($portfolio['gambar']) && file_exists(PORTFOLIO_PATH . $portfolio['gambar'])) {
    @unlink(PORTFOLIO_PATH . $portfolio['gambar']);
}

// Hapus data dari database
$stmt_del = $koneksi->prepare("DELETE FROM portfolio WHERE id = ?");
$stmt_del->bind_param('i', $id);

if ($stmt_del->execute()) {
    set_flash('success', '✅ Portfolio <strong>' . htmlspecialchars($portfolio['judul']) . '</strong> berhasil dihapus.');
} else {
    set_flash('danger', '❌ Gagal menghapus: ' . $stmt_del->error);
}

redirect(ADMIN_URL . 'portfolio.php');
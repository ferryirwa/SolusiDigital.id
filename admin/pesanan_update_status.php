<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'pesanan.php');
}

$id    = (int)($_POST['id'] ?? 0);
$aksi  = $_POST['aksi'] ?? '';

if ($id <= 0) {
    set_flash('danger', 'ID tidak valid!');
    redirect(ADMIN_URL . 'pesanan.php');
}

// Ambil data pesanan
$stmt = $koneksi->prepare("SELECT kode_pesanan, nama_klien, status FROM pesanan WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Pesanan tidak ditemukan!');
    redirect(ADMIN_URL . 'pesanan.php');
}

$pesanan = $result->fetch_assoc();

// ============ AKSI: UPDATE STATUS ============
if ($aksi === 'status') {
    $status_baru = $_POST['status'] ?? '';

    if (!in_array($status_baru, ['baru', 'proses', 'selesai', 'batal'])) {
        set_flash('danger', 'Status tidak valid!');
        redirect(ADMIN_URL . "pesanan_detail.php?id=$id");
    }

    if ($status_baru === $pesanan['status']) {
        set_flash('info', 'Status masih sama, tidak ada perubahan.');
        redirect(ADMIN_URL . "pesanan_detail.php?id=$id");
    }

    $stmt_upd = $koneksi->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt_upd->bind_param('si', $status_baru, $id);

    if ($stmt_upd->execute()) {
        $label = [
            'baru'    => 'Baru',
            'proses'  => 'Sedang Diproses',
            'selesai' => 'Selesai',
            'batal'   => 'Dibatalkan'
        ][$status_baru];

        set_flash('success', "✅ Status pesanan <strong>{$pesanan['kode_pesanan']}</strong> berhasil diubah menjadi <strong>$label</strong>.");
    } else {
        set_flash('danger', '❌ Gagal update status: ' . $stmt_upd->error);
    }
    redirect(ADMIN_URL . "pesanan_detail.php?id=$id");
}

// ============ AKSI: SIMPAN CATATAN ============
if ($aksi === 'catatan') {
    $catatan = trim($_POST['catatan_admin'] ?? '');

    $stmt_upd = $koneksi->prepare("UPDATE pesanan SET catatan_admin = ? WHERE id = ?");
    $stmt_upd->bind_param('si', $catatan, $id);

    if ($stmt_upd->execute()) {
        set_flash('success', '✅ Catatan admin berhasil disimpan.');
    } else {
        set_flash('danger', '❌ Gagal menyimpan catatan: ' . $stmt_upd->error);
    }
    redirect(ADMIN_URL . "pesanan_detail.php?id=$id");
}

// Kalau tidak ada aksi
set_flash('danger', 'Aksi tidak dikenali!');
redirect(ADMIN_URL . 'pesanan.php');
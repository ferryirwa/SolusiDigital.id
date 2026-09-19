<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => false, 'pesan' => 'Unauthorized']);
    exit;
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'pesan' => 'Method not allowed']);
    exit;
}

// Ambil data
$id     = (int)($_POST['id'] ?? 0);
$urutan = (int)($_POST['urutan'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => false, 'pesan' => 'ID tidak valid']);
    exit;
}

$stmt = $koneksi->prepare("UPDATE layanan SET urutan = ? WHERE id = ?");
$stmt->bind_param('ii', $urutan, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'pesan' => 'Urutan berhasil diupdate']);
} else {
    echo json_encode(['status' => false, 'pesan' => 'Gagal update: ' . $stmt->error]);
}
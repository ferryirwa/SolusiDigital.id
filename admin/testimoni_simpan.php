<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'testimoni.php');
}

// Ambil data
$id           = (int)($_POST['id'] ?? 0);
$mode         = $_POST['mode'] ?? 'tambah';
$nama         = trim($_POST['nama'] ?? '');
$jabatan      = trim($_POST['jabatan'] ?? '');
$perusahaan   = trim($_POST['perusahaan'] ?? '');
$isi          = trim($_POST['isi'] ?? '');
$rating       = (int)($_POST['rating'] ?? 5);
$status       = isset($_POST['status']) && $_POST['status'] === 'tampil' ? 'tampil' : 'sembunyi';
$foto_lama    = trim($_POST['foto_lama'] ?? '');
$hapus_foto   = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] == '1';

// Validasi
$errors = [];
if (empty($nama)) $errors[] = 'Nama wajib diisi.';
if (strlen($nama) > 100) $errors[] = 'Nama maksimal 100 karakter.';
if (empty($isi)) $errors[] = 'Isi testimoni wajib diisi.';
if (strlen($isi) > 1000) $errors[] = 'Isi testimoni maksimal 1000 karakter.';
if ($rating < 1 || $rating > 5) $errors[] = 'Rating harus 1-5.';

// Folder foto testimoni
$folder_testimoni = PORTFOLIO_PATH . 'testimoni/';
if (!is_dir($folder_testimoni)) {
    mkdir($folder_testimoni, 0755, true);
}

// Handle foto
$nama_foto = $foto_lama;

if ($hapus_foto && (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE)) {
    if (!empty($foto_lama) && file_exists($folder_testimoni . $foto_lama)) {
        unlink($folder_testimoni . $foto_lama);
    }
    $nama_foto = '';
}

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $upload = upload_gambar($_FILES['foto'], $folder_testimoni, 1024 * 1024); // 1MB
    if ($upload['status']) {
        if (!empty($foto_lama) && file_exists($folder_testimoni . $foto_lama)) {
            unlink($folder_testimoni . $foto_lama);
        }
        $nama_foto = $upload['nama_file'];
    } else {
        $errors[] = 'Upload foto gagal: ' . $upload['pesan'];
    }
}

if (!empty($errors)) {
    $_SESSION['form_testimoni'] = $_POST;
    set_flash('danger', implode('<br>', $errors));
    if ($mode === 'edit') {
        redirect(ADMIN_URL . "testimoni_form.php?id=$id");
    } else {
        redirect(ADMIN_URL . 'testimoni_form.php');
    }
}

// Simpan
if ($mode === 'edit' && $id > 0) {
    $sql = "UPDATE testimoni SET nama=?, jabatan=?, perusahaan=?, isi=?, foto=?, rating=?, status=? WHERE id=?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sssssisi', $nama, $jabatan, $perusahaan, $isi, $nama_foto, $rating, $status, $id);

    if ($stmt->execute()) {
        set_flash('success', '✅ Testimoni dari <strong>' . htmlspecialchars($nama) . '</strong> berhasil diupdate!');
    } else {
        set_flash('danger', '❌ Gagal: ' . $stmt->error);
    }
} else {
    $sql = "INSERT INTO testimoni (nama, jabatan, perusahaan, isi, foto, rating, status) VALUES (?,?,?,?,?,?,?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sssssis', $nama, $jabatan, $perusahaan, $isi, $nama_foto, $rating, $status);

    if ($stmt->execute()) {
        set_flash('success', '✅ Testimoni dari <strong>' . htmlspecialchars($nama) . '</strong> berhasil ditambahkan!');
    } else {
        set_flash('danger', '❌ Gagal: ' . $stmt->error);
    }
}

redirect(ADMIN_URL . 'testimoni.php');
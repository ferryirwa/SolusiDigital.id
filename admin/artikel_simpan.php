<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'artikel.php');
}

// Ambil data
$id           = (int)($_POST['id'] ?? 0);
$mode         = $_POST['mode'] ?? 'tambah';
$judul        = trim($_POST['judul'] ?? '');
$slug         = trim($_POST['slug'] ?? '');
$konten       = $_POST['konten'] ?? '';
$penulis      = trim($_POST['penulis'] ?? 'Admin');
$status       = isset($_POST['status']) && $_POST['status'] === 'publish' ? 'publish' : 'draft';
$gambar_lama  = trim($_POST['gambar_lama'] ?? '');
$hapus_gambar = isset($_POST['hapus_gambar']) && $_POST['hapus_gambar'] == '1';

// Validasi
$errors = [];
if (empty($judul)) {
    $errors[] = 'Judul artikel wajib diisi.';
} elseif (strlen($judul) > 200) {
    $errors[] = 'Judul maksimal 200 karakter.';
}

if (empty(trim(strip_tags($konten)))) {
    $errors[] = 'Konten artikel wajib diisi.';
}

if (empty($slug)) {
    $slug = buat_slug($judul);
} else {
    $slug = buat_slug($slug);
}
if (empty($slug)) {
    $errors[] = 'Slug tidak valid.';
}

// Cek slug unik
$slug_esc = $koneksi->real_escape_string($slug);
if ($mode === 'edit' && $id > 0) {
    $cek = $koneksi->query("SELECT id FROM artikel WHERE slug = '$slug_esc' AND id != $id");
} else {
    $cek = $koneksi->query("SELECT id FROM artikel WHERE slug = '$slug_esc'");
}
if ($cek->num_rows > 0) {
    $errors[] = "Slug '$slug' sudah dipakai.";
}

if (!is_dir(ARTIKEL_PATH)) {
    mkdir(ARTIKEL_PATH, 0755, true);
}

// Handle gambar
$nama_gambar = $gambar_lama;

if ($hapus_gambar && (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE)) {
    if (!empty($gambar_lama) && file_exists(ARTIKEL_PATH . $gambar_lama)) {
        @unlink(ARTIKEL_PATH . $gambar_lama);
    }
    $nama_gambar = '';
}

if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $upload = upload_gambar($_FILES['gambar'], ARTIKEL_PATH, 2 * 1024 * 1024);
    if ($upload['status']) {
        if (!empty($gambar_lama) && file_exists(ARTIKEL_PATH . $gambar_lama)) {
            @unlink(ARTIKEL_PATH . $gambar_lama);
        }
        $nama_gambar = $upload['nama_file'];
    } else {
        $errors[] = 'Upload gambar gagal: ' . $upload['pesan'];
    }
}

if (!empty($errors)) {
    $_SESSION['form_artikel'] = $_POST;
    set_flash('danger', implode('<br>', $errors));
    if ($mode === 'edit') {
        redirect(ADMIN_URL . "artikel_form.php?id=$id");
    } else {
        redirect(ADMIN_URL . 'artikel_form.php');
    }
}

// Simpan
if ($mode === 'edit' && $id > 0) {
    $sql = "UPDATE artikel SET judul=?, slug=?, konten=?, gambar=?, penulis=?, status=? WHERE id=?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ssssssi', $judul, $slug, $konten, $nama_gambar, $penulis, $status, $id);

    if ($stmt->execute()) {
        set_flash('success', '✅ Artikel <strong>' . htmlspecialchars($judul) . '</strong> berhasil diupdate!');
    } else {
        set_flash('danger', '❌ Gagal: ' . $stmt->error);
    }
} else {
    $sql = "INSERT INTO artikel (judul, slug, konten, gambar, penulis, status) VALUES (?,?,?,?,?,?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ssssss', $judul, $slug, $konten, $nama_gambar, $penulis, $status);

    if ($stmt->execute()) {
        set_flash('success', '✅ Artikel <strong>' . htmlspecialchars($judul) . '</strong> berhasil ditambahkan!');
    } else {
        set_flash('danger', '❌ Gagal: ' . $stmt->error);
    }
}

redirect(ADMIN_URL . 'artikel.php');
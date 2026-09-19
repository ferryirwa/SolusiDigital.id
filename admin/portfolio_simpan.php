<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'portfolio.php');
}

// Ambil data
$id              = (int)($_POST['id'] ?? 0);
$mode            = $_POST['mode'] ?? 'tambah';
$judul           = trim($_POST['judul'] ?? '');
$slug            = trim($_POST['slug'] ?? '');
$kategori        = trim($_POST['kategori'] ?? 'web');
$klien           = trim($_POST['klien'] ?? '');
$deskripsi       = trim($_POST['deskripsi'] ?? '');
$link_demo       = trim($_POST['link_demo'] ?? '');
$teknologi       = trim($_POST['teknologi'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? date('Y-m-d'));
$gambar_lama     = trim($_POST['gambar_lama'] ?? '');
$hapus_gambar    = isset($_POST['hapus_gambar']) && $_POST['hapus_gambar'] == '1';

// ============ VALIDASI ============
$errors = [];

if (empty($judul)) {
    $errors[] = 'Judul proyek wajib diisi.';
} elseif (strlen($judul) > 150) {
    $errors[] = 'Judul maksimal 150 karakter.';
}

if (empty($kategori)) {
    $errors[] = 'Kategori wajib dipilih.';
}

// Slug
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
    $cek = $koneksi->query("SELECT id FROM portfolio WHERE slug = '$slug_esc' AND id != $id");
} else {
    $cek = $koneksi->query("SELECT id FROM portfolio WHERE slug = '$slug_esc'");
}
if ($cek->num_rows > 0) {
    $errors[] = "Slug '$slug' sudah dipakai.";
}

// Validasi link demo
if (!empty($link_demo) && !filter_var($link_demo, FILTER_VALIDATE_URL)) {
    $errors[] = 'Link demo tidak valid (harus berupa URL).';
}

// Validasi tanggal
if (!empty($tanggal_selesai) && strtotime($tanggal_selesai) === false) {
    $errors[] = 'Tanggal selesai tidak valid.';
}

// ============ HANDLE UPLOAD GAMBAR ============
$nama_gambar = $gambar_lama;

// Kalau user centang hapus_gambar tapi tidak upload baru
if ($hapus_gambar && (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE)) {
    // Hapus file lama
    if (!empty($gambar_lama) && file_exists(PORTFOLIO_PATH . $gambar_lama)) {
        unlink(PORTFOLIO_PATH . $gambar_lama);
    }
    $nama_gambar = '';
}

// Kalau ada file baru di-upload
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $upload = upload_gambar($_FILES['gambar'], PORTFOLIO_PATH, 2 * 1024 * 1024);

    if ($upload['status']) {
        // Hapus gambar lama kalau ada
        if (!empty($gambar_lama) && file_exists(PORTFOLIO_PATH . $gambar_lama)) {
            unlink(PORTFOLIO_PATH . $gambar_lama);
        }
        $nama_gambar = $upload['nama_file'];
    } else {
        $errors[] = 'Upload gagal: ' . $upload['pesan'];
    }
}

// Kalau ada error, balik ke form
if (!empty($errors)) {
    $_SESSION['form_portfolio'] = $_POST;
    set_flash('danger', implode('<br>', $errors));
    if ($mode === 'edit') {
        redirect(ADMIN_URL . "portfolio_form.php?id=$id");
    } else {
        redirect(ADMIN_URL . 'portfolio_form.php');
    }
}


// ============ SIMPAN KE DATABASE ============
if ($mode === 'edit' && $id > 0) {
    // UPDATE
    $sql = "UPDATE portfolio SET 
                judul = ?, 
                slug = ?, 
                kategori = ?, 
                klien = ?, 
                deskripsi = ?, 
                gambar = ?, 
                link_demo = ?, 
                teknologi = ?, 
                tanggal_selesai = ? 
            WHERE id = ?";
    
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sssssssssi', 
        $judul, $slug, $kategori, $klien, $deskripsi, 
        $nama_gambar, $link_demo, $teknologi, $tanggal_selesai, $id
    );

    if ($stmt->execute()) {
        set_flash('success', '✅ Portfolio <strong>' . htmlspecialchars($judul) . '</strong> berhasil diupdate!');
    } else {
        set_flash('danger', '❌ Gagal mengupdate: ' . $stmt->error);
    }

} else {
    // INSERT
    $sql = "INSERT INTO portfolio 
                (judul, slug, kategori, klien, deskripsi, gambar, link_demo, teknologi, tanggal_selesai) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sssssssss', 
        $judul, $slug, $kategori, $klien, $deskripsi, 
        $nama_gambar, $link_demo, $teknologi, $tanggal_selesai
    );

    if ($stmt->execute()) {
        set_flash('success', '✅ Portfolio <strong>' . htmlspecialchars($judul) . '</strong> berhasil ditambahkan!');
    } else {
        set_flash('danger', '❌ Gagal menyimpan: ' . $stmt->error);
    }
}

redirect(ADMIN_URL . 'portfolio.php');
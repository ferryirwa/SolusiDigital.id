<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'layanan.php');
}

// Ambil data
$id              = (int)($_POST['id'] ?? 0);
$mode            = $_POST['mode'] ?? 'tambah';
$nama            = trim($_POST['nama'] ?? '');
$slug            = trim($_POST['slug'] ?? '');
$deskripsi_singkat = trim($_POST['deskripsi_singkat'] ?? '');
$deskripsi_lengkap = trim($_POST['deskripsi_lengkap'] ?? '');
$harga_mulai     = (int)($_POST['harga_mulai'] ?? 0);
$icon            = trim($_POST['icon'] ?? 'bi-code-slash');
$urutan          = (int)($_POST['urutan'] ?? 0);
$status          = isset($_POST['status']) && $_POST['status'] === 'aktif' ? 'aktif' : 'nonaktif';

// ============ VALIDASI ============
$errors = [];

if (empty($nama)) {
    $errors[] = 'Nama layanan wajib diisi.';
} elseif (strlen($nama) > 100) {
    $errors[] = 'Nama layanan maksimal 100 karakter.';
}

if (empty($deskripsi_singkat)) {
    $errors[] = 'Deskripsi singkat wajib diisi.';
} elseif (strlen($deskripsi_singkat) > 300) {
    $errors[] = 'Deskripsi singkat maksimal 300 karakter.';
}

// Generate slug otomatis kalau kosong
if (empty($slug)) {
    $slug = buat_slug($nama);
} else {
    $slug = buat_slug($slug);
}

if (empty($slug)) {
    $errors[] = 'Slug tidak valid.';
}

// Cek slug unik (kecuali untuk dirinya sendiri saat edit)
$slug_escaped = $koneksi->real_escape_string($slug);
if ($mode === 'edit') {
    $cek = $koneksi->query("SELECT id FROM layanan WHERE slug = '$slug_escaped' AND id != $id");
} else {
    $cek = $koneksi->query("SELECT id FROM layanan WHERE slug = '$slug_escaped'");
}
if ($cek->num_rows > 0) {
    $errors[] = "Slug '$slug' sudah dipakai. Gunakan nama atau slug lain.";
}

if ($harga_mulai < 0) {
    $errors[] = 'Harga tidak boleh negatif.';
}

// Kalau ada error, redirect balik ke form
if (!empty($errors)) {
    $_SESSION['form_layanan'] = $_POST;
    set_flash('danger', implode('<br>', $errors));
    if ($mode === 'edit') {
        redirect(ADMIN_URL . "layanan_form.php?id=$id");
    } else {
        redirect(ADMIN_URL . 'layanan_form.php');
    }
}

// ============ SIMPAN ============
$nama_esc            = $koneksi->real_escape_string($nama);
$slug_esc            = $koneksi->real_escape_string($slug);
$desk_singkat_esc    = $koneksi->real_escape_string($deskripsi_singkat);
$desk_lengkap_esc    = $koneksi->real_escape_string($deskripsi_lengkap);
$icon_esc            = $koneksi->real_escape_string($icon);
$status_esc          = $koneksi->real_escape_string($status);

if ($mode === 'edit' && $id > 0) {
    // UPDATE
    $sql = "UPDATE layanan SET 
                nama = ?, 
                slug = ?, 
                deskripsi_singkat = ?, 
                deskripsi_lengkap = ?, 
                harga_mulai = ?, 
                icon = ?, 
                urutan = ?, 
                status = ? 
            WHERE id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ssssiisii', 
        $nama, $slug, $deskripsi_singkat, $deskripsi_lengkap, 
        $harga_mulai, $icon, $urutan, $status, $id
    );

    if ($stmt->execute()) {
        set_flash('success', '✅ Layanan <strong>' . htmlspecialchars($nama) . '</strong> berhasil diupdate!');
    } else {
        set_flash('danger', '❌ Gagal mengupdate: ' . $stmt->error);
    }
    redirect(ADMIN_URL . 'layanan.php');

} else {
    // INSERT
    $sql = "INSERT INTO layanan 
                (nama, slug, deskripsi_singkat, deskripsi_lengkap, harga_mulai, icon, urutan, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('sssssiss', 
        $nama, $slug, $deskripsi_singkat, $deskripsi_lengkap, 
        $harga_mulai, $icon, $urutan, $status
    );

    if ($stmt->execute()) {
        set_flash('success', '✅ Layanan <strong>' . htmlspecialchars($nama) . '</strong> berhasil ditambahkan!');
    } else {
        set_flash('danger', '❌ Gagal menyimpan: ' . $stmt->error);
    }
    redirect(ADMIN_URL . 'layanan.php');
}
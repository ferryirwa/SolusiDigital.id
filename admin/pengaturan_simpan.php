<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'pengaturan.php');
}

// ============ AMBIL DATA ============
$id                = (int)($_POST['id'] ?? 0);
$nama_situs        = trim($_POST['nama_situs'] ?? '');
$deskripsi         = trim($_POST['deskripsi'] ?? '');
$email             = trim($_POST['email'] ?? '');
$whatsapp          = trim($_POST['whatsapp'] ?? '');
$alamat            = trim($_POST['alamat'] ?? '');
$facebook          = trim($_POST['facebook'] ?? '');
$instagram         = trim($_POST['instagram'] ?? '');
$meta_keyword      = trim($_POST['meta_keyword'] ?? '');
$meta_description  = trim($_POST['meta_description'] ?? '');
$logo_lama         = trim($_POST['logo_lama'] ?? '');
$hapus_logo        = isset($_POST['hapus_logo']) && $_POST['hapus_logo'] == '1';

// ============ VALIDASI ============
$errors = [];

if (empty($nama_situs)) {
    $errors[] = 'Nama situs wajib diisi.';
} elseif (strlen($nama_situs) > 100) {
    $errors[] = 'Nama situs maksimal 100 karakter.';
}

if (empty($deskripsi)) {
    $errors[] = 'Deskripsi wajib diisi.';
} elseif (strlen($deskripsi) > 500) {
    $errors[] = 'Deskripsi maksimal 500 karakter.';
}

if (empty($email)) {
    $errors[] = 'Email wajib diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}

if (empty($whatsapp)) {
    $errors[] = 'Nomor WhatsApp wajib diisi.';
} else {
    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);
    $whatsapp = ltrim($whatsapp, '0');
    $whatsapp = ltrim($whatsapp, '62');
    if (strlen($whatsapp) < 8 || strlen($whatsapp) > 15) {
        $errors[] = 'Nomor WhatsApp tidak valid (8-15 digit).';
    }
}

if (!empty($facebook) && !filter_var($facebook, FILTER_VALIDATE_URL)) {
    $errors[] = 'URL Facebook tidak valid.';
}
if (!empty($instagram) && !filter_var($instagram, FILTER_VALIDATE_URL)) {
    $errors[] = 'URL Instagram tidak valid.';
}

// ============ HANDLE UPLOAD LOGO ============
$nama_logo = $logo_lama;
$folder_logo = UPLOAD_PATH . 'logo/';

if (!is_dir($folder_logo)) {
    mkdir($folder_logo, 0755, true);
}

// Hapus logo lama kalau dicentang
if ($hapus_logo && (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE)) {
    if (!empty($logo_lama) && file_exists($folder_logo . $logo_lama)) {
        @unlink($folder_logo . $logo_lama);
    }
    $nama_logo = '';
}

// Upload logo baru
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['logo'];

    // Cek ukuran (1MB)
    if ($file['size'] > 1024 * 1024) {
        $errors[] = 'Ukuran logo terlalu besar (max 1MB).';
    }

    // Cek ekstensi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
    if (!in_array($ext, $allowed_ext)) {
        $errors[] = 'Format logo harus PNG, JPG, SVG, atau WEBP.';
    }

    if (empty($errors)) {
        $nama_logo_baru = 'logo_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $path_tujuan = $folder_logo . $nama_logo_baru;

        if (move_uploaded_file($file['tmp_name'], $path_tujuan)) {
            // Hapus logo lama
            if (!empty($logo_lama) && file_exists($folder_logo . $logo_lama)) {
                @unlink($folder_logo . $logo_lama);
            }
            $nama_logo = $nama_logo_baru;
        } else {
            $errors[] = 'Gagal mengupload logo.';
        }
    }
}

// ============ JIKA ADA ERROR ============
if (!empty($errors)) {
    $_SESSION['form_pengaturan'] = $_POST;
    set_flash('danger', 'Periksa kembali:<br>• ' . implode('<br>• ', $errors));
    redirect(ADMIN_URL . 'pengaturan.php');
}

// ============ SIMPAN KE DATABASE ============
// Cek apakah data pengaturan sudah ada
$cek = $koneksi->query("SELECT id FROM pengaturan LIMIT 1");
$data_ada = $cek && $cek->num_rows > 0;

if ($data_ada) {
    // UPDATE
    $sql = "UPDATE pengaturan SET 
                nama_situs = ?, 
                logo = ?, 
                deskripsi = ?, 
                alamat = ?, 
                email = ?, 
                whatsapp = ?, 
                facebook = ?, 
                instagram = ?, 
                meta_keyword = ?, 
                meta_description = ? 
            WHERE id = ?";

    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ssssssssssi',
        $nama_situs, $nama_logo, $deskripsi, $alamat, $email,
        $whatsapp, $facebook, $instagram, $meta_keyword, $meta_description, $id
    );

    if ($stmt->execute()) {
        set_flash('success', '✅ Pengaturan website berhasil disimpan!');
    } else {
        set_flash('danger', '❌ Gagal menyimpan: ' . $stmt->error);
    }
} else {
    // INSERT
    $sql = "INSERT INTO pengaturan 
                (nama_situs, logo, deskripsi, alamat, email, whatsapp, facebook, instagram, meta_keyword, meta_description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ssssssssss',
        $nama_situs, $nama_logo, $deskripsi, $alamat, $email,
        $whatsapp, $facebook, $instagram, $meta_keyword, $meta_description
    );

    if ($stmt->execute()) {
        set_flash('success', '✅ Pengaturan website berhasil disimpan!');
    } else {
        set_flash('danger', '❌ Gagal menyimpan: ' . $stmt->error);
    }
}

redirect(ADMIN_URL . 'pengaturan.php');
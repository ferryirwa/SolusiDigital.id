<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'pesan');
}

// ============ AMBIL DATA ============
$nama_klien     = trim($_POST['nama_klien'] ?? '');
$email          = trim($_POST['email'] ?? '');
$whatsapp       = trim($_POST['whatsapp'] ?? '');
$layanan_id     = (int)($_POST['layanan_id'] ?? 0);
$budget         = (int)($_POST['budget'] ?? 0);
$target_selesai = trim($_POST['target_selesai'] ?? '');
$deskripsi      = trim($_POST['deskripsi'] ?? '');
$setuju         = isset($_POST['setuju']) && $_POST['setuju'] == '1';

// ============ VALIDASI ============
$errors = [];

if (empty($nama_klien)) {
    $errors[] = 'Nama lengkap wajib diisi.';
} elseif (strlen($nama_klien) < 3 || strlen($nama_klien) > 100) {
    $errors[] = 'Nama harus 3-100 karakter.';
}

if (empty($email)) {
    $errors[] = 'Email wajib diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid.';
}

// Bersihkan nomor WA (format lokal tanpa 0/62, sesuai tampilan "+62" di form)
$whatsapp = normalisasi_wa($whatsapp);
if (empty($whatsapp)) {
    $errors[] = 'Nomor WhatsApp wajib diisi.';
} elseif (strlen($whatsapp) < 8 || strlen($whatsapp) > 15) {
    $errors[] = 'Nomor WhatsApp tidak valid (8-15 digit).';
}

if ($layanan_id <= 0) {
    $errors[] = 'Pilih jenis layanan.';
} else {
    // Cek layanan exist
    $cek_l = $koneksi->prepare("SELECT id FROM layanan WHERE id = ? AND status='aktif' LIMIT 1");
    $cek_l->bind_param('i', $layanan_id);
    $cek_l->execute();
    if ($cek_l->get_result()->num_rows === 0) {
        $errors[] = 'Layanan yang dipilih tidak valid.';
    }
}

if ($budget <= 0) {
    $errors[] = 'Budget wajib diisi dan lebih dari 0.';
}

if (empty($deskripsi)) {
    $errors[] = 'Deskripsi kebutuhan wajib diisi.';
} elseif (strlen($deskripsi) < 20) {
    $errors[] = 'Deskripsi minimal 20 karakter, jelaskan lebih detail.';
} elseif (strlen($deskripsi) > 3000) {
    $errors[] = 'Deskripsi maksimal 3000 karakter.';
}

if (!empty($target_selesai)) {
    $ts = strtotime($target_selesai);
    if ($ts === false || $ts < strtotime('+3 days')) {
        $errors[] = 'Target selesai minimal 3 hari dari sekarang.';
    }
}

if (!$setuju) {
    $errors[] = 'Anda harus menyetujui penggunaan data.';
}

// ============ HANDLE UPLOAD LAMPIRAN ============
$nama_lampiran = '';
$folder_pesanan = UPLOAD_PATH . 'pesanan/';

if (!is_dir($folder_pesanan)) {
    mkdir($folder_pesanan, 0755, true);
}

if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['lampiran'];

    // Cek ukuran (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Ukuran lampiran terlalu besar (max 5MB).';
    }

    // Cek ekstensi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
    if (!in_array($ext, $allowed_ext)) {
        $errors[] = 'Format lampiran harus PDF, DOC, DOCX, JPG, PNG, ZIP, atau RAR.';
    }

    // Cek MIME
    $mime = mime_content_type($file['tmp_name']);
    $allowed_mime = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'application/zip',
        'application/x-rar-compressed',
        'application/vnd.rar',
        'application/octet-stream' // fallback untuk zip/rar
    ];
    if (!in_array($mime, $allowed_mime)) {
        $errors[] = 'File lampiran tidak valid.';
    }
}

// ============ JIKA ADA ERROR ============
if (!empty($errors)) {
    $_SESSION['form_pesan'] = $_POST;
    set_flash('danger', 'Periksa kembali:<br>• ' . implode('<br>• ', $errors));
    redirect(BASE_URL . 'pesan');
}

// ============ UPLOAD LAMPIRAN (kalau valid) ============
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['lampiran'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $nama_lampiran = date('YmdHis') . '_' . uniqid() . '.' . $ext;
    $path_tujuan = $folder_pesanan . $nama_lampiran;

    if (!move_uploaded_file($file['tmp_name'], $path_tujuan)) {
        $nama_lampiran = '';
    }
}

// ============ GENERATE KODE PESANAN ============
// Format: ORD-YYYYMMDD-XXX
$kode_pesanan = generate_kode_pesanan($koneksi);

// Pastikan unik
$max_loop = 10;
while ($max_loop > 0) {
    $cek = $koneksi->prepare("SELECT id FROM pesanan WHERE kode_pesanan = ?");
    $cek->bind_param('s', $kode_pesanan);
    $cek->execute();
    if ($cek->get_result()->num_rows === 0) break;
    $kode_pesanan = generate_kode_pesanan($koneksi);
    $max_loop--;
}

// ============ SIMPAN KE DATABASE ============
$target_final = !empty($target_selesai) ? $target_selesai : null;

$sql = "INSERT INTO pesanan 
            (kode_pesanan, nama_klien, email, whatsapp, layanan_id, budget, deskripsi, file_lampiran, target_selesai, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'baru')";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param('ssssiisss', 
    $kode_pesanan, $nama_klien, $email, $whatsapp, 
    $layanan_id, $budget, $deskripsi, $nama_lampiran, $target_final
);

if (!$stmt->execute()) {
    set_flash('danger', 'Gagal menyimpan pesanan: ' . $stmt->error);
    redirect(BASE_URL . 'pesan');
}

$id_pesanan = $stmt->insert_id;

// ============ SIMPAN KE SESSION UNTUK HALAMAN SUKSES ============
$_SESSION['pesanan_sukses'] = [
    'id' => $id_pesanan,
    'kode' => $kode_pesanan,
    'nama' => $nama_klien,
    'email' => $email,
    'whatsapp' => $whatsapp,
    'layanan_id' => $layanan_id,
    'budget' => $budget,
    'waktu' => date('Y-m-d H:i:s'),
];

// ============ KIRIM NOTIF EMAIL KE ADMIN (opsional) ============
// Bisa diaktifkan kalau sudah konfigurasi mail server
// @mail($pengaturan['email'], "Pesanan Baru: $kode_pesanan", "Ada pesanan baru...", "From: noreply@website.com");

// ============ KIRIM NOTIF WA (redirect otomatis ke WA admin) ============
// Ini akan di-handle di halaman sukses dengan tombol WA

// ============ REDIRECT KE HALAMAN SUKSES ============
redirect(BASE_URL . 'pesan/sukses');
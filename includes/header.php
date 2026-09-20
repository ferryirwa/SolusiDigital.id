<?php
/**
 *
 * Header Template — Frontend
 * Website Jasa Programmer
 */
/** @var array $pengaturan */
/** @var mysqli $koneksi */

// ============ GUARD: Pastikan dependency sudah ada ============
if (!isset($koneksi)) {
    die('Error: File koneksi.php belum di-include sebelum header.php');
}

if (!defined('BASE_URL')) {
    die('Error: konfigurasi.php belum di-include sebelum header.php');
}

// ============ AMBIL DATA PENGATURAN ============
$pengaturan = null;

$pengaturan_q = $koneksi->query("SELECT * FROM pengaturan LIMIT 1");
if ($pengaturan_q && $pengaturan_q->num_rows > 0) {
    $pengaturan = $pengaturan_q->fetch_assoc();
}

// Fallback default kalau tabel pengaturan kosong / error
/** @var array $pengaturan */
if (empty($pengaturan) || !is_array($pengaturan)) {
    $pengaturan = [
        'nama_situs'       => defined('SITE_NAME') ? SITE_NAME : 'JasaProgrammer.id',
        'logo'             => '',
        'deskripsi'        => defined('SITE_DESC') ? SITE_DESC : 'Jasa Pembuatan Website & Aplikasi',
        'whatsapp'         => '6281234567890',
        'email'            => 'info@website.com',
        'alamat'           => 'Jakarta, Indonesia',
        'facebook'         => '',
        'instagram'        => '',
        'meta_keyword'     => 'jasa website, jasa android, jasa programmer',
        'meta_description' => defined('SITE_DESC') ? SITE_DESC : '',
    ];
}

// ============ META DINAMIS (bisa di-override sebelum include) ============
$page_title = isset($page_title) ? $page_title : $pengaturan['nama_situs'];
$page_desc  = isset($page_desc)  ? $page_desc  : ($pengaturan['meta_description'] ?? '');

// Gambar OG: pakai logo yang diupload kalau ada, kalau tidak pakai favicon
$logo_terpasang = !empty($pengaturan['logo']) ? UPLOADS_URL . 'logo/' . $pengaturan['logo'] : '';
$page_img = isset($page_img) ? $page_img : ($logo_terpasang ?: ASSETS_URL . 'img/favicon.png');

// ============ HALAMAN AKTIF ============
$halaman_aktif = basename($_SERVER['PHP_SELF'], '.php');

// Deteksi halaman detail (slug)
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($request_uri, '/layanan/') !== false) $halaman_aktif = 'layanan';
if (strpos($request_uri, '/portfolio/') !== false) $halaman_aktif = 'portfolio';
if (strpos($request_uri, '/artikel/') !== false) $halaman_aktif = 'artikel';
if (strpos($request_uri, '/pesan') !== false) $halaman_aktif = 'pesan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO -->
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pengaturan['meta_keyword'] ?? '') ?>">
    <meta name="author" content="<?= htmlspecialchars($pengaturan['nama_situs']) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page_img) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>img/favicon.png">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>">
            <i class="bi bi-code-slash"></i> 
            <?= htmlspecialchars($pengaturan['nama_situs']) ?>
        </a>
        <button class="navbar-toggler border-0" type="button" 
                data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'index' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'layanan' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>layanan">Layanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'portfolio' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>portfolio">Portfolio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'artikel' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>artikel">Artikel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'tentang' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>tentang">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif === 'kontak' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>kontak">Kontak</a>
                </li>
                
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a href="<?= BASE_URL ?>pesan" class="btn btn-primary">
                        <i class="bi bi-cart-check"></i> Pesan Sekarang
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
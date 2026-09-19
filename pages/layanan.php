<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

$page_title = 'Layanan — ' . SITE_NAME;
$page_desc  = 'Daftar lengkap layanan jasa pembuatan website, aplikasi Android, hosting, dan layanan IT profesional.';

$layanan = $koneksi->query("SELECT * FROM layanan WHERE status='aktif' ORDER BY urutan ASC");

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Layanan Kami</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Layanan</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Layanan -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Layanan</span>
            <h2>Solusi Digital Lengkap</h2>
            <p>Pilih layanan yang sesuai dengan kebutuhan bisnis Anda</p>
        </div>
        
        <div class="row g-4">
            <?php while ($l = $layanan->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card-service">
                        <div class="icon">
                            <i class="bi <?= htmlspecialchars($l['icon']) ?>"></i>
                        </div>
                        <h5><?= htmlspecialchars($l['nama']) ?></h5>
                        <p><?= htmlspecialchars($l['deskripsi_singkat']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="price">Mulai <?= rupiah($l['harga_mulai']) ?></div>
                            <a href="<?= BASE_URL ?>layanan/<?= $l['slug'] ?>" class="btn btn-outline-primary btn-sm">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="container">
    <div class="cta-section text-center">
        <div class="container">
            <h2>Butuh Layanan Custom?</h2>
            <p class="lead mb-4">Kami siap membuat solusi khusus sesuai kebutuhan Anda.</p>
            <a href="<?= BASE_URL ?>kontak" class="btn btn-accent btn-lg">
                <i class="bi bi-chat-dots"></i> Hubungi Kami
            </a>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
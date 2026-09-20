<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

$slug = isset($_GET['slug']) ? clean($koneksi, $_GET['slug']) : '';
if (empty($slug)) {
    redirect(BASE_URL . 'portfolio');
}

// Ambil data portfolio
$stmt = $koneksi->prepare("SELECT * FROM portfolio WHERE slug = ? LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Portfolio tidak ditemukan.');
    redirect(BASE_URL . 'portfolio');
}

$p = $result->fetch_assoc();

$page_title = $p['judul'] . ' — ' . SITE_NAME;
$page_desc  = potong_teks(strip_tags($p['deskripsi']), 150);

// Portfolio lain
$portfolio_lain = $koneksi->query("SELECT * FROM portfolio WHERE id != {$p['id']} ORDER BY created_at DESC LIMIT 3");

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 style="font-size: 1.8rem;"><?= htmlspecialchars($p['judul']) ?></h1>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>portfolio">Portfolio</a></li>
                <li class="breadcrumb-item active"><?= potong_teks($p['judul'], 30) ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-service">
                    <?php if (!empty($p['gambar']) && file_exists(PORTFOLIO_PATH . $p['gambar'])): ?>
                        <img src="<?= UPLOADS_URL ?>portfolio/<?= htmlspecialchars($p['gambar']) ?>"
                             alt="<?= htmlspecialchars($p['judul']) ?>"
                             class="w-100 rounded mb-4" style="max-height: 450px; object-fit: cover;">
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php if (!empty($p['kategori'])): ?>
                            <span class="tech-badge"><?= ucfirst(htmlspecialchars($p['kategori'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($p['teknologi'])): ?>
                            <?php foreach (explode(',', $p['teknologi']) as $tech): ?>
                                <span class="tech-badge"><?= htmlspecialchars(trim($tech)) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h2><?= htmlspecialchars($p['judul']) ?></h2>

                    <div class="row g-3 my-4">
                        <?php if (!empty($p['klien'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Klien</small>
                                <div class="fw-semibold">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($p['klien']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($p['tanggal_selesai'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Tanggal Selesai</small>
                                <div class="fw-semibold">
                                    <i class="bi bi-calendar"></i> <?= tanggal_indo($p['tanggal_selesai']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-5">

                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>portfolio" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Semua Portfolio
                        </a>
                        <div class="d-flex gap-2">
                            <?php if (!empty($p['link_demo'])): ?>
                                <a href="<?= htmlspecialchars($p['link_demo']) ?>" target="_blank"
                                   rel="noopener" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-up-right"></i> Lihat Demo
                                </a>
                            <?php endif; ?>
                            <a href="<?= link_wa($pengaturan['whatsapp'], 'Halo, saya melihat portfolio ' . $p['judul'] . '. Saya ingin konsultasi proyek serupa.') ?>"
                               target="_blank" class="btn btn-success">
                                <i class="bi bi-whatsapp"></i> Konsultasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-service card-service-sm mb-3">
                    <h5>Portfolio Lainnya</h5>
                    <hr>
                    <?php if ($portfolio_lain && $portfolio_lain->num_rows > 0): ?>
                        <?php while ($pl = $portfolio_lain->fetch_assoc()): ?>
                            <a href="<?= BASE_URL ?>portfolio/<?= $pl['slug'] ?>"
                               class="d-block mb-3 text-decoration-none">
                                <div class="fw-semibold small text-dark"><?= potong_teks($pl['judul'], 60) ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-tag"></i> <?= ucfirst(htmlspecialchars($pl['kategori'])) ?>
                                </small>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="small text-muted mb-0">Belum ada portfolio lain.</p>
                    <?php endif; ?>
                </div>

                <div class="card-service card-service-sm">
                    <h5>Butuh Proyek Serupa?</h5>
                    <p class="small text-muted">Konsultasi gratis untuk kebutuhan digital Anda.</p>
                    <a href="<?= BASE_URL ?>pesan" class="btn btn-primary w-100">
                        <i class="bi bi-cart-check"></i> Pesan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
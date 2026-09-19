<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

$slug = isset($_GET['slug']) ? clean($koneksi, $_GET['slug']) : '';
if (empty($slug)) {
    redirect(BASE_URL . 'layanan');
}

// Ambil data layanan
$stmt = $koneksi->prepare("SELECT * FROM layanan WHERE slug = ? AND status='aktif' LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Layanan tidak ditemukan.');
    redirect(BASE_URL . 'layanan');
}

$layanan = $result->fetch_assoc();

$page_title = $layanan['nama'] . ' — ' . SITE_NAME;
$page_desc  = $layanan['deskripsi_singkat'];

// Layanan lain
$layanan_lain = $koneksi->query("SELECT * FROM layanan WHERE status='aktif' AND id != {$layanan['id']} ORDER BY urutan ASC LIMIT 4");

require_once '../includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($layanan['nama']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>layanan">Layanan</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($layanan['nama']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Detail -->
<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-service">
                    <div class="icon mb-3" style="width: 80px; height: 80px; font-size: 40px;">
                        <i class="bi <?= htmlspecialchars($layanan['icon']) ?>"></i>
                    </div>
                    <h2><?= htmlspecialchars($layanan['nama']) ?></h2>
                    <p class="lead"><?= htmlspecialchars($layanan['deskripsi_singkat']) ?></p>
                    <hr>
                    <div style="line-height: 1.8; font-size: 15px;">
                        <?= nl2br(htmlspecialchars($layanan['deskripsi_lengkap'])) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-service sticky-top" style="top: 100px;">
                    <h5>Mulai dari</h5>
                    <h2 class="text-primary mb-3"><?= rupiah($layanan['harga_mulai']) ?></h2>
                    <p class="small text-muted">
                        Harga dapat menyesuaikan dengan kompleksitas kebutuhan Anda.
                    </p>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>pesan?layanan=<?= $layanan['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-cart-check"></i> Pesan Layanan Ini
                        </a>
                        <a href="<?= link_wa($pengaturan['whatsapp'], 'Halo, saya tertarik dengan layanan ' . $layanan['nama'] . '. Bisa minta info lebih detail?') ?>"
                           target="_blank" class="btn btn-outline-success">
                            <i class="bi bi-whatsapp"></i> Tanya via WA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layanan Lain -->
        <?php if ($layanan_lain->num_rows > 0): ?>
            <div class="mt-5">
                <h3 class="mb-4">Layanan Lainnya</h3>
                <div class="row g-4">
                    <?php while ($ll = $layanan_lain->fetch_assoc()): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card-service">
                                <div class="icon" style="width: 50px; height: 50px; font-size: 24px;">
                                    <i class="bi <?= htmlspecialchars($ll['icon']) ?>"></i>
                                </div>
                                <h5 class="mt-2" style="font-size: 1rem;"><?= htmlspecialchars($ll['nama']) ?></h5>
                                <a href="<?= BASE_URL ?>layanan/<?= $ll['slug'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    Lihat
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
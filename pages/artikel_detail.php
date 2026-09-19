<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */
$slug = isset($_GET['slug']) ? clean($koneksi, $_GET['slug']) : '';
if (empty($slug)) redirect(BASE_URL . 'artikel');

$stmt = $koneksi->prepare("SELECT * FROM artikel WHERE slug = ? AND status='publish' LIMIT 1");
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Artikel tidak ditemukan.');
    redirect(BASE_URL . 'artikel');
}

$a = $result->fetch_assoc();

// Increment views
$koneksi->query("UPDATE artikel SET views = views + 1 WHERE id = {$a['id']}");

$page_title = $a['judul'] . ' — ' . SITE_NAME;
$page_desc  = potong_teks(strip_tags($a['konten']), 150);

$artikel_lain = $koneksi->query("SELECT * FROM artikel WHERE id != {$a['id']} AND status='publish' ORDER BY created_at DESC LIMIT 3");

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 style="font-size: 1.8rem;"><?= htmlspecialchars($a['judul']) ?></h1>
        <div class="mt-2">
            <small>
                <i class="bi bi-person"></i> <?= htmlspecialchars($a['penulis']) ?>
                · <i class="bi bi-calendar"></i> <?= tanggal_indo($a['created_at']) ?>
                · <i class="bi bi-eye"></i> <?= number_format($a['views'] + 1) ?> views
            </small>
        </div>
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>artikel">Artikel</a></li>
                <li class="breadcrumb-item active"><?= potong_teks($a['judul'], 30) ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (!empty($a['gambar']) && file_exists(ARTIKEL_PATH . $a['gambar'])): ?>
                    <img src="<?= UPLOADS_URL ?>artikel/<?= htmlspecialchars($a['gambar']) ?>" 
                         class="w-100 rounded mb-4" style="max-height: 450px; object-fit: cover;">
                <?php endif; ?>

                <div class="artikel-konten" style="line-height: 1.9; font-size: 16px;">
                    <?= $a['konten'] ?>
                </div>

                <hr class="my-5">

                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>artikel" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Semua Artikel
                    </a>
                    <a href="<?= link_wa($pengaturan['whatsapp'], 'Saya baca artikel: ' . $a['judul'] . '. Menarik!') ?>" 
                       target="_blank" class="btn btn-success">
                        <i class="bi bi-whatsapp"></i> Diskusi
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-service mb-3">
                    <h5>Artikel Lainnya</h5>
                    <hr>
                    <?php while ($al = $artikel_lain->fetch_assoc()): ?>
                        <a href="<?= BASE_URL ?>artikel/<?= $al['slug'] ?>" 
                           class="d-block mb-3 text-decoration-none">
                            <div class="fw-semibold small text-dark"><?= potong_teks($al['judul'], 60) ?></div>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <?= tanggal_indo($al['created_at']) ?>
                            </small>
                        </a>
                    <?php endwhile; ?>
                </div>

                <div class="card-service">
                    <h5>Butuh Bantuan?</h5>
                    <p class="small text-muted">Konsultasi gratis untuk kebutuhan digital Anda.</p>
                    <a href="<?= BASE_URL ?>kontak" class="btn btn-primary w-100">
                        <i class="bi bi-chat-dots"></i> Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.artikel-konten h2 { font-size: 1.5rem; margin-top: 1.5rem; }
.artikel-konten h3 { font-size: 1.2rem; margin-top: 1.2rem; }
.artikel-konten p { margin-bottom: 1rem; }
.artikel-konten img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
.artikel-konten ul, .artikel-konten ol { padding-left: 25px; margin-bottom: 1rem; }
.artikel-konten a { color: #0D6EFD; text-decoration: underline; }
</style>

<?php require_once '../includes/footer.php'; ?>
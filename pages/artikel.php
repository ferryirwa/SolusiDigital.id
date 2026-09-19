<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

$page_title = 'Artikel — ' . SITE_NAME;
$page_desc  = 'Tips, tutorial, dan wawasan seputar dunia digital, website, dan aplikasi.';

$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$where = "WHERE status='publish'";
if ($cari !== '') {
    $where .= " AND (judul LIKE '%$cari%' OR konten LIKE '%$cari%')";
}

$artikel = $koneksi->query("SELECT * FROM artikel $where ORDER BY created_at DESC");

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Artikel & Blog</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Artikel</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- Search -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-6">
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="cari" class="form-control" 
                               placeholder="Cari artikel..." 
                               value="<?= htmlspecialchars($cari) ?>">
                        <button class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($artikel->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($a = $artikel->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card-portfolio">
                            <div class="thumb">
                                <?php if (!empty($a['gambar']) && file_exists(ARTIKEL_PATH . $a['gambar'])): ?>
                                    <img src="<?= UPLOADS_URL ?>artikel/<?= htmlspecialchars($a['gambar']) ?>" 
                                         alt="<?= htmlspecialchars($a['judul']) ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                        <i class="bi bi-journal-text" style="font-size: 60px; opacity: 0.3;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="body">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> <?= tanggal_indo($a['created_at']) ?>
                                    · <i class="bi bi-eye"></i> <?= number_format($a['views']) ?>
                                </small>
                                <h5 class="mt-2"><?= htmlspecialchars($a['judul']) ?></h5>
                                <p class="small text-muted">
                                    <?= potong_teks(strip_tags($a['konten']), 100) ?>
                                </p>
                                <a href="<?= BASE_URL ?>artikel/<?= $a['slug'] ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    Baca <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-journal-x fs-1 d-block mb-3 text-muted"></i>
                <h5 class="text-muted">Belum ada artikel</h5>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
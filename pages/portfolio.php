<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

$page_title = 'Portfolio — ' . SITE_NAME;
$page_desc  = 'Galeri proyek website dan aplikasi yang telah kami kerjakan untuk klien dari berbagai industri.';

$kategori_filter = isset($_GET['kategori']) ? clean($koneksi, $_GET['kategori']) : '';

$where = $kategori_filter ? "WHERE kategori = '$kategori_filter'" : '';
$portfolio = $koneksi->query("SELECT * FROM portfolio $where ORDER BY created_at DESC");
$kategori_list = $koneksi->query("SELECT DISTINCT kategori FROM portfolio WHERE kategori IS NOT NULL ORDER BY kategori ASC");

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Portfolio Kami</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Portfolio</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Filter Kategori -->
        <div class="text-center mb-5">
            <a href="<?= BASE_URL ?>portfolio" 
               class="btn <?= empty($kategori_filter) ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm me-2 mb-2">
                Semua
            </a>
            <?php while ($k = $kategori_list->fetch_assoc()): ?>
                <a href="<?= BASE_URL ?>portfolio?kategori=<?= urlencode($k['kategori']) ?>" 
                   class="btn <?= $kategori_filter === $k['kategori'] ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm me-2 mb-2">
                    <?= ucfirst(htmlspecialchars($k['kategori'])) ?>
                </a>
            <?php endwhile; ?>
        </div>

        <!-- List Portfolio -->
        <?php if ($portfolio->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($p = $portfolio->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card-portfolio">
                            <div class="thumb">
                                <?php if (!empty($p['gambar']) && file_exists(PORTFOLIO_PATH . $p['gambar'])): ?>
                                    <img src="<?= UPLOADS_URL ?>portfolio/<?= htmlspecialchars($p['gambar']) ?>" 
                                         alt="<?= htmlspecialchars($p['judul']) ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                        <i class="bi bi-image" style="font-size: 60px; opacity: 0.3;"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="badge-cat"><?= ucfirst(htmlspecialchars($p['kategori'])) ?></span>
                            </div>
                            <div class="body">
                                <h5><?= htmlspecialchars($p['judul']) ?></h5>
                                <?php if (!empty($p['klien'])): ?>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($p['klien']) ?>
                                    </small>
                                <?php endif; ?>
                                <p class="small text-muted"><?= potong_teks($p['deskripsi'], 90) ?></p>
                                <?php if (!empty($p['teknologi'])): ?>
                                    <div class="mb-3">
                                        <?php foreach (array_slice(explode(',', $p['teknologi']), 0, 3) as $tech): ?>
                                            <span class="tech-badge"><?= htmlspecialchars(trim($tech)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>portfolio/<?= $p['slug'] ?>" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    Lihat Detail <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-images fs-1 d-block mb-3 text-muted"></i>
                <h5 class="text-muted">Belum ada portfolio</h5>
                <p class="text-muted">Portfolio akan segera ditampilkan.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
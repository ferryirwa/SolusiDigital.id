<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Filter & pencarian
$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$kategori_filter = isset($_GET['kategori']) ? clean($koneksi, $_GET['kategori']) : '';
$view = isset($_GET['view']) && $_GET['view'] === 'grid' ? 'grid' : 'list';

// Query dengan kondisi
$where = [];
if ($cari !== '') {
    $where[] = "(judul LIKE '%$cari%' OR klien LIKE '%$cari%' OR teknologi LIKE '%$cari%')";
}
if ($kategori_filter !== '') {
    $where[] = "kategori = '$kategori_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Query portfolio dengan error handling
$query = $koneksi->query("SELECT * FROM portfolio $where_sql ORDER BY created_at DESC");
if (!$query) {
    die('<div style="padding:20px;font-family:sans-serif;">
        <h3 style="color:red;">Error Query Portfolio</h3>
        <p><strong>Pesan:</strong> ' . htmlspecialchars($koneksi->error) . '</p>
        <p><strong>Query:</strong> SELECT * FROM portfolio ' . htmlspecialchars($where_sql) . ' ORDER BY created_at DESC</p>
    </div>');
}

$total = $query->num_rows;

// Simpan data ke array untuk dipakai di 2 view
$data_portfolio = [];
while ($row = $query->fetch_assoc()) {
    $data_portfolio[] = $row;
}

// Ambil kategori unik (dengan handling)
$kategori_list = $koneksi->query("SELECT DISTINCT kategori FROM portfolio WHERE kategori IS NOT NULL ORDER BY kategori ASC");
$kategori_data = [];
if ($kategori_list && $kategori_list->num_rows > 0) {
    while ($k = $kategori_list->fetch_assoc()) {
        $kategori_data[] = $k;
    }
}
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-images text-primary"></i> Kelola Portfolio
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <!-- Header Action -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Daftar Portfolio</h5>
            <small class="text-muted">Total: <strong><?= $total ?></strong> portfolio</small>
        </div>
        <div class="d-flex gap-2">
            <!-- Toggle View -->
            <div class="btn-group" role="group">
                <a href="?view=list&cari=<?= urlencode($cari) ?>&kategori=<?= urlencode($kategori_filter) ?>" 
                   class="btn btn-sm <?= $view === 'list' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <i class="bi bi-list-ul"></i>
                </a>
                <a href="?view=grid&cari=<?= urlencode($cari) ?>&kategori=<?= urlencode($kategori_filter) ?>" 
                   class="btn btn-sm <?= $view === 'grid' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <i class="bi bi-grid-3x3-gap"></i>
                </a>
            </div>
            <a href="portfolio_form.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Portfolio
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <input type="hidden" name="view" value="<?= $view ?>">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" 
                               placeholder="Cari judul, klien, atau teknologi..." 
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php if (!empty($kategori_data)): ?>
                            <?php foreach ($kategori_data as $k): ?>
                                <option value="<?= htmlspecialchars($k['kategori']) ?>" 
                                        <?= $kategori_filter === $k['kategori'] ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($k['kategori'])) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="portfolio.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($total > 0): ?>

        <?php if ($view === 'grid'): ?>
            <!-- ============ GRID VIEW ============ -->
            <div class="row g-3">
                <?php foreach ($data_portfolio as $p): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <!-- Thumbnail -->
                            <div style="height: 200px; overflow: hidden; position: relative; background: #f0f0f0;">
                                <?php if (!empty($p['gambar']) && file_exists(PORTFOLIO_PATH . $p['gambar'])): ?>
                                    <img src="<?= UPLOADS_URL ?>portfolio/<?= htmlspecialchars($p['gambar']) ?>" 
                                         alt="<?= htmlspecialchars($p['judul']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                        <i class="bi bi-image" style="font-size: 60px; opacity: 0.3;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Kategori Badge -->
                                <span class="badge bg-primary" style="position: absolute; top: 10px; left: 10px;">
                                    <?= ucfirst(htmlspecialchars($p['kategori'])) ?>
                                </span>
                            </div>

                            <div class="card-body">
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['judul']) ?></h6>
                                <?php if (!empty($p['klien'])): ?>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($p['klien']) ?>
                                    </small>
                                <?php endif; ?>
                                
                                <p class="small text-muted mb-2">
                                    <?= potong_teks($p['deskripsi'], 80) ?>
                                </p>

                                <?php if (!empty($p['teknologi'])): ?>
                                    <div class="mb-2">
                                        <?php foreach (explode(',', $p['teknologi']) as $tech): ?>
                                            <span class="badge bg-light text-dark border me-1 mb-1" style="font-size: 10px;">
                                                <?= htmlspecialchars(trim($tech)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?= $p['tanggal_selesai'] ? tanggal_indo($p['tanggal_selesai']) : '-' ?>
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!empty($p['link_demo'])): ?>
                                            <a href="<?= htmlspecialchars($p['link_demo']) ?>" 
                                               target="_blank" class="btn btn-outline-info" title="Lihat Demo">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="portfolio_form.php?id=<?= $p['id'] ?>" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="portfolio_hapus.php?id=<?= $p['id'] ?>" 
                                           class="btn btn-danger" title="Hapus"
                                           onclick="return confirm('Yakin hapus portfolio \'<?= htmlspecialchars($p['judul']) ?>\'?\n\nData dan gambar akan dihapus permanen!')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- ============ LIST VIEW ============ -->
            <div class="card stat-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="100">Gambar</th>
                                    <th>Judul & Klien</th>
                                    <th width="120">Kategori</th>
                                    <th>Teknologi</th>
                                    <th width="120">Tgl Selesai</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data_portfolio as $p): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($p['gambar']) && file_exists(PORTFOLIO_PATH . $p['gambar'])): ?>
                                                <img src="<?= UPLOADS_URL ?>portfolio/<?= htmlspecialchars($p['gambar']) ?>" 
                                                     alt="thumb"
                                                     style="width: 70px; height: 50px; object-fit: cover; border-radius: 6px;">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center bg-light" 
                                                     style="width: 70px; height: 50px; border-radius: 6px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($p['judul']) ?></div>
                                            <?php if (!empty($p['klien'])): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-person"></i> <?= htmlspecialchars($p['klien']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?= ucfirst(htmlspecialchars($p['kategori'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['teknologi'])): ?>
                                                <?php foreach (array_slice(explode(',', $p['teknologi']), 0, 3) as $tech): ?>
                                                    <span class="badge bg-light text-dark border" style="font-size: 10px;">
                                                        <?= htmlspecialchars(trim($tech)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count(explode(',', $p['teknologi'])) > 3): ?>
                                                    <small class="text-muted">+<?= count(explode(',', $p['teknologi'])) - 3 ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= $p['tanggal_selesai'] ? tanggal_indo($p['tanggal_selesai']) : '-' ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <?php if (!empty($p['link_demo'])): ?>
                                                    <a href="<?= htmlspecialchars($p['link_demo']) ?>" 
                                                       target="_blank" class="btn btn-outline-info" title="Lihat Demo">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="portfolio_form.php?id=<?= $p['id'] ?>" 
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="portfolio_hapus.php?id=<?= $p['id'] ?>" 
                                                   class="btn btn-danger" title="Hapus"
                                                   onclick="return confirm('Yakin hapus portfolio \'<?= htmlspecialchars($p['judul']) ?>\'?\n\nData dan gambar akan dihapus permanen!')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty State -->
        <div class="card stat-card">
            <div class="card-body text-center py-5">
                <i class="bi bi-images fs-1 d-block mb-3 text-muted"></i>
                <h5 class="text-muted">Belum ada portfolio</h5>
                <p class="text-muted small mb-3">
                    <?= $cari || $kategori_filter ? 'Tidak ada hasil yang cocok dengan filter.' : 'Mulai tambahkan portfolio pertama Anda.' ?>
                </p>
                <a href="portfolio_form.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Sekarang
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
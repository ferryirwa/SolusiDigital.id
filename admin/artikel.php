<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$status_filter = isset($_GET['status']) ? clean($koneksi, $_GET['status']) : '';

$where = [];
if ($cari !== '') {
    $where[] = "(judul LIKE '%$cari%' OR konten LIKE '%$cari%')";
}
if (in_array($status_filter, ['publish', 'draft'])) {
    $where[] = "status = '$status_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = $koneksi->query("SELECT * FROM artikel $where_sql ORDER BY created_at DESC");
$total = $query->num_rows;
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-journal-text text-primary"></i> Kelola Artikel
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Daftar Artikel</h5>
            <small class="text-muted">Total: <strong><?= $total ?></strong> artikel</small>
        </div>
        <a href="artikel_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tulis Artikel
        </a>
    </div>

    <!-- Filter -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control"
                               placeholder="Cari judul atau isi artikel..."
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="publish" <?= $status_filter == 'publish' ? 'selected' : '' ?>>Publish</option>
                        <option value="draft" <?= $status_filter == 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i></button>
                    <a href="artikel.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card stat-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="100">Gambar</th>
                            <th>Judul & Slug</th>
                            <th width="120">Penulis</th>
                            <th width="80" class="text-center">Views</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="120">Tanggal</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total > 0): ?>
                            <?php while ($a = $query->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($a['gambar']) && file_exists(ARTIKEL_PATH . $a['gambar'])): ?>
                                            <img src="<?= UPLOADS_URL ?>artikel/<?= htmlspecialchars($a['gambar']) ?>"
                                                 style="width: 70px; height: 50px; object-fit: cover; border-radius: 6px;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light"
                                                 style="width: 70px; height: 50px; border-radius: 6px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($a['judul']) ?></div>
                                        <small class="text-muted"><code><?= htmlspecialchars($a['slug']) ?></code></small>
                                    </td>
                                    <td><small><?= htmlspecialchars($a['penulis']) ?></small></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-eye"></i> <?= number_format($a['views']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($a['status'] == 'publish'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Publish</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-pencil"></i> Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= tanggal_indo($a['created_at']) ?></small></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>artikel/<?= $a['slug'] ?>" target="_blank"
                                               class="btn btn-outline-info" title="Preview">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="artikel_form.php?id=<?= $a['id'] ?>"
                                               class="btn btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="artikel_hapus.php?id=<?= $a['id'] ?>"
                                               class="btn btn-danger" title="Hapus"
                                               onclick="return confirm('Yakin hapus artikel \'<?= htmlspecialchars($a['judul']) ?>\'?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-journal-text fs-1 d-block mb-3 text-muted"></i>
                                    <h6 class="text-muted">Belum ada artikel</h6>
                                    <a href="artikel_form.php" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-plus-circle"></i> Tulis Artikel Pertama
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
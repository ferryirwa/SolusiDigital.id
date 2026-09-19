<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Filter & pencarian
$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$status_filter = isset($_GET['status']) ? clean($koneksi, $_GET['status']) : '';

// Query dengan kondisi
$where = [];
if ($cari !== '') {
    $where[] = "(nama LIKE '%$cari%' OR deskripsi_singkat LIKE '%$cari%')";
}
if (in_array($status_filter, ['aktif', 'nonaktif'])) {
    $where[] = "status = '$status_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = $koneksi->query("SELECT * FROM layanan $where_sql ORDER BY urutan ASC, id DESC");
$total = $query->num_rows;
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-grid text-primary"></i> Kelola Layanan
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
            <h5 class="mb-0">Daftar Layanan</h5>
            <small class="text-muted">Total: <strong><?= $total ?></strong> layanan</small>
        </div>
        <a href="layanan_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Layanan
        </a>
    </div>

    <!-- Filter -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control" 
                               placeholder="Cari nama atau deskripsi..." 
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?= $status_filter == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $status_filter == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="layanan.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
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
                            <th width="60" class="text-center">Urutan</th>
                            <th width="60" class="text-center">Icon</th>
                            <th>Nama Layanan</th>
                            <th>Harga Mulai</th>
                            <th class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total > 0): ?>
                            <?php while ($l = $query->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?= $l['urutan'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi <?= htmlspecialchars($l['icon']) ?> fs-4 text-primary"></i>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($l['nama']) ?></div>
                                        <small class="text-muted d-block">
                                            <code><?= htmlspecialchars($l['slug']) ?></code>
                                        </small>
                                        <small class="text-muted">
                                            <?= potong_teks($l['deskripsi_singkat'], 80) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-primary"><?= rupiah($l['harga_mulai']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($l['status'] == 'aktif'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Aktif
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Nonaktif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="layanan_form.php?id=<?= $l['id'] ?>" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="layanan_hapus.php?id=<?= $l['id'] ?>" 
                                               class="btn btn-danger" title="Hapus"
                                               onclick="return confirm('Yakin hapus layanan \'<?= htmlspecialchars($l['nama']) ?>\'?\n\nData yang dihapus tidak bisa dikembalikan!')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
                                    <h6 class="text-muted">Belum ada layanan</h6>
                                    <p class="text-muted small mb-3">
                                        <?= $cari || $status_filter ? 'Tidak ada hasil yang cocok dengan filter.' : 'Mulai tambahkan layanan pertama Anda.' ?>
                                    </p>
                                    <a href="layanan_form.php" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle"></i> Tambah Sekarang
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
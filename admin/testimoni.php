<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Filter
$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$status_filter = isset($_GET['status']) ? clean($koneksi, $_GET['status']) : '';
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

$where = [];
if ($cari !== '') {
    $where[] = "(nama LIKE '%$cari%' OR perusahaan LIKE '%$cari%' OR isi LIKE '%$cari%')";
}
if (in_array($status_filter, ['tampil', 'sembunyi'])) {
    $where[] = "status = '$status_filter'";
}
if ($rating_filter >= 1 && $rating_filter <= 5) {
    $where[] = "rating = $rating_filter";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = $koneksi->query("SELECT * FROM testimoni $where_sql ORDER BY created_at DESC");
$total = $query->num_rows;
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-chat-quote text-primary"></i> Kelola Testimoni
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
            <h5 class="mb-0">Daftar Testimoni</h5>
            <small class="text-muted">Total: <strong><?= $total ?></strong> testimoni</small>
        </div>
        <a href="testimoni_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Testimoni
        </a>
    </div>

    <!-- Filter -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control"
                               placeholder="Cari nama, perusahaan, atau isi..."
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="tampil" <?= $status_filter == 'tampil' ? 'selected' : '' ?>>Tampil</option>
                        <option value="sembunyi" <?= $status_filter == 'sembunyi' ? 'selected' : '' ?>>Sembunyi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="rating" class="form-select">
                        <option value="">Semua Rating</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= $rating_filter == $i ? 'selected' : '' ?>>
                                <?= str_repeat('★', $i) ?> (<?= $i ?>)
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="testimoni.php" class="btn btn-outline-secondary">
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
                            <th width="70">Foto</th>
                            <th>Nama & Jabatan</th>
                            <th>Isi Testimoni</th>
                            <th width="120" class="text-center">Rating</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total > 0): ?>
                            <?php while ($t = $query->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($t['foto']) && file_exists(PORTFOLIO_PATH . 'testimoni/' . $t['foto'])): ?>
                                            <img src="<?= UPLOADS_URL ?>portfolio/testimoni/<?= htmlspecialchars($t['foto']) ?>"
                                                 alt="foto"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-primary text-white"
                                                 style="width: 50px; height: 50px; border-radius: 50%; font-weight: bold; font-size: 20px;">
                                                <?= strtoupper(substr($t['nama'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($t['nama']) ?></div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($t['jabatan'] ?? '-') ?>
                                            <?= !empty($t['perusahaan']) ? ' · ' . htmlspecialchars($t['perusahaan']) : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= potong_teks($t['isi'], 100) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $t['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($t['status'] == 'tampil'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-eye"></i> Tampil
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-eye-slash"></i> Sembunyi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="testimoni_form.php?id=<?= $t['id'] ?>"
                                               class="btn btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="testimoni_hapus.php?id=<?= $t['id'] ?>"
                                               class="btn btn-danger" title="Hapus"
                                               onclick="return confirm('Yakin hapus testimoni dari \'<?= htmlspecialchars($t['nama']) ?>\'?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-chat-quote fs-1 d-block mb-3 text-muted"></i>
                                    <h6 class="text-muted">Belum ada testimoni</h6>
                                    <a href="testimoni_form.php" class="btn btn-primary btn-sm mt-2">
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
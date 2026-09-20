<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Filter
$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$status_filter = isset($_GET['status']) ? clean($koneksi, $_GET['status']) : '';

$where = [];
if ($cari !== '') {
    $where[] = "(nama LIKE '%$cari%' OR email LIKE '%$cari%' OR subjek LIKE '%$cari%' OR pesan LIKE '%$cari%')";
}
if (in_array($status_filter, ['belum', 'dibaca'])) {
    $where[] = "status = '$status_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = $koneksi->query("SELECT * FROM pesan_kontak $where_sql ORDER BY created_at DESC");
$total = $query->num_rows;

$total_belum = $koneksi->query("SELECT COUNT(*) as jml FROM pesan_kontak WHERE status='belum'")->fetch_assoc()['jml'];
$total_dibaca = $koneksi->query("SELECT COUNT(*) as jml FROM pesan_kontak WHERE status='dibaca'")->fetch_assoc()['jml'];
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-envelope text-primary"></i> Pesan Kontak
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <!-- Statistik -->
    <div class="row g-3 mb-3">
        <div class="col-md-4 col-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Total Pesan</p>
                        <h3><?= $total_belum + $total_dibaca ?></h3>
                    </div>
                    <i class="bi bi-envelope fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Belum Dibaca</p>
                        <h3 class="text-danger"><?= $total_belum ?></h3>
                    </div>
                    <i class="bi bi-envelope-exclamation fs-1 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Sudah Dibaca</p>
                        <h3 class="text-success"><?= $total_dibaca ?></h3>
                    </div>
                    <i class="bi bi-envelope-open fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control"
                               placeholder="Cari nama, email, subjek, isi..."
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="belum"  <?= $status_filter == 'belum' ? 'selected' : '' ?>>Belum Dibaca</option>
                        <option value="dibaca" <?= $status_filter == 'dibaca' ? 'selected' : '' ?>>Sudah Dibaca</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i></button>
                    <a href="pesan_kontak.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- List Pesan -->
    <div class="card stat-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">Status</th>
                            <th>Pengirim</th>
                            <th>Subjek</th>
                            <th>Isi Pesan</th>
                            <th width="120">Tanggal</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total > 0): ?>
                            <?php while ($m = $query->fetch_assoc()): ?>
                                <tr class="<?= $m['status'] === 'belum' ? 'table-warning' : '' ?>">
                                    <td class="text-center">
                                        <?php if ($m['status'] === 'belum'): ?>
                                            <i class="bi bi-envelope-fill text-warning fs-5"></i>
                                        <?php else: ?>
                                            <i class="bi bi-envelope-open text-muted fs-5"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($m['nama']) ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($m['email']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($m['subjek'] ?: '-') ?></small>
                                    </td>
                                    <td>
                                        <small><?= potong_teks($m['pesan'], 80) ?></small>
                                    </td>
                                    <td>
                                        <small><?= tanggal_indo($m['created_at']) ?></small><br>
                                        <small class="text-muted"><?= date('H:i', strtotime($m['created_at'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="pesan_kontak_detail.php?id=<?= $m['id'] ?>"
                                               class="btn btn-info text-white" title="Baca">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= htmlspecialchars($m['subjek']) ?>"
                                               class="btn btn-primary" title="Balas Email">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                            <a href="pesan_kontak_hapus.php?id=<?= $m['id'] ?>"
                                               class="btn btn-danger" title="Hapus"
                                               onclick="return confirm('Yakin hapus pesan dari <?= htmlspecialchars($m['nama']) ?>?')">
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
                                    <h6 class="text-muted">Belum ada pesan</h6>
                                    <p class="text-muted small mb-0">
                                        <?= $cari || $status_filter ? 'Tidak ada pesan yang cocok.' : 'Pesan dari form kontak akan muncul di sini.' ?>
                                    </p>
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
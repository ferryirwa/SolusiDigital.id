<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Filter
$cari          = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$status_filter = isset($_GET['status']) ? clean($koneksi, $_GET['status']) : '';
$tgl_dari      = isset($_GET['tgl_dari']) ? clean($koneksi, $_GET['tgl_dari']) : '';
$tgl_sampai    = isset($_GET['tgl_sampai']) ? clean($koneksi, $_GET['tgl_sampai']) : '';

$where = [];
if ($cari !== '') {
    $where[] = "(kode_pesanan LIKE '%$cari%' OR nama_klien LIKE '%$cari%' OR email LIKE '%$cari%' OR whatsapp LIKE '%$cari%')";
}
if (in_array($status_filter, ['baru', 'proses', 'selesai', 'batal'])) {
    $where[] = "status = '$status_filter'";
}
if ($tgl_dari !== '') {
    $where[] = "DATE(created_at) >= '$tgl_dari'";
}
if ($tgl_sampai !== '') {
    $where[] = "DATE(created_at) <= '$tgl_sampai'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Hitung total
$total_query = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan $where_sql");
$total_data  = $total_query->fetch_assoc()['jml'];
$total_page  = ceil($total_data / $per_page);

// Ambil data
$query = $koneksi->query("
    SELECT p.*, l.nama AS nama_layanan 
    FROM pesanan p 
    LEFT JOIN layanan l ON p.layanan_id = l.id 
    $where_sql 
    ORDER BY p.created_at DESC 
    LIMIT $per_page OFFSET $offset
");

// Statistik (global, tidak terpengaruh filter)
$stat_total   = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan")->fetch_assoc()['jml'];
$stat_baru    = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='baru'")->fetch_assoc()['jml'];
$stat_proses  = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='proses'")->fetch_assoc()['jml'];
$stat_selesai = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='selesai'")->fetch_assoc()['jml'];

// Build query string untuk pagination
$qs_params = $_GET;
unset($qs_params['page']);
$qs_base = http_build_query($qs_params);
$qs_sep = $qs_base ? '&' : '';
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-cart-check text-primary"></i> Kelola Pesanan
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
        <div class="col-md-3 col-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Total</p>
                        <h3><?= $stat_total ?></h3>
                    </div>
                    <i class="bi bi-cart fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Baru</p>
                        <h3 class="text-warning"><?= $stat_baru ?></h3>
                    </div>
                    <i class="bi bi-bell fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Proses</p>
                        <h3 class="text-info"><?= $stat_proses ?></h3>
                    </div>
                    <i class="bi bi-gear fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 text-muted small">Selesai</p>
                        <h3 class="text-success"><?= $stat_selesai ?></h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="cari" class="form-control"
                               placeholder="Cari kode, nama, email, WA..."
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="baru"    <?= $status_filter == 'baru' ? 'selected' : '' ?>>Baru</option>
                        <option value="proses"  <?= $status_filter == 'proses' ? 'selected' : '' ?>>Proses</option>
                        <option value="selesai" <?= $status_filter == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="batal"   <?= $status_filter == 'batal' ? 'selected' : '' ?>>Batal</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="tgl_dari" class="form-control"
                           placeholder="Dari tanggal"
                           value="<?= htmlspecialchars($tgl_dari) ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="tgl_sampai" class="form-control"
                           placeholder="Sampai tanggal"
                           value="<?= htmlspecialchars($tgl_sampai) ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="pesanan.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- List Pesanan -->
    <div class="card stat-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="150">Kode Pesanan</th>
                            <th>Klien</th>
                            <th>Layanan</th>
                            <th width="120">Budget</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="130">Tanggal</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_data > 0): ?>
                            <?php while ($p = $query->fetch_assoc()): ?>
                                <?php
                                $warna = [
                                    'baru'    => 'warning',
                                    'proses'  => 'info',
                                    'selesai' => 'success',
                                    'batal'   => 'danger'
                                ][$p['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <code class="fw-bold text-primary"><?= htmlspecialchars($p['kode_pesanan']) ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($p['nama_klien']) ?></div>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($p['email']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($p['whatsapp']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($p['nama_layanan'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <strong><?= rupiah($p['budget']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $warna ?>">
                                            <?= ucfirst($p['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= tanggal_indo($p['created_at']) ?></small><br>
                                        <small class="text-muted"><?= date('H:i', strtotime($p['created_at'])) ?> WIB</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="pesanan_detail.php?id=<?= $p['id'] ?>"
                                               class="btn btn-info text-white" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= link_wa($p['whatsapp'], 'Halo ' . $p['nama_klien'] . ', terima kasih sudah order di ' . SITE_NAME . '. Pesanan Anda dengan kode ' . $p['kode_pesanan'] . ' sedang kami proses.') ?>"
                                               target="_blank" class="btn btn-success" title="Chat WA">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                            <a href="pesanan_hapus.php?id=<?= $p['id'] ?>"
                                               class="btn btn-danger" title="Hapus"
                                               onclick="return confirm('Yakin hapus pesanan <?= htmlspecialchars($p['kode_pesanan']) ?>?\n\nData tidak bisa dikembalikan!')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-cart-x fs-1 d-block mb-3 text-muted"></i>
                                    <h6 class="text-muted">Belum ada pesanan</h6>
                                    <p class="text-muted small mb-0">
                                        <?= $cari || $status_filter || $tgl_dari || $tgl_sampai ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan masuk.' ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_page > 1): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan <?= $offset + 1 ?>–<?= min($offset + $per_page, $total_data) ?> 
                        dari <strong><?= $total_data ?></strong> pesanan
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $qs_base . $qs_sep ?>page=<?= $page - 1 ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_page; $i++): ?>
                                <?php if ($i == 1 || $i == $total_page || abs($i - $page) <= 2): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= $qs_base . $qs_sep ?>page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php elseif (abs($i - $page) == 3): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $total_page ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $qs_base . $qs_sep ?>page=<?= $page + 1 ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Filter
$cari = isset($_GET['cari']) ? clean($koneksi, $_GET['cari']) : '';
$role_filter = isset($_GET['role']) ? clean($koneksi, $_GET['role']) : '';

$where = [];
if ($cari !== '') {
    $where[] = "(username LIKE '%$cari%' OR nama LIKE '%$cari%' OR email LIKE '%$cari%')";
}
if (in_array($role_filter, ['super', 'admin'])) {
    $where[] = "role = '$role_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = $koneksi->query("SELECT * FROM admin $where_sql ORDER BY id ASC");
$total = $query->num_rows;
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-people text-primary"></i> Kelola Admin
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
            <h5 class="mb-0">Daftar Admin</h5>
            <small class="text-muted">Total: <strong><?= $total ?></strong> admin</small>
        </div>
        <a href="admin_form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Admin
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
                               placeholder="Cari username, nama, atau email..."
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="super" <?= $role_filter == 'super' ? 'selected' : '' ?>>Super Admin</option>
                        <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i></button>
                    <a href="admin.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
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
                            <th width="60">Avatar</th>
                            <th>Nama & Username</th>
                            <th>Email</th>
                            <th width="120" class="text-center">Role</th>
                            <th width="130">Dibuat</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total > 0): ?>
                            <?php while ($a = $query->fetch_assoc()): ?>
                                <?php $is_self = $a['id'] == $_SESSION['admin_id']; ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center rounded-circle"
                                             style="width: 45px; height: 45px; background: <?= $a['role'] === 'super' ? '#0A2540' : '#0D6EFD' ?>; color: #fff; font-weight: bold; font-size: 18px;">
                                            <?= strtoupper(substr($a['nama'], 0, 1)) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($a['nama']) ?>
                                            <?php if ($is_self): ?>
                                                <span class="badge bg-info ms-1">Anda</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-person"></i> <?= htmlspecialchars($a['username']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($a['email'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($a['role'] === 'super'): ?>
                                            <span class="badge bg-dark">
                                                <i class="bi bi-shield-fill-check"></i> Super
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-person"></i> Admin
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= tanggal_indo($a['created_at']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($is_self): ?>
                                            <a href="profil.php" class="btn btn-sm btn-info text-white" title="Edit Profil">
                                                <i class="bi bi-person-gear"></i> Profil
                                            </a>
                                        <?php else: ?>
                                            <div class="btn-group btn-group-sm">
                                                <a href="admin_form.php?id=<?= $a['id'] ?>"
                                                   class="btn btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($a['role'] !== 'super'): ?>
                                                    <a href="admin_hapus.php?id=<?= $a['id'] ?>"
                                                       class="btn btn-danger" title="Hapus"
                                                       onclick="return confirm('Yakin hapus admin \'<?= htmlspecialchars($a['nama']) ?>\'?\n\nData tidak bisa dikembalikan!')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-danger" disabled title="Super admin tidak bisa dihapus">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-people fs-1 d-block mb-3 text-muted"></i>
                                    <h6 class="text-muted">Tidak ada admin ditemukan</h6>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="alert alert-info mt-3 small">
        <i class="bi bi-info-circle"></i> 
        <strong>Super Admin</strong> tidak bisa dihapus untuk mencegah terkunci dari sistem.
        Anda tidak bisa menghapus akun sendiri.
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
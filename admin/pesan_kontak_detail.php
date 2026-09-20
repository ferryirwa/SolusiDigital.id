<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('danger', 'ID tidak valid!');
    redirect(ADMIN_URL . 'pesan_kontak.php');
}

// Ambil pesan
$stmt = $koneksi->prepare("SELECT * FROM pesan_kontak WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Pesan tidak ditemukan!');
    redirect(ADMIN_URL . 'pesan_kontak.php');
}

$m = $result->fetch_assoc();

// Auto tandai sudah dibaca
if ($m['status'] === 'belum') {
    $koneksi->query("UPDATE pesan_kontak SET status='dibaca' WHERE id = $id");
}
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-envelope-open text-primary"></i> Detail Pesan
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="pesan_kontak.php">Pesan Kontak</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card stat-card">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($m['subjek'] ?: '(Tanpa Subjek)') ?></h4>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i>
                                <?= tanggal_indo($m['created_at']) ?> pukul 
                                <?= date('H:i', strtotime($m['created_at'])) ?> WIB
                            </small>
                        </div>
                        <span class="badge bg-<?= $m['status'] === 'dibaca' ? 'success' : 'warning' ?>">
                            <?= $m['status'] === 'dibaca' ? '✓ Dibaca' : '● Baru' ?>
                        </span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <strong>Dari:</strong> 
                        <?= htmlspecialchars($m['nama']) ?> 
                        &lt;<?= htmlspecialchars($m['email']) ?>&gt;
                    </div>

                    <div class="p-3 bg-light rounded" style="white-space: pre-wrap; line-height: 1.8;">
                        <?= htmlspecialchars($m['pesan']) ?>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-person text-primary"></i> Info Pengirim
                    </h5>

                    <div class="mb-3">
                        <small class="text-muted d-block">Nama</small>
                        <div class="fw-semibold"><?= htmlspecialchars($m['nama']) ?></div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <div class="fw-semibold">
                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>">
                                <?= htmlspecialchars($m['email']) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-lightning text-warning"></i> Aksi
                    </h5>

                    <div class="d-grid gap-2">
                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= htmlspecialchars($m['subjek'] ?: 'Pesan Anda') ?>"
                           class="btn btn-primary btn-lg">
                            <i class="bi bi-envelope-open-fill"></i> Balas Email
                        </a>
                        <a href="pesan_kontak.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <a href="pesan_kontak_hapus.php?id=<?= $m['id'] ?>"
                           class="btn btn-outline-danger btn-lg"
                           onclick="return confirm('Yakin hapus pesan ini?')">
                            <i class="bi bi-trash"></i> Hapus Pesan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
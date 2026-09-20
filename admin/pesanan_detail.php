<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'ID pesanan tidak valid!');
    redirect(ADMIN_URL . 'pesanan.php');
}

// Ambil pesanan
$stmt = $koneksi->prepare("
    SELECT p.*, l.nama AS nama_layanan, l.harga_mulai 
    FROM pesanan p 
    LEFT JOIN layanan l ON p.layanan_id = l.id 
    WHERE p.id = ? LIMIT 1
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash('danger', 'Pesanan tidak ditemukan!');
    redirect(ADMIN_URL . 'pesanan.php');
}

$p = $result->fetch_assoc();

$warna = [
    'baru'    => 'warning',
    'proses'  => 'info',
    'selesai' => 'success',
    'batal'   => 'danger'
][$p['status']] ?? 'secondary';
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-file-text text-primary"></i> Detail Pesanan
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="pesanan.php">Pesanan</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($p['kode_pesanan']) ?></li>
        </ol>
    </nav>

    <!-- Header Pesanan -->
    <div class="card stat-card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">
                        <code class="text-primary fw-bold"><?= htmlspecialchars($p['kode_pesanan']) ?></code>
                        <span class="badge bg-<?= $warna ?> ms-2 align-middle">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </h4>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> 
                        Dibuat: <?= tanggal_indo($p['created_at']) ?> 
                        pukul <?= date('H:i', strtotime($p['created_at'])) ?> WIB
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= link_wa($p['whatsapp'], 'Halo ' . $p['nama_klien'] . ', terkait pesanan ' . $p['kode_pesanan'] . '...') ?>"
                       target="_blank" class="btn btn-success btn-lg">
                        <i class="bi bi-whatsapp"></i> Chat Klien
                    </a>
                    <a href="mailto:<?= htmlspecialchars($p['email']) ?>?subject=Balasan Pesanan <?= htmlspecialchars($p['kode_pesanan']) ?>"
                       class="btn btn-primary btn-lg">
                        <i class="bi bi-envelope"></i> Email Klien
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Kolom Kiri -->
        <div class="col-lg-8">

            <!-- Data Klien -->
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-person-circle text-primary"></i> Data Klien
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nama</small>
                            <div class="fw-semibold"><?= htmlspecialchars($p['nama_klien']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Email</small>
                            <div class="fw-semibold">
                                <a href="mailto:<?= htmlspecialchars($p['email']) ?>">
                                    <?= htmlspecialchars($p['email']) ?>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">WhatsApp</small>
                            <div class="fw-semibold">
                                <a href="<?= link_wa($p['whatsapp']) ?>" target="_blank" class="text-success">
                                    <i class="bi bi-whatsapp"></i> <?= htmlspecialchars($p['whatsapp']) ?>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tanggal Order</small>
                            <div class="fw-semibold"><?= tanggal_indo($p['created_at']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Pesanan -->
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-briefcase text-primary"></i> Detail Pesanan
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Jenis Layanan</small>
                            <div class="fw-semibold">
                                <?= htmlspecialchars($p['nama_layanan'] ?? 'Tidak ditentukan') ?>
                            </div>
                            <?php if ($p['harga_mulai']): ?>
                                <small class="text-muted">
                                    Harga mulai: <?= rupiah($p['harga_mulai']) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Budget Klien</small>
                            <div class="fw-bold text-primary fs-5"><?= rupiah($p['budget']) ?></div>
                        </div>
                        <?php if (!empty($p['target_selesai'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Target Selesai</small>
                                <div class="fw-semibold">
                                    <i class="bi bi-calendar-check"></i> <?= tanggal_indo($p['target_selesai']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <small class="text-muted d-block mb-2">Deskripsi Kebutuhan</small>
                    <div class="p-3 bg-light rounded" style="white-space: pre-wrap; line-height: 1.7;">
                        <?= htmlspecialchars($p['deskripsi']) ?>
                    </div>

                    <?php if (!empty($p['file_lampiran']) && file_exists(UPLOAD_PATH . 'pesanan/' . $p['file_lampiran'])): ?>
                        <hr>
                        <small class="text-muted d-block mb-2">File Lampiran</small>
                        <a href="<?= UPLOADS_URL ?>pesanan/<?= htmlspecialchars($p['file_lampiran']) ?>"
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-paperclip"></i> Lihat Lampiran
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Catatan Admin -->
            <div class="card stat-card">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-pencil-square text-warning"></i> Catatan Internal Admin
                    </h5>
                    <form action="pesanan_update_status.php" method="POST">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="aksi" value="catatan">
                        <textarea name="catatan_admin" class="form-control mb-2" rows="4"
                                  placeholder="Catatan internal (tidak terlihat oleh klien)..."><?= htmlspecialchars($p['catatan_admin'] ?? '') ?></textarea>
                        <button class="btn btn-warning btn-sm">
                            <i class="bi bi-save"></i> Simpan Catatan
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan -->
        <div class="col-lg-4">

            <!-- Ubah Status -->
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-arrow-repeat text-info"></i> Ubah Status
                    </h5>

                    <form action="pesanan_update_status.php" method="POST">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="aksi" value="status">

                        <div class="d-grid gap-2">
                            <?php
                            $statuses = [
                                'baru'    => ['Baru',    'warning', 'bell'],
                                'proses'  => ['Proses',  'info',    'gear'],
                                'selesai' => ['Selesai', 'success', 'check-circle'],
                                'batal'   => ['Batal',   'danger',  'x-circle'],
                            ];
                            foreach ($statuses as $key => $info):
                                $aktif = $p['status'] === $key;
                            ?>
                                <button type="submit" name="status" value="<?= $key ?>"
                                        class="btn btn-<?= $aktif ? $info[1] : 'outline-' . $info[1] ?> text-start"
                                        <?= $aktif ? 'disabled' : '' ?>>
                                    <i class="bi bi-<?= $info[2] ?>"></i> 
                                    <?= $info[0] ?>
                                    <?php if ($aktif): ?>
                                        <span class="badge bg-light text-dark ms-2">Sedang Aktif</span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-clock-history text-primary"></i> Timeline
                    </h5>

                    <div class="timeline">
                        <div class="d-flex mb-3">
                            <div class="me-3 text-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                                     style="width: 30px; height: 30px;">
                                    <i class="bi bi-plus"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-semibold small">Pesanan Dibuat</div>
                                <small class="text-muted">
                                    <?= tanggal_indo($p['created_at']) ?>
                                    <?= date('H:i', strtotime($p['created_at'])) ?>
                                </small>
                            </div>
                        </div>

                        <?php if ($p['updated_at'] !== $p['created_at']): ?>
                            <div class="d-flex">
                                <div class="me-3 text-center">
                                    <div class="bg-info rounded-circle d-flex align-items-center justify-content-center text-white"
                                         style="width: 30px; height: 30px;">
                                        <i class="bi bi-pencil"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-semibold small">Terakhir Diupdate</div>
                                    <small class="text-muted">
                                        <?= tanggal_indo($p['updated_at']) ?>
                                        <?= date('H:i', strtotime($p['updated_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Aksi -->
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="pesanan.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                        <a href="pesanan_hapus.php?id=<?= $p['id'] ?>"
                           class="btn btn-outline-danger"
                           onclick="return confirm('Yakin hapus pesanan <?= htmlspecialchars($p['kode_pesanan']) ?>?\n\nData tidak bisa dikembalikan!')">
                            <i class="bi bi-trash"></i> Hapus Pesanan
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
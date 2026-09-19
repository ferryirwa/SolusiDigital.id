<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// ============ STATISTIK ============
$total_pesanan   = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan")->fetch_assoc()['jml'];
$pesanan_baru    = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='baru'")->fetch_assoc()['jml'];
$pesanan_proses  = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='proses'")->fetch_assoc()['jml'];
$pesanan_selesai = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='selesai'")->fetch_assoc()['jml'];
$total_layanan   = $koneksi->query("SELECT COUNT(*) as jml FROM layanan")->fetch_assoc()['jml'];
$total_portfolio = $koneksi->query("SELECT COUNT(*) as jml FROM portfolio")->fetch_assoc()['jml'];
$total_artikel   = $koneksi->query("SELECT COUNT(*) as jml FROM artikel")->fetch_assoc()['jml'];
$pesan_belum     = $koneksi->query("SELECT COUNT(*) as jml FROM pesan_kontak WHERE status='belum'")->fetch_assoc()['jml'];

// ============ PESANAN TERBARU ============
$pesanan_terbaru = $koneksi->query("
    SELECT p.*, l.nama AS nama_layanan 
    FROM pesanan p 
    LEFT JOIN layanan l ON p.layanan_id = l.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");

// ============ GRAFIK 7 HARI TERAKHIR ============
$grafik = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $q = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE DATE(created_at)='$tgl'");
    $grafik[] = [
        'tanggal' => date('d M', strtotime($tgl)),
        'jumlah'  => $q->fetch_assoc()['jml']
    ];
}
$max_grafik = max(array_column($grafik, 'jumlah')) ?: 1;
?>

<!-- Style khusus halaman dashboard -->
<style>
    .bar-grafik {
        background: linear-gradient(180deg, #0D6EFD, #0A2540);
        height: var(--tinggi, 20px);
        border-radius: 6px 6px 0 0;
        min-height: 20px;
        transition: all 0.3s;
    }
</style>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-speedometer2 text-primary"></i> Dashboard
        </h1>
    </div>
    <div class="user-info">
        <div class="text-end d-none d-md-block">
            <div class="fw-semibold small"><?= htmlspecialchars($_SESSION['admin_nama']) ?></div>
            <div class="text-muted" style="font-size: 11px;">
                <?= ucfirst($_SESSION['admin_role']) ?>
            </div>
        </div>
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<!-- Content -->
<div class="content-area">

    <?php tampil_flash(); ?>

    <!-- Sambutan -->
    <div class="alert alert-primary border-0 mb-4" style="background: linear-gradient(135deg, #0D6EFD, #0A2540); color: #fff;">
        <h5 class="mb-1"><i class="bi bi-hand-thumbs-up"></i> Halo, <?= htmlspecialchars($_SESSION['admin_nama']) ?>!</h5>
        <p class="mb-0 small">Selamat datang kembali di panel admin. Berikut ringkasan aktivitas website Anda hari ini.</p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Total Pesanan</p>
                        <h3><?= $total_pesanan ?></h3>
                    </div>
                    <div class="icon-box" style="background: rgba(13,110,253,0.1); color: #0D6EFD;">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Pesanan Baru</p>
                        <h3 class="text-warning"><?= $pesanan_baru ?></h3>
                    </div>
                    <div class="icon-box" style="background: rgba(255,193,7,0.1); color: #FFC107;">
                        <i class="bi bi-bell"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Sedang Proses</p>
                        <h3 class="text-info"><?= $pesanan_proses ?></h3>
                    </div>
                    <div class="icon-box" style="background: rgba(13,202,240,0.1); color: #0dcaf0;">
                        <i class="bi bi-gear"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Selesai</p>
                        <h3 class="text-success"><?= $pesanan_selesai ?></h3>
                    </div>
                    <div class="icon-box" style="background: rgba(25,135,84,0.1); color: #198754;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Konten + Pesan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Layanan</p>
                        <h3><?= $total_layanan ?></h3>
                    </div>
                    <i class="bi bi-grid fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Portfolio</p>
                        <h3><?= $total_portfolio ?></h3>
                    </div>
                    <i class="bi bi-images fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Artikel</p>
                        <h3><?= $total_artikel ?></h3>
                    </div>
                    <i class="bi bi-journal-text fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p>Pesan Belum Dibaca</p>
                        <h3 class="text-danger"><?= $pesan_belum ?></h3>
                    </div>
                    <i class="bi bi-envelope fs-1 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Grafik -->
        <div class="col-lg-7">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-graph-up-arrow text-primary"></i> Pesanan 7 Hari Terakhir
                    </h5>
                    <div class="d-flex align-items-end justify-content-between" style="height: 200px; gap: 10px;">
                        <?php foreach ($grafik as $g): ?>
                            <?php $tinggi = ($g['jumlah'] / $max_grafik) * 100; ?>
                            <div class="text-center flex-fill">
                                <div class="fw-bold small mb-1"><?= $g['jumlah'] ?></div>
                                <div class="bar-grafik" style="--tinggi: <?= max($tinggi, 5) ?>%;"></div>
                                <div class="small text-muted mt-2"><?= $g['tanggal'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aksi Cepat -->
        <div class="col-lg-5">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-lightning-charge text-warning"></i> Aksi Cepat
                    </h5>
                    <div class="d-grid gap-2">
                        <a href="<?= ADMIN_URL ?>layanan_form.php" class="btn btn-outline-primary text-start">
                            <i class="bi bi-plus-circle"></i> Tambah Layanan Baru
                        </a>
                        <a href="<?= ADMIN_URL ?>portfolio_form.php" class="btn btn-outline-success text-start">
                            <i class="bi bi-plus-circle"></i> Tambah Portfolio
                        </a>
                        <a href="<?= ADMIN_URL ?>artikel_form.php" class="btn btn-outline-info text-start">
                            <i class="bi bi-plus-circle"></i> Tulis Artikel
                        </a>
                        <a href="<?= ADMIN_URL ?>pesanan.php" class="btn btn-outline-warning text-start">
                            <i class="bi bi-cart-check"></i> Lihat Pesanan 
                            <?php if ($pesanan_baru > 0): ?>
                                <span class="badge bg-danger ms-2"><?= $pesanan_baru ?> baru</span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= ADMIN_URL ?>pengaturan.php" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-gear"></i> Pengaturan Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="card stat-card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history text-primary"></i> Pesanan Terbaru
                </h5>
                <a href="<?= ADMIN_URL ?>pesanan.php" class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Klien</th>
                            <th>Layanan</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pesanan_terbaru->num_rows > 0): ?>
                            <?php while ($p = $pesanan_terbaru->fetch_assoc()): ?>
                                <?php
                                $warna = [
                                    'baru'    => 'warning',
                                    'proses'  => 'info',
                                    'selesai' => 'success',
                                    'batal'   => 'danger'
                                ][$p['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($p['kode_pesanan']) ?></code></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($p['nama_klien']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($p['whatsapp']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($p['nama_layanan'] ?? '-') ?></td>
                                    <td><?= rupiah($p['budget']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $warna ?>"><?= ucfirst($p['status']) ?></span>
                                    </td>
                                    <td>
                                        <small><?= tanggal_indo($p['created_at']) ?></small>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada pesanan
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
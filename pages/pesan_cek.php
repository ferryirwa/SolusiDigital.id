<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

$page_title = 'Cek Status Pesanan — ' . SITE_NAME;
$page_desc  = 'Lacak status pesanan Anda dengan kode pesanan yang sudah diberikan.';

$kode = isset($_GET['kode']) ? trim($_GET['kode']) : '';
$pesanan = null;
$not_found = false;

if (!empty($kode)) {
    $stmt = $koneksi->prepare("
        SELECT p.*, l.nama AS nama_layanan 
        FROM pesanan p 
        LEFT JOIN layanan l ON p.layanan_id = l.id 
        WHERE p.kode_pesanan = ? 
        LIMIT 1
    ");
    $stmt->bind_param('s', $kode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pesanan = $result->fetch_assoc();
    } else {
        $not_found = true;
    }
}

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Cek Status Pesanan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Cek Pesanan</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Form Cek -->
                <div class="card-service mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-search text-primary"></i> Masukkan Kode Pesanan
                    </h5>
                    <form method="GET">
                        <div class="input-group input-group-lg">
                            <input type="text" name="kode" class="form-control" 
                                   placeholder="Contoh: ORD-20250101-001"
                                   value="<?= htmlspecialchars($kode) ?>"
                                   required style="font-family: monospace;">
                            <button class="btn btn-primary">
                                <i class="bi bi-search"></i> Cek
                            </button>
                        </div>
                    </form>
                    <small class="text-muted d-block mt-2">
                        Kode pesanan diberikan setelah Anda submit form pemesanan.
                    </small>
                </div>

                <!-- Hasil -->
                <?php if ($not_found): ?>
                    <div class="card-service card-service-sm text-center" style="border: 2px solid #dc3545;">
                        <i class="bi bi-x-circle text-danger" style="font-size: 60px;"></i>
                        <h4 class="mt-3">Pesanan Tidak Ditemukan</h4>
                        <p class="text-muted">
                            Kode <strong><?= htmlspecialchars($kode) ?></strong> tidak ditemukan. 
                            Periksa kembali kode Anda atau hubungi kami.
                        </p>
                        <a href="<?= link_wa($pengaturan['whatsapp'], 'Halo, saya lupa kode pesanan saya.') ?>" 
                           target="_blank" class="btn btn-success">
                            <i class="bi bi-whatsapp"></i> Hubungi Admin
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($pesanan): ?>
                    <?php
                    // Konfigurasi status
                    $status_info = [
                        'baru'    => ['Baru', 'warning', 'bell', 'Pesanan baru diterima, menunggu konfirmasi admin.'],
                        'proses'  => ['Diproses', 'info', 'gear', 'Pesanan sedang dikerjakan oleh tim kami.'],
                        'selesai' => ['Selesai', 'success', 'check-circle', 'Pesanan telah selesai dikerjakan.'],
                        'batal'   => ['Dibatalkan', 'danger', 'x-circle', 'Pesanan dibatalkan.'],
                    ];
                    $st = $status_info[$pesanan['status']] ?? ['Unknown', 'secondary', 'question', ''];
                    ?>

                    <!-- Header Status -->
                    <div class="card-service mb-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <small class="text-muted d-block">Kode Pesanan</small>
                                <h3 class="text-primary mb-0" style="font-family: monospace; letter-spacing: 2px;">
                                    <?= htmlspecialchars($pesanan['kode_pesanan']) ?>
                                </h3>
                            </div>
                            <span class="badge bg-<?= $st[1] ?> fs-6 px-3 py-2">
                                <i class="bi bi-<?= $st[2] ?>"></i> <?= $st[0] ?>
                            </span>
                        </div>
                        <hr>
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-info-circle"></i> <?= $st[3] ?>
                        </p>
                    </div>

                    <!-- Timeline Status -->
                    <div class="card-service mb-4">
                        <h5 class="mb-4">
                            <i class="bi bi-diagram-3 text-primary"></i> Progress Pesanan
                        </h5>

                        <?php
                        $steps = ['baru', 'proses', 'selesai'];
                        $current_index = array_search($pesanan['status'], $steps);
                        $is_batal = $pesanan['status'] === 'batal';
                        ?>

                        <?php if ($is_batal): ?>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-x-circle"></i> Pesanan ini telah dibatalkan. 
                                Hubungi admin untuk info lebih lanjut.
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between position-relative" style="padding: 20px 0;">
                                <!-- Progress line -->
                                <div class="position-absolute" style="top: 40px; left: 10%; right: 10%; height: 3px; background: #e9ecef; z-index: 1;"></div>
                                <div class="position-absolute" 
                                     style="top: 40px; left: 10%; height: 3px; background: #0D6EFD; z-index: 1;
                                            width: <?= max(0, ($current_index / (count($steps) - 1)) * 80) ?>%;"></div>

                                <?php foreach ($steps as $i => $step): ?>
                                    <?php
                                    $active = $i <= $current_index;
                                    $labels = ['Diterima', 'Diproses', 'Selesai'];
                                    $icons = ['check-lg', 'gear', 'check-circle'];
                                    ?>
                                    <div class="text-center position-relative" style="z-index: 2; flex: 1;">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                                             style="width: 50px; height: 50px; 
                                                    background: <?= $active ? '#0D6EFD' : '#e9ecef' ?>; 
                                                    color: <?= $active ? '#fff' : '#adb5bd' ?>;
                                                    font-size: 20px;">
                                            <i class="bi bi-<?= $icons[$i] ?>"></i>
                                        </div>
                                        <div class="fw-semibold small"><?= $labels[$i] ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Detail Pesanan -->
                    <div class="card-service mb-4">
                        <h5 class="mb-3">
                            <i class="bi bi-receipt text-primary"></i> Detail Pesanan
                        </h5>
                        <table class="table table-borderless mb-0 small">
                            <tr>
                                <td class="text-muted" width="40%">Nama</td>
                                <td class="fw-semibold"><?= htmlspecialchars($pesanan['nama_klien']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="fw-semibold"><?= htmlspecialchars($pesanan['email']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Layanan</td>
                                <td class="fw-semibold"><?= htmlspecialchars($pesanan['nama_layanan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Budget</td>
                                <td class="fw-bold text-primary"><?= rupiah($pesanan['budget']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Pesan</td>
                                <td class="fw-semibold"><?= tanggal_indo($pesanan['created_at']) ?></td>
                            </tr>
                            <?php if ($pesanan['updated_at'] !== $pesanan['created_at']): ?>
                                <tr>
                                    <td class="text-muted">Terakhir Diupdate</td>
                                    <td class="fw-semibold"><?= tanggal_indo($pesanan['updated_at']) ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>

                        <hr>
                        <small class="text-muted d-block mb-2">Deskripsi Kebutuhan:</small>
                        <div class="p-3 bg-light rounded small" style="white-space: pre-wrap; line-height: 1.7;">
                            <?= htmlspecialchars($pesanan['deskripsi']) ?>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="d-grid gap-2">
                        <a href="<?= link_wa($pengaturan['whatsapp'], 'Halo, saya ingin tanya status pesanan ' . $pesanan['kode_pesanan']) ?>" 
                           target="_blank" class="btn btn-success btn-lg">
                            <i class="bi bi-whatsapp"></i> Tanya Status via WA
                        </a>
                        <a href="<?= BASE_URL ?>pesan" class="btn btn-outline-secondary">
                            <i class="bi bi-plus-circle"></i> Buat Pesanan Baru
                        </a>
                    </div>

                <?php endif; ?>

                <?php if (empty($kode) && !$not_found): ?>
                    <!-- Info Cara Cek -->
                    <div class="card-service">
                        <h5 class="mb-3">
                            <i class="bi bi-lightbulb text-warning"></i> Belum Punya Kode?
                        </h5>
                        <p class="small text-muted">
                            Kode pesanan diberikan otomatis setelah Anda submit form pemesanan.
                            Format kode: <code>ORD-YYYYMMDD-XXX</code>
                        </p>
                        <a href="<?= BASE_URL ?>pesan" class="btn btn-primary">
                            <i class="bi bi-cart-check"></i> Pesan Sekarang
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

// Cek apakah ada data pesanan di session
if (!isset($_SESSION['pesanan_sukses'])) {
    set_flash('warning', 'Silakan isi form pemesanan terlebih dahulu.');
    redirect(BASE_URL . 'pesan');
}

$pesanan = $_SESSION['pesanan_sukses'];

// Ambil nama layanan
$layanan_nama = '-';
if (!empty($pesanan['layanan_id'])) {
    $stmt = $koneksi->prepare("SELECT nama FROM layanan WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $pesanan['layanan_id']);
    $stmt->execute();
    $l = $stmt->get_result()->fetch_assoc();
    if ($l) $layanan_nama = $l['nama'];
}

$page_title = 'Pesanan Berhasil — ' . SITE_NAME;

// Pesan WhatsApp otomatis ke admin
$pesan_wa = "Halo Admin " . SITE_NAME . ",\n\n"
          . "Saya baru saja melakukan pemesanan:\n"
          . "📋 Kode: *" . $pesanan['kode'] . "*\n"
          . "👤 Nama: " . $pesanan['nama'] . "\n"
          . "📧 Email: " . $pesanan['email'] . "\n"
          . "📱 WA: +62" . $pesanan['whatsapp'] . "\n"
          . "🛠️ Layanan: " . $layanan_nama . "\n"
          . "💰 Budget: " . rupiah($pesanan['budget']) . "\n\n"
          . "Mohon konfirmasi pesanan saya. Terima kasih!";

$wa_link = link_wa($pengaturan['whatsapp'], $pesan_wa);

require_once '../includes/header.php';
?>

<section class="section" style="padding-top: 140px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Success Icon -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #198754, #0d4f2e); animation: pop 0.5s ease;">
                        <i class="bi bi-check-lg text-white" style="font-size: 60px;"></i>
                    </div>
                    <h2 class="mb-2">Pesanan Berhasil Dikirim!</h2>
                    <p class="text-muted">
                        Terima kasih, <strong><?= htmlspecialchars($pesanan['nama']) ?></strong>. 
                        Pesanan Anda sudah kami terima.
                    </p>
                </div>

                <!-- Kode Pesanan -->
                <div class="card-service text-center mb-4" 
                     style="background: linear-gradient(135deg, #e7f1ff, #f0f7ff); border: 2px dashed #0D6EFD;">
                    <small class="text-muted d-block mb-2">Kode Pesanan Anda</small>
                    <h2 class="text-primary mb-0" style="font-family: monospace; letter-spacing: 3px;">
                        <?= htmlspecialchars($pesanan['kode']) ?>
                    </h2>
                    <button class="btn btn-sm btn-outline-primary mt-3" onclick="copyKode()">
                        <i class="bi bi-clipboard"></i> Copy Kode
                    </button>
                    <small class="d-block text-muted mt-2">
                        ⚠️ <strong>Simpan kode ini</strong> untuk melacak status pesanan Anda.
                    </small>
                </div>

                <!-- Detail Pesanan -->
                <div class="card-service mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-receipt text-primary"></i> Detail Pesanan
                    </h5>
                    <table class="table table-borderless mb-0 small">
                        <tr>
                            <td class="text-muted" width="40%">Nama</td>
                            <td class="fw-semibold"><?= htmlspecialchars($pesanan['nama']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td class="fw-semibold"><?= htmlspecialchars($pesanan['email']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">WhatsApp</td>
                            <td class="fw-semibold">+62<?= htmlspecialchars($pesanan['whatsapp']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Layanan</td>
                            <td class="fw-semibold"><?= htmlspecialchars($layanan_nama) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Budget</td>
                            <td class="fw-bold text-primary"><?= rupiah($pesanan['budget']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Waktu Pesan</td>
                            <td class="fw-semibold">
                                <?= tanggal_indo($pesanan['waktu']) ?> 
                                <?= date('H:i', strtotime($pesanan['waktu'])) ?> WIB
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge bg-warning">Menunggu Konfirmasi</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Langkah Selanjutnya -->
                <div class="card-service mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-list-check text-success"></i> Langkah Selanjutnya
                    </h5>
                    <ol class="small ps-3 mb-0" style="line-height: 2;">
                        <li>
                            <strong>Konfirmasi via WhatsApp</strong> — Klik tombol di bawah untuk konfirmasi 
                            otomatis ke tim kami.
                        </li>
                        <li>
                            <strong>Tunggu balasan</strong> — Kami akan merespons dalam 1×24 jam kerja.
                        </li>
                        <li>
                            <strong>Diskusi detail</strong> — Kami akan bahas teknis, timeline, dan pembayaran.
                        </li>
                        <li>
                            <strong>Proyek dimulai</strong> — Setelah deal, proyek langsung dikerjakan.
                        </li>
                    </ol>
                </div>

                <!-- CTA -->
                <div class="card-service mb-4" 
                     style="background: linear-gradient(135deg, #0D6EFD, #0A2540); color: #fff;">
                    <h5 class="mb-2" style="color:#fff;">
                        <i class="bi bi-whatsapp"></i> Konfirmasi Sekarang
                    </h5>
                    <p class="small mb-3" style="color: rgba(255,255,255,0.85);">
                        Klik tombol di bawah untuk konfirmasi pesanan Anda via WhatsApp. 
                        Pesan akan otomatis terisi dengan detail pesanan Anda.
                    </p>
                    <a href="<?= $wa_link ?>" target="_blank" 
                       class="btn btn-warning btn-lg w-100">
                        <i class="bi bi-whatsapp"></i> Konfirmasi via WhatsApp
                    </a>
                </div>

                <!-- Aksi Lain -->
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>pesan/cek?kode=<?= urlencode($pesanan['kode']) ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> Lacak Status Pesanan
                    </a>
                    <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Kembali ke Home
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
@keyframes pop {
    0% { transform: scale(0); opacity: 0; }
    70% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
function copyKode() {
    const kode = '<?= $pesanan['kode'] ?>';
    navigator.clipboard.writeText(kode).then(function() {
        alert('Kode berhasil dicopy: ' + kode);
    });
}
</script>

<?php
// Hapus session setelah ditampilkan supaya tidak bisa diakses lagi
// Komentar baris ini kalau mau user bisa refresh halaman
unset($_SESSION['pesanan_sukses']);

require_once '../includes/footer.php';
?>
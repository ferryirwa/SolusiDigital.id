<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

$page_title = 'Kontak — ' . SITE_NAME;
$page_desc  = 'Hubungi kami untuk konsultasi gratis seputar pembuatan website, aplikasi, dan layanan digital lainnya.';

// Handle submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $subjek = trim($_POST['subjek'] ?? '');
    $pesan  = trim($_POST['pesan'] ?? '');

    $errors = [];
    if (empty($nama)) $errors[] = 'Nama wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (empty($pesan)) $errors[] = 'Pesan wajib diisi.';
    if (strlen($pesan) > 5000) $errors[] = 'Pesan terlalu panjang.';

    if (empty($errors)) {
        $stmt = $koneksi->prepare("INSERT INTO pesan_kontak (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nama, $email, $subjek, $pesan);
        if ($stmt->execute()) {
            set_flash('success', 'Terima kasih! Pesan Anda sudah kami terima. Kami akan segera menghubungi Anda.');
            redirect(BASE_URL . 'kontak');
        } else {
            set_flash('danger', 'Maaf, terjadi kesalahan. Silakan coba lagi.');
        }
    } else {
        set_flash('danger', implode('<br>', $errors));
    }
}

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Hubungi Kami</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Kontak</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php tampil_flash(); ?>

        <div class="row g-4">
            <!-- Info Kontak -->
            <div class="col-lg-4">
                <div class="card-service card-service-info">
                    <div class="icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>Alamat</h5>
                    <p class="small mb-0"><?= htmlspecialchars($pengaturan['alamat']) ?></p>
                </div>

                <div class="card-service card-service-info">
                    <div class="icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <h5>Email</h5>
                    <a href="mailto:<?= htmlspecialchars($pengaturan['email']) ?>" class="small">
                        <?= htmlspecialchars($pengaturan['email']) ?>
                    </a>
                </div>

                <div class="card-service card-service-info">
                    <div class="icon">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h5>WhatsApp</h5>
                    <a href="<?= link_wa($pengaturan['whatsapp']) ?>" target="_blank" class="small text-success">
                        <?= htmlspecialchars($pengaturan['whatsapp']) ?>
                    </a>
                </div>

                <div class="card-service card-service-info">
                    <div class="icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h5>Jam Operasional</h5>
                    <p class="small mb-0">
                        Senin - Jumat: 09.00 - 18.00 WIB<br>
                        Sabtu: 09.00 - 14.00 WIB<br>
                        Minggu: Tutup (support WA tetap aktif)
                    </p>
                </div>
            </div>

            <!-- Form -->
            <div class="col-lg-8">
                <div class="card-service card-service-form">
                    <h4>Kirim Pesan</h4>
                    <p class="text-muted small mb-4">
                        Isi form di bawah, kami akan merespons dalam 1×24 jam.
                    </p>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" 
                                       placeholder="Nama lengkap" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="email@contoh.com" required maxlength="100">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subjek</label>
                                <input type="text" name="subjek" class="form-control" 
                                       placeholder="Contoh: Tanya harga website toko online" 
                                       maxlength="150">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Pesan <span class="text-danger">*</span></label>
                                <textarea name="pesan" class="form-control" rows="6" 
                                          placeholder="Tulis pesan Anda di sini..." 
                                          required maxlength="5000"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send"></i> Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Maps -->
        <div class="mt-5">
            <div class="card-service card-service-map">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.5!2d106.8!3d-6.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTInMDAuMCJTIDEwNsKwNDgnMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890" 
                    width="100%" height="400" 
                    allowfullscreen="" loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
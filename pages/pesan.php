<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

$page_title = 'Pesan Layanan — ' . SITE_NAME;
$page_desc  = 'Form pemesanan jasa pembuatan website, aplikasi Android, dan layanan IT lainnya.';

// Ambil semua layanan aktif
$layanan_list = $koneksi->query("SELECT id, nama, harga_mulai, icon FROM layanan WHERE status='aktif' ORDER BY urutan ASC");

// Pre-select layanan dari query string (jika dari halaman detail)
$pre_layanan = isset($_GET['layanan']) ? (int)$_GET['layanan'] : 0;

// Ambil data lama kalau ada (dari session kalau validasi gagal)
$form_data = $_SESSION['form_pesan'] ?? [];
unset($_SESSION['form_pesan']);

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Pesan Layanan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Pesan</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php tampil_flash(); ?>

        <div class="row g-4">
            <!-- Info Kiri -->
            <div class="col-lg-4">
                <div class="card-service sticky-top" style="top: 100px;">
                    <h5><i class="bi bi-info-circle text-primary"></i> Cara Pesan</h5>
                    <ol class="small ps-3 mb-3">
                        <li>Isi form pemesanan dengan lengkap</li>
                        <li>Klik "Kirim Pesanan"</li>
                        <li>Anda akan mendapat <strong>kode pesanan</strong></li>
                        <li>Kami akan menghubungi via WhatsApp/email dalam 1×24 jam</li>
                        <li>Diskusi & konfirmasi detail proyek</li>
                    </ol>

                    <hr>

                    <h6><i class="bi bi-shield-check text-success"></i> Jaminan Kami</h6>
                    <ul class="small ps-3 mb-0">
                        <li>Konsultasi 100% gratis</li>
                        <li>Harga transparan tanpa biaya tersembunyi</li>
                        <li>Garansi revisi hingga puas</li>
                        <li>Data Anda aman & rahasia</li>
                    </ul>
                </div>
            </div>

            <!-- Form -->
            <div class="col-lg-8">
                <div class="card-service card-service-form">
                    <h4>Form Pemesanan</h4>
                    <p class="text-muted small mb-4">
                        Isi data di bawah dengan lengkap. Tanda <span class="text-danger">*</span> wajib diisi.
                    </p>

                    <form action="<?= BASE_URL ?>pages/pesan_proses.php" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="formPesan">

                        <!-- ============ DATA KLIEN ============ -->
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-person-circle"></i> Data Anda
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_klien" class="form-control"
                                       placeholder="Contoh: Budi Santoso"
                                       value="<?= htmlspecialchars($form_data['nama_klien'] ?? '') ?>"
                                       required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control"
                                       placeholder="email@contoh.com"
                                       value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                       required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nomor WhatsApp <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+62</span>
                                    <input type="tel" name="whatsapp" class="form-control"
                                           placeholder="81234567890"
                                           value="<?= htmlspecialchars($form_data['whatsapp'] ?? '') ?>"
                                           required pattern="[0-9]{8,15}"
                                           title="Masukkan nomor tanpa 0 atau +62. Contoh: 81234567890">
                                </div>
                                <small class="text-muted">
                                    Contoh: <code>81234567890</code> (tanpa 0 atau +62)
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Jenis Layanan <span class="text-danger">*</span>
                                </label>
                                <select name="layanan_id" class="form-select" required id="selectLayanan">
                                    <option value="">-- Pilih Layanan --</option>
                                    <?php while ($l = $layanan_list->fetch_assoc()): ?>
                                        <option value="<?= $l['id'] ?>" 
                                                data-harga="<?= $l['harga_mulai'] ?>"
                                                <?= ($pre_layanan == $l['id'] || ($form_data['layanan_id'] ?? 0) == $l['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($l['nama']) ?> 
                                            (Mulai <?= rupiah($l['harga_mulai']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- ============ DETAIL PROYEK ============ -->
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-briefcase"></i> Detail Proyek
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Budget Anda <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="budget" class="form-control"
                                           placeholder="3000000"
                                           value="<?= htmlspecialchars($form_data['budget'] ?? '') ?>"
                                           required min="0" step="500000">
                                </div>
                                <small class="text-muted">
                                    Perkiraan budget yang Anda siapkan
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Selesai</label>
                                <input type="date" name="target_selesai" class="form-control"
                                       value="<?= htmlspecialchars($form_data['target_selesai'] ?? '') ?>"
                                       min="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                                <small class="text-muted">Opsional. Minimal 3 hari dari sekarang.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    Deskripsi Kebutuhan <span class="text-danger">*</span>
                                </label>
                                <textarea name="deskripsi" class="form-control" rows="6"
                                          placeholder="Ceritakan kebutuhan Anda secara detail, misalnya:
- Fitur apa saja yang diinginkan
- Referensi website/aplikasi (kalau ada)
- Target pengguna
- Hal khusus yang perlu diketahui"
                                          required maxlength="3000"><?= htmlspecialchars($form_data['deskripsi'] ?? '') ?></textarea>
                                <small class="text-muted">
                                    <span id="charCount">0</span>/3000 karakter
                                </small>
                            </div>
                        </div>

                        <!-- ============ LAMPIRAN ============ -->
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-paperclip"></i> Lampiran (Opsional)
                        </h6>

                        <div class="mb-4">
                            <label class="form-label">Upload File Referensi</label>
                            <input type="file" name="lampiran" class="form-control"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.rar"
                                   id="inputLampiran">
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i>
                                Format: PDF, DOC, DOCX, JPG, PNG, ZIP, RAR. Maks 5MB.<br>
                                Contoh: proposal, sketsa desain, referensi, dokumen kebutuhan.
                            </small>
                            <div id="fileInfo" class="mt-2 small text-primary fw-semibold" style="display:none;"></div>
                        </div>

                        <!-- ============ PERSETUJUAN ============ -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" 
                                   name="setuju" id="setuju" value="1" required>
                            <label class="form-check-label small" for="setuju">
                                Saya setuju data yang saya isi digunakan untuk keperluan pemrosesan pesanan 
                                dan dihubungi oleh tim <?= htmlspecialchars($pengaturan['nama_situs']) ?>.
                            </label>
                        </div>

                        <!-- ============ SUBMIT ============ -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                                <i class="bi bi-send"></i> Kirim Pesanan
                            </button>
                            <a href="<?= BASE_URL ?>kontak" class="btn btn-outline-secondary">
                                <i class="bi bi-chat-dots"></i> Konsultasi Dulu
                            </a>
                        </div>

                        <!-- Catatan Kode Pesanan -->
                        <div class="alert alert-info mt-4 mb-0 small">
                            <i class="bi bi-info-circle"></i>
                            Setelah submit, Anda akan mendapat <strong>kode pesanan</strong>. 
                            Simpan kode tersebut untuk melacak status pesanan Anda.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Character counter
const deskripsi = document.querySelector('textarea[name="deskripsi"]');
const charCount = document.getElementById('charCount');
deskripsi.addEventListener('input', function() {
    charCount.textContent = this.value.length;
});
charCount.textContent = deskripsi.value.length;

// File info
document.getElementById('inputLampiran').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const info = document.getElementById('fileInfo');
    if (file) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        info.style.display = 'block';
        info.innerHTML = '<i class="bi bi-check-circle text-success"></i> ' + 
                         file.name + ' (' + sizeMB + ' MB)';
    } else {
        info.style.display = 'none';
    }
});

// Loading state saat submit
document.getElementById('formPesan').addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
});
</script>

<?php require_once '../includes/footer.php'; ?>
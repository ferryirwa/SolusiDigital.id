<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Ambil data pengaturan
$q = $koneksi->query("SELECT * FROM pengaturan LIMIT 1");
$pengaturan = $q && $q->num_rows > 0 ? $q->fetch_assoc() : [
    'id' => 0,
    'nama_situs' => '',
    'logo' => '',
    'deskripsi' => '',
    'alamat' => '',
    'email' => '',
    'whatsapp' => '',
    'facebook' => '',
    'instagram' => '',
    'meta_keyword' => '',
    'meta_description' => '',
];

// Refill dari session kalau ada error
if (isset($_SESSION['form_pengaturan'])) {
    $pengaturan = array_merge($pengaturan, $_SESSION['form_pengaturan']);
    unset($_SESSION['form_pengaturan']);
}

$logo_existing = !empty($pengaturan['logo']) && file_exists(UPLOAD_PATH . 'logo/' . $pengaturan['logo']);
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-gear text-primary"></i> Pengaturan Website
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <form action="pengaturan_simpan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$pengaturan['id'] ?>">
        <input type="hidden" name="logo_lama" value="<?= htmlspecialchars($pengaturan['logo']) ?>">

        <div class="row g-3">

            <!-- Kolom Kiri -->
            <div class="col-lg-8">

                <!-- Identitas Situs -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-info-circle text-primary"></i> Identitas Situs
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Situs <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_situs" class="form-control form-control-lg"
                                   placeholder="Contoh: JasaProgrammer.id"
                                   value="<?= htmlspecialchars($pengaturan['nama_situs']) ?>"
                                   required maxlength="100">
                            <small class="text-muted">Nama ini muncul di navbar, footer, dan title halaman.</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                Deskripsi Singkat <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" class="form-control" rows="3"
                                      placeholder="Jasa pembuatan website, aplikasi Android, hosting..."
                                      required maxlength="500"><?= htmlspecialchars($pengaturan['deskripsi']) ?></textarea>
                            <small class="text-muted">Muncul di footer dan meta description.</small>
                        </div>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-telephone text-primary"></i> Kontak & Alamat
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control"
                                           placeholder="info@website.com"
                                           value="<?= htmlspecialchars($pengaturan['email']) ?>"
                                           required maxlength="100">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    WhatsApp <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+62</span>
                                    <input type="tel" name="whatsapp" class="form-control"
                                           placeholder="81234567890"
                                           value="<?= htmlspecialchars(preg_replace('/^62/', '', $pengaturan['whatsapp'])) ?>"
                                           required pattern="[0-9]{8,15}">
                                </div>
                                <small class="text-muted">Tanpa 0 atau 62 di depan.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat Kantor</label>
                                <textarea name="alamat" class="form-control" rows="2"
                                          placeholder="Jl. Teknologi No. 123, Jakarta Selatan"
                                          maxlength="300"><?= htmlspecialchars($pengaturan['alamat']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sosial Media -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-share text-primary"></i> Sosial Media
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Facebook</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                                    <input type="url" name="facebook" class="form-control"
                                           placeholder="https://facebook.com/username"
                                           value="<?= htmlspecialchars($pengaturan['facebook']) ?>"
                                           maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                                    <input type="url" name="instagram" class="form-control"
                                           placeholder="https://instagram.com/username"
                                           value="<?= htmlspecialchars($pengaturan['instagram']) ?>"
                                           maxlength="255">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Kosongkan kalau tidak ada. Harus URL lengkap dengan <code>https://</code>
                        </small>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-search text-primary"></i> SEO (Search Engine Optimization)
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Meta Keywords</label>
                            <textarea name="meta_keyword" class="form-control" rows="2"
                                      placeholder="jasa website, jasa android, jasa hosting, jasa programmer"
                                      maxlength="500"><?= htmlspecialchars($pengaturan['meta_keyword']) ?></textarea>
                            <small class="text-muted">
                                Pisahkan dengan koma. Digunakan mesin pencari untuk kategorisasi.
                            </small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3"
                                      placeholder="Kami menyediakan jasa pembuatan website, aplikasi Android, dan hosting profesional dengan harga terjangkau."
                                      maxlength="300"><?= htmlspecialchars($pengaturan['meta_description']) ?></textarea>
                            <small class="text-muted">Maks 160 karakter untuk hasil optimal di Google.</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">

                <!-- Logo -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-image text-info"></i> Logo Website
                        </h5>

                        <div class="text-center mb-3 p-3 rounded"
                             style="background: #f8f9fa; border: 2px dashed #dee2e6; min-height: 150px;">
                            <?php if ($logo_existing): ?>
                                <img src="<?= UPLOADS_URL ?>logo/<?= htmlspecialchars($pengaturan['logo']) ?>"
                                     id="logoPreview"
                                     style="max-width: 100%; max-height: 120px; object-fit: contain;">
                            <?php else: ?>
                                <div id="logoPlaceholder" class="text-muted py-4">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 40px; opacity: 0.4;"></i>
                                    <p class="small mb-0 mt-2">Belum ada logo</p>
                                </div>
                                <img id="logoPreview" style="display:none; max-width: 100%; max-height: 120px; object-fit: contain;">
                            <?php endif; ?>
                        </div>

                        <input type="file" name="logo" class="form-control"
                               accept="image/png,image/jpeg,image/svg+xml,image/webp"
                               onchange="previewLogo(event)">
                        <small class="text-muted d-block mt-2">
                            Format: PNG, JPG, SVG, WEBP. Maks 1MB.<br>
                            Disarankan PNG transparan, ukuran 200x50px.
                        </small>

                        <?php if ($logo_existing): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                       name="hapus_logo" id="hapus_logo" value="1">
                                <label class="form-check-label small text-danger" for="hapus_logo">
                                    <i class="bi bi-trash"></i> Hapus logo
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preview Info -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-eye text-warning"></i> Preview
                        </h5>
                        <div class="p-3 rounded" style="background: #0A2540; color: #fff;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <?php if ($logo_existing): ?>
                                    <img src="<?= UPLOADS_URL ?>logo/<?= htmlspecialchars($pengaturan['logo']) ?>"
                                         style="height: 24px;">
                                <?php else: ?>
                                    <i class="bi bi-code-slash text-warning"></i>
                                <?php endif; ?>
                                <strong class="small"><?= htmlspecialchars($pengaturan['nama_situs']) ?></strong>
                            </div>
                            <small style="color: rgba(255,255,255,0.7); font-size: 11px;">
                                <?= potong_teks($pengaturan['deskripsi'], 80) ?>
                            </small>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Preview tampilan di navbar/footer.
                        </small>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Simpan Pengaturan
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>

<script>
function previewLogo(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 1MB.');
        event.target.value = '';
        return;
    }

    const allowed = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'];
    if (!allowed.includes(file.type)) {
        alert('Format harus PNG, JPG, SVG, atau WEBP.');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');
        img.src = e.target.result;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';

        const cb = document.getElementById('hapus_logo');
        if (cb) cb.checked = false;
    };
    reader.readAsDataURL(file);
}
</script>

<?php require_once 'includes/footer.php'; ?>
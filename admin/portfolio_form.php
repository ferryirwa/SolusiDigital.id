<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Mode
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

// Default data
$data = [
    'judul' => '',
    'slug' => '',
    'kategori' => 'web',
    'klien' => '',
    'deskripsi' => '',
    'gambar' => '',
    'link_demo' => '',
    'teknologi' => '',
    'tanggal_selesai' => date('Y-m-d'),
];

// Kalau edit, ambil dari DB
if ($mode === 'edit') {
    $stmt = $koneksi->prepare("SELECT * FROM portfolio WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        set_flash('danger', 'Portfolio tidak ditemukan!');
        redirect(ADMIN_URL . 'portfolio.php');
    }
    $data = $result->fetch_assoc();
}

// Refill dari session kalau ada error
if (isset($_SESSION['form_portfolio'])) {
    $data = array_merge($data, $_SESSION['form_portfolio']);
    unset($_SESSION['form_portfolio']);
}

// Daftar kategori
$kategori_options = [
    'web'      => 'Website',
    'android'  => 'Aplikasi Android',
    'ios'      => 'Aplikasi iOS',
    'desktop'  => 'Aplikasi Desktop',
    'desain'   => 'Desain UI/UX',
    'lainnya'  => 'Lainnya',
];

// Cek apakah gambar existing ada
$gambar_existing = !empty($data['gambar']) && file_exists(PORTFOLIO_PATH . $data['gambar']);
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-<?= $mode === 'edit' ? 'pencil' : 'plus-circle' ?> text-primary"></i>
            <?= $mode === 'edit' ? 'Edit Portfolio' : 'Tambah Portfolio' ?>
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
            <li class="breadcrumb-item"><a href="portfolio.php">Portfolio</a></li>
            <li class="breadcrumb-item active"><?= $mode === 'edit' ? 'Edit' : 'Tambah' ?></li>
        </ol>
    </nav>

    <form action="portfolio_simpan.php" method="POST" enctype="multipart/form-data" id="formPortfolio">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($data['gambar']) ?>">

        <div class="row g-3">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <div class="card stat-card">
                    <div class="card-body">

                        <h5 class="mb-4">
                            <i class="bi bi-info-circle text-primary"></i> Informasi Portfolio
                        </h5>

                        <!-- Judul -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Judul Proyek <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="judul" 
                                   id="judul" 
                                   class="form-control form-control-lg" 
                                   placeholder="Contoh: Website Toko Online ABC"
                                   value="<?= htmlspecialchars($data['judul']) ?>"
                                   required maxlength="150"
                                   onkeyup="generateSlug()">
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slug URL</label>
                            <div class="input-group">
                                <span class="input-group-text small"><?= BASE_URL ?>portfolio/</span>
                                <input type="text" 
                                       name="slug" 
                                       id="slug" 
                                       class="form-control" 
                                       placeholder="website-toko-online-abc"
                                       value="<?= htmlspecialchars($data['slug']) ?>"
                                       maxlength="170">
                            </div>
                            <small class="text-muted">Kosongkan untuk generate otomatis.</small>
                        </div>

                        <!-- Kategori & Klien -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <select name="kategori" class="form-select" required>
                                    <?php foreach ($kategori_options as $key => $label): ?>
                                        <option value="<?= $key ?>" 
                                                <?= $data['kategori'] === $key ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nama Klien</label>
                                <input type="text" 
                                       name="klien" 
                                       class="form-control" 
                                       placeholder="Contoh: PT Maju Jaya"
                                       value="<?= htmlspecialchars($data['klien']) ?>"
                                       maxlength="100">
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Proyek</label>
                            <textarea name="deskripsi" 
                                      class="form-control" 
                                      rows="6" 
                                      placeholder="Ceritakan tentang proyek ini: latar belakang, tantangan, solusi yang diberikan..."><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                        </div>

                        <!-- Teknologi -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teknologi yang Digunakan</label>
                            <input type="text" 
                                   name="teknologi" 
                                   class="form-control" 
                                   placeholder="Contoh: PHP, MySQL, Bootstrap, jQuery"
                                   value="<?= htmlspecialchars($data['teknologi']) ?>"
                                   maxlength="200">
                            <small class="text-muted">
                                Pisahkan dengan koma. Contoh: <code>Laravel, MySQL, Tailwind</code>
                            </small>
                        </div>

                        <!-- Link Demo -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Link Demo / Live Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" 
                                       name="link_demo" 
                                       class="form-control" 
                                       placeholder="https://contoh.com"
                                       value="<?= htmlspecialchars($data['link_demo']) ?>"
                                       maxlength="255">
                            </div>
                            <small class="text-muted">Opsional. Kosongkan kalau proyek private.</small>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">

                <!-- Upload Gambar -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-image text-info"></i> Gambar Portfolio
                        </h5>

                        <!-- Preview Area -->
                        <div id="previewBox" 
                             class="mb-3 text-center p-2 rounded"
                             style="background: #f8f9fa; border: 2px dashed #dee2e6; min-height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            
                            <?php if ($gambar_existing): ?>
                                <img src="<?= UPLOADS_URL ?>portfolio/<?= htmlspecialchars($data['gambar']) ?>" 
                                     id="imgPreview"
                                     style="max-width: 100%; max-height: 260px; object-fit: contain; border-radius: 8px;">
                            <?php else: ?>
                                <div id="placeholder" class="text-muted">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 50px; opacity: 0.4;"></i>
                                    <p class="small mb-0 mt-2">Belum ada gambar</p>
                                </div>
                                <img id="imgPreview" style="display: none; max-width: 100%; max-height: 260px; object-fit: contain; border-radius: 8px;">
                            <?php endif; ?>
                        </div>

                        <input type="file" 
                               name="gambar" 
                               id="inputGambar" 
                               class="form-control" 
                               accept="image/jpeg,image/png,image/webp,image/gif"
                               onchange="previewImage(event)">

                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i> 
                            Format: JPG, PNG, WEBP, GIF. Maks 2MB.<br>
                            Rasio disarankan: <strong>16:10</strong> (mis. 1200x750px).
                        </small>

                        <?php if ($mode === 'edit' && $gambar_existing): ?>
                            <div class="form-check mt-3">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="hapus_gambar" 
                                       id="hapus_gambar" 
                                       value="1">
                                <label class="form-check-label small text-danger" for="hapus_gambar">
                                    <i class="bi bi-trash"></i> Hapus gambar ini
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tanggal Selesai -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-calendar-check text-success"></i> Tanggal Selesai
                        </h5>
                        <input type="date" 
                               name="tanggal_selesai" 
                               class="form-control" 
                               value="<?= htmlspecialchars($data['tanggal_selesai']) ?>"
                               max="<?= date('Y-m-d') ?>">
                        <small class="text-muted">Kapan proyek ini selesai dikerjakan.</small>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> 
                                <?= $mode === 'edit' ? 'Update Portfolio' : 'Simpan Portfolio' ?>
                            </button>
                            <a href="portfolio.php" class="btn btn-outline-secondary">
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
// Auto-generate slug
function generateSlug() {
    const judul = document.getElementById('judul').value;
    const slugInput = document.getElementById('slug');
    if (slugInput.dataset.manual !== 'true') {
        const slug = judul
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
    }
}
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Preview gambar
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validasi ukuran (2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 2MB.');
        event.target.value = '';
        return;
    }

    // Validasi tipe
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        alert('Format file harus JPG, PNG, WEBP, atau GIF.');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById('imgPreview');
        const placeholder = document.getElementById('placeholder');
        
        img.src = e.target.result;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';

        // Auto-check hapus_gambar kalau ada
        const cbHapus = document.getElementById('hapus_gambar');
        if (cbHapus) cbHapus.checked = false;
    };
    reader.readAsDataURL(file);
}

// Drag & drop pada preview box
const previewBox = document.getElementById('previewBox');
const inputGambar = document.getElementById('inputGambar');

previewBox.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = '#0D6EFD';
    this.style.background = '#e7f1ff';
});

previewBox.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = '#dee2e6';
    this.style.background = '#f8f9fa';
});

previewBox.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '#dee2e6';
    this.style.background = '#f8f9fa';

    const file = e.dataTransfer.files[0];
    if (file) {
        inputGambar.files = e.dataTransfer.files;
        previewImage({ target: { files: e.dataTransfer.files, value: '' } });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

$data = [
    'judul' => '',
    'slug' => '',
    'konten' => '',
    'gambar' => '',
    'penulis' => $_SESSION['admin_nama'] ?? 'Admin',
    'status' => 'draft',
];

if ($mode === 'edit') {
    $stmt = $koneksi->prepare("SELECT * FROM artikel WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        set_flash('danger', 'Artikel tidak ditemukan!');
        redirect(ADMIN_URL . 'artikel.php');
    }
    $data = $result->fetch_assoc();
}

if (isset($_SESSION['form_artikel'])) {
    $data = array_merge($data, $_SESSION['form_artikel']);
    unset($_SESSION['form_artikel']);
}

$gambar_existing = !empty($data['gambar']) && file_exists(ARTIKEL_PATH . $data['gambar']);
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-<?= $mode === 'edit' ? 'pencil' : 'plus-circle' ?> text-primary"></i>
            <?= $mode === 'edit' ? 'Edit Artikel' : 'Tulis Artikel' ?>
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
            <li class="breadcrumb-item"><a href="artikel.php">Artikel</a></li>
            <li class="breadcrumb-item active"><?= $mode === 'edit' ? 'Edit' : 'Tulis' ?></li>
        </ol>
    </nav>

    <form action="artikel_simpan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($data['gambar']) ?>">

        <div class="row g-3">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <div class="card stat-card">
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Judul Artikel <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="judul" id="judul"
                                   class="form-control form-control-lg"
                                   placeholder="Contoh: 5 Tips Memilih Jasa Pembuatan Website"
                                   value="<?= htmlspecialchars($data['judul']) ?>"
                                   required maxlength="200" onkeyup="generateSlug()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slug URL</label>
                            <div class="input-group">
                                <span class="input-group-text small"><?= BASE_URL ?>artikel/</span>
                                <input type="text" name="slug" id="slug" class="form-control"
                                       placeholder="5-tips-memilih-jasa-website"
                                       value="<?= htmlspecialchars($data['slug']) ?>"
                                       maxlength="220">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Konten Artikel <span class="text-danger">*</span>
                            </label>
                            <div class="btn-toolbar mb-2" role="toolbar">
                                <div class="btn-group btn-group-sm me-2" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatText('bold')" title="Bold">
                                        <i class="bi bi-type-bold"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatText('italic')" title="Italic">
                                        <i class="bi bi-type-italic"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatText('underline')" title="Underline">
                                        <i class="bi bi-type-underline"></i>
                                    </button>
                                </div>
                                <div class="btn-group btn-group-sm me-2" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatBlock('h2')" title="Heading 2">
                                        H2
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatBlock('h3')" title="Heading 3">
                                        H3
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatBlock('p')" title="Paragraph">
                                        P
                                    </button>
                                </div>
                                <div class="btn-group btn-group-sm me-2" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatBlock('ul')" title="List">
                                        <i class="bi bi-list-ul"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="formatText('insertUnorderedList')" title="Bullet">
                                        <i class="bi bi-list-check"></i>
                                    </button>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="insertLink()" title="Link">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearFormat()" title="Hapus Format">
                                        <i class="bi bi-eraser"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="editor" contenteditable="true"
                                 class="form-control"
                                 style="min-height: 400px; overflow-y: auto; padding: 15px; background: #fff; line-height: 1.7;">
                                <?= $data['konten'] ?>
                            </div>
                            <textarea name="konten" id="konten" style="display:none;"><?= htmlspecialchars($data['konten']) ?></textarea>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Editor sederhana. HTML akan diizinkan.
                            </small>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">

                <!-- Publish -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-send text-success"></i> Publish
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>
                                    📝 Draft (belum tampil)
                                </option>
                                <option value="publish" <?= $data['status'] === 'publish' ? 'selected' : '' ?>>
                                    ✅ Publish (tampil di website)
                                </option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Penulis</label>
                            <input type="text" name="penulis" class="form-control"
                                   value="<?= htmlspecialchars($data['penulis']) ?>"
                                   maxlength="100">
                        </div>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-image text-info"></i> Thumbnail
                        </h5>

                        <div class="text-center mb-3 p-2 rounded"
                             style="background: #f8f9fa; border: 2px dashed #dee2e6; min-height: 150px; overflow: hidden;">
                            <?php if ($gambar_existing): ?>
                                <img src="<?= UPLOADS_URL ?>artikel/<?= htmlspecialchars($data['gambar']) ?>"
                                     id="imgPreview"
                                     style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 6px;">
                            <?php else: ?>
                                <div id="placeholder" class="text-muted py-4">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 40px; opacity: 0.4;"></i>
                                    <p class="small mb-0 mt-2">Belum ada gambar</p>
                                </div>
                                <img id="imgPreview" style="display:none; max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 6px;">
                            <?php endif; ?>
                        </div>

                        <input type="file" name="gambar" class="form-control"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="previewImage(event)">
                        <small class="text-muted d-block mt-2">
                            Maks 2MB. Rasio disarankan 16:9 (1200x675px).
                        </small>

                        <?php if ($mode === 'edit' && $gambar_existing): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                       name="hapus_gambar" id="hapus_gambar" value="1">
                                <label class="form-check-label small text-danger" for="hapus_gambar">
                                    <i class="bi bi-trash"></i> Hapus gambar
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i>
                                <?= $mode === 'edit' ? 'Update Artikel' : 'Simpan Artikel' ?>
                            </button>
                            <a href="artikel.php" class="btn btn-outline-secondary">
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
// ============ SLUG ============
function generateSlug() {
    const judul = document.getElementById('judul').value;
    const slugInput = document.getElementById('slug');
    if (slugInput.dataset.manual !== 'true') {
        const slug = judul.toLowerCase()
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

// ============ EDITOR ============
function formatText(cmd) {
    document.execCommand(cmd, false, null);
    document.getElementById('editor').focus();
}

function formatBlock(tag) {
    document.execCommand('formatBlock', false, tag);
    document.getElementById('editor').focus();
}

function insertLink() {
    const url = prompt('Masukkan URL:');
    if (url) document.execCommand('createLink', false, url);
}

function clearFormat() {
    document.execCommand('removeFormat', false, null);
}

// Update hidden textarea saat form submit
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('konten').value = document.getElementById('editor').innerHTML;
});

// ============ PREVIEW GAMBAR ============
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 2MB.');
        event.target.value = '';
        return;
    }

    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowed.includes(file.type)) {
        alert('Format harus JPG, PNG, atau WEBP.');
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

        const cb = document.getElementById('hapus_gambar');
        if (cb) cb.checked = false;
    };
    reader.readAsDataURL(file);
}
</script>

<?php require_once 'includes/footer.php'; ?>
<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Cek mode: tambah atau edit
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

// Default data
$data = [
    'nama' => '',
    'slug' => '',
    'deskripsi_singkat' => '',
    'deskripsi_lengkap' => '',
    'harga_mulai' => 0,
    'icon' => 'bi-code-slash',
    'gambar' => '',
    'urutan' => 0,
    'status' => 'aktif',
];

// Kalau mode edit, ambil data dari DB
if ($mode === 'edit') {
    $stmt = $koneksi->prepare("SELECT * FROM layanan WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        set_flash('danger', 'Layanan tidak ditemukan!');
        redirect(ADMIN_URL . 'layanan.php');
    }
    $data = $result->fetch_assoc();
}

// Ambil data lama dari session kalau ada (untuk refill form saat error)
if (isset($_SESSION['form_layanan'])) {
    $data = array_merge($data, $_SESSION['form_layanan']);
    unset($_SESSION['form_layanan']);
}

// Daftar icon Bootstrap Icons yang tersedia
$icons = [
    'bi-globe', 'bi-phone', 'bi-server', 'bi-tools', 'bi-palette',
    'bi-graph-up-arrow', 'bi-code-slash', 'bi-cart', 'bi-shop', 'bi-building',
    'bi-laptop', 'bi-cloud', 'bi-shield-check', 'bi-gear', 'bi-star',
    'bi-heart', 'bi-lightning', 'bi-rocket', 'bi-puzzle', 'bi-cpu'
];
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-<?= $mode === 'edit' ? 'pencil' : 'plus-circle' ?> text-primary"></i> 
            <?= $mode === 'edit' ? 'Edit Layanan' : 'Tambah Layanan' ?>
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
            <li class="breadcrumb-item"><a href="layanan.php">Layanan</a></li>
            <li class="breadcrumb-item active"><?= $mode === 'edit' ? 'Edit' : 'Tambah' ?></li>
        </ol>
    </nav>

    <form action="layanan_simpan.php" method="POST" id="formLayanan">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">

        <div class="row g-3">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <div class="card stat-card">
                    <div class="card-body">

                        <h5 class="mb-4">
                            <i class="bi bi-info-circle text-primary"></i> Informasi Layanan
                        </h5>

                        <!-- Nama Layanan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Layanan <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="nama" 
                                   id="nama" 
                                   class="form-control form-control-lg" 
                                   placeholder="Contoh: Pembuatan Website"
                                   value="<?= htmlspecialchars($data['nama']) ?>"
                                   required maxlength="100"
                                   onkeyup="generateSlug()">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Nama layanan yang akan ditampilkan di website.
                            </small>
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slug URL</label>
                            <div class="input-group">
                                <span class="input-group-text small">
                                    <?= BASE_URL ?>layanan/
                                </span>
                                <input type="text" 
                                       name="slug" 
                                       id="slug" 
                                       class="form-control" 
                                       placeholder="pembuatan-website"
                                       value="<?= htmlspecialchars($data['slug']) ?>"
                                       maxlength="120">
                            </div>
                            <small class="text-muted">
                                Kosongkan untuk generate otomatis dari nama. Hanya huruf, angka, dan tanda minus.
                            </small>
                        </div>

                        <!-- Deskripsi Singkat -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Deskripsi Singkat <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi_singkat" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Deskripsi singkat untuk card di halaman depan..."
                                      required maxlength="300"><?= htmlspecialchars($data['deskripsi_singkat']) ?></textarea>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Maks 300 karakter. Muncul di kartu layanan.
                            </small>
                        </div>

                        <!-- Deskripsi Lengkap -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Lengkap</label>
                            <textarea name="deskripsi_lengkap" 
                                      class="form-control" 
                                      rows="8" 
                                      placeholder="Penjelasan detail layanan, fitur, keunggulan..."><?= htmlspecialchars($data['deskripsi_lengkap']) ?></textarea>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Muncul di halaman detail layanan.
                            </small>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">

                <!-- Harga & Urutan -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-cash-coin text-success"></i> Harga & Urutan
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Harga Mulai (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" 
                                       name="harga_mulai" 
                                       class="form-control" 
                                       value="<?= (int)$data['harga_mulai'] ?>"
                                       min="0" step="50000"
                                       placeholder="2500000">
                            </div>
                            <small class="text-muted">Isi 0 kalau harga negotiable.</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Urutan Tampil</label>
                            <input type="number" 
                                   name="urutan" 
                                   class="form-control" 
                                   value="<?= (int)$data['urutan'] ?>"
                                   min="0" max="999">
                            <small class="text-muted">Angka kecil tampil lebih dulu.</small>
                        </div>
                    </div>
                </div>

                <!-- Icon Picker -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-emoji-smile text-warning"></i> Icon
                        </h5>

                        <label class="form-label fw-semibold">Pilih Icon</label>
                        <input type="hidden" name="icon" id="iconInput" value="<?= htmlspecialchars($data['icon']) ?>">

                        <div class="mb-2 text-center p-3 bg-light rounded">
                            <i class="bi <?= htmlspecialchars($data['icon']) ?>" 
                               id="iconPreview" 
                               style="font-size: 40px; color: #0D6EFD;"></i>
                            <div class="small text-muted mt-2" id="iconName">
                                <?= htmlspecialchars($data['icon']) ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2" id="iconGrid">
                            <?php foreach ($icons as $ic): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary icon-btn"
                                        data-icon="<?= $ic ?>"
                                        style="width: 42px; height: 42px; padding: 0;">
                                    <i class="bi <?= $ic ?>"></i>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-toggle-on text-info"></i> Status
                        </h5>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="status" 
                                   id="status" 
                                   value="aktif"
                                   <?= $data['status'] === 'aktif' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="status">
                                Aktifkan Layanan
                            </label>
                        </div>
                        <small class="text-muted">
                            Layanan nonaktif tidak akan muncul di website.
                        </small>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> 
                                <?= $mode === 'edit' ? 'Update Layanan' : 'Simpan Layanan' ?>
                            </button>
                            <a href="layanan.php" class="btn btn-outline-secondary">
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
// Auto-generate slug dari nama
function generateSlug() {
    const nama = document.getElementById('nama').value;
    const slugInput = document.getElementById('slug');
    
    // Hanya auto-generate kalau slug masih kosong atau belum diubah manual
    if (slugInput.dataset.manual !== 'true') {
        const slug = nama
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
    }
}

// Tandai slug diubah manual
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Icon picker
document.querySelectorAll('.icon-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const icon = this.dataset.icon;
        document.getElementById('iconInput').value = icon;
        document.getElementById('iconPreview').className = 'bi ' + icon;
        document.getElementById('iconName').textContent = icon;
        
        // Highlight yang aktif
        document.querySelectorAll('.icon-btn').forEach(b => b.classList.remove('btn-primary', 'text-white'));
        document.querySelectorAll('.icon-btn').forEach(b => b.classList.add('btn-outline-secondary'));
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-primary', 'text-white');
    });
});

// Highlight icon yang aktif saat load
document.querySelectorAll('.icon-btn').forEach(function(btn) {
    if (btn.dataset.icon === document.getElementById('iconInput').value) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary', 'text-white');
    }
});

// Toggle status switch — set value
document.getElementById('status').addEventListener('change', function() {
    // Kalau unchecked, value tidak dikirim ke POST, kita handle di backend
});
</script>

<?php require_once 'includes/footer.php'; ?>
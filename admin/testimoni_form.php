<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

$data = [
    'nama' => '',
    'jabatan' => '',
    'perusahaan' => '',
    'isi' => '',
    'foto' => '',
    'rating' => 5,
    'status' => 'tampil',
];

if ($mode === 'edit') {
    $stmt = $koneksi->prepare("SELECT * FROM testimoni WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        set_flash('danger', 'Testimoni tidak ditemukan!');
        redirect(ADMIN_URL . 'testimoni.php');
    }
    $data = $result->fetch_assoc();
}

if (isset($_SESSION['form_testimoni'])) {
    $data = array_merge($data, $_SESSION['form_testimoni']);
    unset($_SESSION['form_testimoni']);
}
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-<?= $mode === 'edit' ? 'pencil' : 'plus-circle' ?> text-primary"></i>
            <?= $mode === 'edit' ? 'Edit Testimoni' : 'Tambah Testimoni' ?>
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
            <li class="breadcrumb-item"><a href="testimoni.php">Testimoni</a></li>
            <li class="breadcrumb-item active"><?= $mode === 'edit' ? 'Edit' : 'Tambah' ?></li>
        </ol>
    </nav>

    <form action="testimoni_simpan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($data['foto']) ?>">

        <div class="row g-3">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <div class="card stat-card">
                    <div class="card-body">

                        <h5 class="mb-4">
                            <i class="bi bi-person text-primary"></i> Data Klien
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Klien <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama" class="form-control form-control-lg"
                                   placeholder="Contoh: Budi Santoso"
                                   value="<?= htmlspecialchars($data['nama']) ?>"
                                   required maxlength="100">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Jabatan</label>
                                <input type="text" name="jabatan" class="form-control"
                                       placeholder="Contoh: CEO"
                                       value="<?= htmlspecialchars($data['jabatan']) ?>"
                                       maxlength="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Perusahaan</label>
                                <input type="text" name="perusahaan" class="form-control"
                                       placeholder="Contoh: PT Maju Jaya"
                                       value="<?= htmlspecialchars($data['perusahaan']) ?>"
                                       maxlength="100">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">
                            <i class="bi bi-chat-quote text-success"></i> Isi Testimoni
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Testimoni <span class="text-danger">*</span>
                            </label>
                            <textarea name="isi" class="form-control" rows="5"
                                      placeholder="Tulis testimoni dari klien..."
                                      required maxlength="1000"><?= htmlspecialchars($data['isi']) ?></textarea>
                            <small class="text-muted">Maks 1000 karakter.</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="rating<?= $i ?>" value="<?= $i ?>"
                                           <?= $data['rating'] == $i ? 'checked' : '' ?> style="display:none;">
                                    <label for="rating<?= $i ?>" class="rating-star" style="cursor:pointer; font-size: 30px; color: #ddd;">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                <?php endfor; ?>
                                <span class="ms-2 fw-bold text-warning" id="ratingText"><?= $data['rating'] ?>.0</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">

                <!-- Foto -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-person-circle text-info"></i> Foto Klien
                        </h5>

                        <div class="text-center mb-3">
                            <?php $foto_existing = !empty($data['foto']) && file_exists(PORTFOLIO_PATH . 'testimoni/' . $data['foto']); ?>
                            <?php if ($foto_existing): ?>
                                <img src="<?= UPLOADS_URL ?>portfolio/testimoni/<?= htmlspecialchars($data['foto']) ?>"
                                     id="fotoPreview"
                                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #0D6EFD;">
                            <?php else: ?>
                                <div id="fotoPlaceholder"
                                     class="d-inline-flex align-items-center justify-content-center bg-light text-muted"
                                     style="width: 120px; height: 120px; border-radius: 50%; border: 3px dashed #dee2e6;">
                                    <i class="bi bi-person" style="font-size: 50px;"></i>
                                </div>
                                <img id="fotoPreview" style="display: none; width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #0D6EFD;">
                            <?php endif; ?>
                        </div>

                        <input type="file" name="foto" class="form-control"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="previewFoto(event)">
                        <small class="text-muted d-block mt-2">
                            Opsional. Format JPG/PNG/WEBP. Maks 1MB.<br>
                            Disarankan rasio 1:1 (persegi).
                        </small>

                        <?php if ($mode === 'edit' && $foto_existing): ?>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox"
                                       name="hapus_foto" id="hapus_foto" value="1">
                                <label class="form-check-label small text-danger" for="hapus_foto">
                                    <i class="bi bi-trash"></i> Hapus foto ini
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status -->
                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="bi bi-toggle-on text-info"></i> Status
                        </h5>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="status" id="status" value="tampil"
                                   <?= $data['status'] === 'tampil' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="status">
                                Tampilkan di website
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i>
                                <?= $mode === 'edit' ? 'Update Testimoni' : 'Simpan Testimoni' ?>
                            </button>
                            <a href="testimoni.php" class="btn btn-outline-secondary">
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
// Preview foto
function previewFoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 1MB.');
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
        const img = document.getElementById('fotoPreview');
        const placeholder = document.getElementById('fotoPlaceholder');
        img.src = e.target.result;
        img.style.display = 'inline-block';
        if (placeholder) placeholder.style.display = 'none';

        const cb = document.getElementById('hapus_foto');
        if (cb) cb.checked = false;
    };
    reader.readAsDataURL(file);
}

// Rating bintang interaktif
const ratingStars = document.querySelectorAll('.rating-star');
const ratingInputs = document.querySelectorAll('input[name="rating"]');

function updateRatingDisplay(rating) {
    ratingStars.forEach((star, index) => {
        // Rating ditampilkan dari kiri (5 → 1)
        const nilai = 5 - index;
        star.style.color = nilai <= rating ? '#FFC107' : '#ddd';
    });
    document.getElementById('ratingText').textContent = rating + '.0';
}

ratingStars.forEach((star, index) => {
    star.addEventListener('mouseenter', function() {
        const nilai = 5 - index;
        ratingStars.forEach((s, i) => {
            const n = 5 - i;
            s.style.color = n <= nilai ? '#FFC107' : '#ddd';
        });
    });

    star.addEventListener('click', function() {
        const nilai = 5 - index;
        document.getElementById('rating' + nilai).checked = true;
        updateRatingDisplay(nilai);
    });
});

document.querySelector('.rating-input').addEventListener('mouseleave', function() {
    const selected = document.querySelector('input[name="rating"]:checked').value;
    updateRatingDisplay(selected);
});

// Init
const initialRating = document.querySelector('input[name="rating"]:checked').value;
updateRatingDisplay(initialRating);
</script>

<?php require_once 'includes/footer.php'; ?>
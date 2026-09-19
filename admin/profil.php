<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

// Ambil data admin yang login
$stmt = $koneksi->prepare("SELECT * FROM admin WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    redirect(ADMIN_URL . 'logout.php');
}

// Refill dari session
if (isset($_SESSION['form_profil'])) {
    $admin = array_merge($admin, $_SESSION['form_profil']);
    unset($_SESSION['form_profil']);
}
?>

<!-- Topbar -->
<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <i class="bi bi-person-gear text-primary"></i> Profil Saya
        </h1>
    </div>
    <div class="user-info">
        <div class="avatar"><?= strtoupper(substr($admin['nama'], 0, 1)) ?></div>
    </div>
</div>

<div class="content-area">

    <?php tampil_flash(); ?>

    <div class="row g-3">

        <!-- Kartu Profil -->
        <div class="col-lg-4">
            <div class="card stat-card text-center">
                <div class="card-body py-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #0D6EFD, #0A2540); color: #fff; font-size: 40px; font-weight: 700;">
                        <?= strtoupper(substr($admin['nama'], 0, 1)) ?>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($admin['nama']) ?></h4>
                    <p class="text-muted small mb-2">@<?= htmlspecialchars($admin['username']) ?></p>
                    <span class="badge bg-<?= $admin['role'] === 'super' ? 'dark' : 'primary' ?>">
                        <i class="bi bi-shield-<?= $admin['role'] === 'super' ? 'fill-check' : 'check' ?>"></i>
                        <?= ucfirst($admin['role']) ?>
                    </span>
                    <hr>
                    <div class="text-start small">
                        <div class="mb-2">
                            <i class="bi bi-envelope text-primary"></i>
                            <?= htmlspecialchars($admin['email'] ?: 'Belum diisi') ?>
                        </div>
                        <div>
                            <i class="bi bi-calendar text-primary"></i>
                            Bergabung <?= tanggal_indo($admin['created_at']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit -->
        <div class="col-lg-8">
            <form action="profil_simpan.php" method="POST" autocomplete="off">
                <input type="hidden" name="aksi" value="profil">

                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-person text-primary"></i> Edit Profil
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama" class="form-control"
                                   value="<?= htmlspecialchars($admin['nama']) ?>"
                                   required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($admin['email']) ?>"
                                   maxlength="100">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                            <small class="text-muted">Username tidak bisa diubah.</small>
                        </div>
                    </div>
                </div>

                <div class="card stat-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Form Ganti Password -->
            <form action="profil_simpan.php" method="POST" autocomplete="off" id="formPassword">
                <input type="hidden" name="aksi" value="password">

                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-lock text-warning"></i> Ganti Password
                        </h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Password Lama <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_lama" class="form-control"
                                   placeholder="Masukkan password lama" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Password Baru <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_baru" class="form-control"
                                   placeholder="Minimal 6 karakter" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Konfirmasi Password Baru <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_konfirmasi" class="form-control"
                                   placeholder="Ulangi password baru" required minlength="6">
                        </div>

                        <div class="alert alert-warning small mb-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            Setelah ganti password, Anda akan logout otomatis dan perlu login ulang.
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key"></i> Ganti Password
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

</div>

<script>
document.getElementById('formPassword').addEventListener('submit', function(e) {
    const baru = document.querySelector('input[name="password_baru"]').value;
    const konfirmasi = document.querySelector('input[name="password_konfirmasi"]').value;

    if (baru !== konfirmasi) {
        e.preventDefault();
        alert('Password baru dan konfirmasi tidak sama!');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
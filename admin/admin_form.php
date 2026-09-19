<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'edit' : 'tambah';

// Cek: kalau edit akun sendiri, redirect ke profil
if ($mode === 'edit' && $id == $_SESSION['admin_id']) {
    redirect(ADMIN_URL . 'profil.php');
}

$data = [
    'username' => '',
    'nama' => '',
    'email' => '',
    'role' => 'admin',
];

if ($mode === 'edit') {
    $stmt = $koneksi->prepare("SELECT * FROM admin WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        set_flash('danger', 'Admin tidak ditemukan!');
        redirect(ADMIN_URL . 'admin.php');
    }
    $data = $result->fetch_assoc();
}

if (isset($_SESSION['form_admin'])) {
    $data = array_merge($data, $_SESSION['form_admin']);
    unset($_SESSION['form_admin']);
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
            <?= $mode === 'edit' ? 'Edit Admin' : 'Tambah Admin' ?>
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
            <li class="breadcrumb-item"><a href="admin.php">Admin</a></li>
            <li class="breadcrumb-item active"><?= $mode === 'edit' ? 'Edit' : 'Tambah' ?></li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <form action="admin_simpan.php" method="POST" autocomplete="off">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="mode" value="<?= $mode ?>">

                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="mb-4">
                            <i class="bi bi-person-plus text-primary"></i> Data Admin
                        </h5>

                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Username <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control"
                                       placeholder="contoh: budi_admin"
                                       value="<?= htmlspecialchars($data['username']) ?>"
                                       required maxlength="50"
                                       pattern="[a-zA-Z0-9_.-]+"
                                       title="Hanya huruf, angka, titik, underscore, dan dash">
                            </div>
                            <small class="text-muted">Minimal 4 karakter. Tanpa spasi.</small>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama" class="form-control"
                                   placeholder="Contoh: Budi Santoso"
                                   value="<?= htmlspecialchars($data['nama']) ?>"
                                   required maxlength="100">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control"
                                       placeholder="budi@website.com"
                                       value="<?= htmlspecialchars($data['email']) ?>"
                                       maxlength="100">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Password 
                                <?= $mode === 'tambah' ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control"
                                       placeholder="<?= $mode === 'edit' ? 'Kosongkan kalau tidak diubah' : 'Minimal 6 karakter' ?>"
                                       <?= $mode === 'tambah' ? 'required' : '' ?>
                                       minlength="6">
                                <span class="input-group-text" style="cursor:pointer;" onclick="togglePwd('password', 'eye1')">
                                    <i class="bi bi-eye" id="eye1"></i>
                                </span>
                            </div>
                            <?php if ($mode === 'edit'): ?>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Biarkan kosong kalau tidak ingin mengubah password.
                                </small>
                            <?php else: ?>
                                <small class="text-muted">Minimal 6 karakter.</small>
                            <?php endif; ?>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Konfirmasi Password
                                <?= $mode === 'tambah' ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                                       placeholder="Ulangi password"
                                       <?= $mode === 'tambah' ? 'required' : '' ?>
                                       minlength="6">
                                <span class="input-group-text" style="cursor:pointer;" onclick="togglePwd('password_confirm', 'eye2')">
                                    <i class="bi bi-eye" id="eye2"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                Role <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select" required>
                                <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>
                                    Admin — Akses terbatas
                                </option>
                                <option value="super" <?= $data['role'] === 'super' ? 'selected' : '' ?>>
                                    Super Admin — Akses penuh
                                </option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <strong>Admin</strong>: bisa kelola konten & pesanan.<br>
                                <strong>Super Admin</strong>: akses penuh termasuk kelola admin.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="card stat-card mt-3">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                <i class="bi bi-save"></i>
                                <?= $mode === 'edit' ? 'Update Admin' : 'Simpan Admin' ?>
                            </button>
                            <a href="admin.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
function togglePwd(inputId, iconId) {
    const pwd = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Validasi password match client-side
document.querySelector('form').addEventListener('submit', function(e) {
    const pwd = document.getElementById('password').value;
    const pwd2 = document.getElementById('password_confirm').value;

    if (pwd !== pwd2) {
        e.preventDefault();
        alert('Password dan konfirmasi password tidak sama!');
        return false;
    }

    if (pwd.length > 0 && pwd.length < 6) {
        e.preventDefault();
        alert('Password minimal 6 karakter!');
        return false;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
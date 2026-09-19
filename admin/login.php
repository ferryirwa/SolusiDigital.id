<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Kalau sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'dashboard.php');
}

$error = '';
if (isset($_SESSION['error_login'])) {
    $error = $_SESSION['error_login'];
    unset($_SESSION['error_login']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0D6EFD 0%, #0A2540 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-card .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card .logo i {
            font-size: 50px;
            color: #0D6EFD;
        }
        .login-card h3 {
            text-align: center;
            margin-bottom: 5px;
            font-weight: 700;
        }
        .login-card p.subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }
        .btn-login {
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
        }
        .input-group-text {
            background: #f8f9fa;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">
        <i class="bi bi-code-slash"></i>
    </div>
    <h3>Login Admin</h3>
    <p class="subtitle"><?= SITE_NAME ?> — Panel Administrator</p>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php tampil_flash(); ?>

    <form action="proses_login.php" method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control" 
                       placeholder="Masukkan username" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control" 
                       placeholder="Masukkan password" required>
                <span class="input-group-text" onclick="togglePassword()">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login">
            <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
    </form>

    <hr class="my-4">
    <p class="text-center text-muted mb-0" style="font-size: 13px;">
        &copy; <?= date('Y') ?> <?= SITE_NAME ?>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
</body>
</html>
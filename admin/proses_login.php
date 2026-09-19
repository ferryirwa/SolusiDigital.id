<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

// Hanya izinkan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . 'login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input
if (empty($username) || empty($password)) {
    $_SESSION['error_login'] = 'Username dan password wajib diisi!';
    redirect(ADMIN_URL . 'login.php');
}

// Rate limiting sederhana — max 5 percobaan per 5 menit
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_time'] = time();
}

// Reset kalau sudah lewat 5 menit
if (time() - $_SESSION['login_time'] > 300) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_time'] = time();
}

if ($_SESSION['login_attempts'] >= 5) {
    $sisa = 300 - (time() - $_SESSION['login_time']);
    $_SESSION['error_login'] = 'Terlalu banyak percobaan. Coba lagi dalam ' . ceil($sisa / 60) . ' menit.';
    redirect(ADMIN_URL . 'login.php');
}

// Query cek user (prepared statement — anti SQL injection)
$stmt = $koneksi->prepare("SELECT id, username, password, nama, email, role FROM admin WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    // Verifikasi password
    if (password_verify($password, $admin['password'])) {
        // Login sukses
        session_regenerate_id(true); // Anti session fixation

        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_user']  = $admin['username'];
        $_SESSION['admin_nama']  = $admin['nama'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role']  = $admin['role'];
        $_SESSION['login_at']    = time();

        // Reset attempts
        $_SESSION['login_attempts'] = 0;

        set_flash('success', 'Selamat datang, ' . htmlspecialchars($admin['nama']) . '!');
        redirect(ADMIN_URL . 'dashboard.php');
    }
}

// Login gagal
$_SESSION['login_attempts']++;
$_SESSION['error_login'] = 'Username atau password salah!';
redirect(ADMIN_URL . 'login.php');
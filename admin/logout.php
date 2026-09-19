<?php
require_once '../config/konfigurasi.php';
require_once '../config/fungsi.php';

// Hapus semua session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mulai session baru untuk flash message
session_start();
session_regenerate_id(true);
$_SESSION['flash'] = ['tipe' => 'success', 'pesan' => 'Anda berhasil logout.'];

redirect(ADMIN_URL . 'login.php');
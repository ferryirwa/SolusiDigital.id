<?php
/**
 * File Koneksi Database
 * Website Jasa Programmer
 */

// Cegah akses langsung
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Kosongkan kalau XAMPP default
define('DB_NAME', 'db_jasa_programmer');

// Buat koneksi
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($koneksi->connect_error) {
    die('Koneksi database gagal: ' . $koneksi->connect_error);
}

// Set charset UTF-8
$koneksi->set_charset('utf8mb4');

// Set timezone
date_default_timezone_set('Asia/Jakarta');
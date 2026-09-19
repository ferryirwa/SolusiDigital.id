<?php
/**
 * Konfigurasi Global Website
 * 
 * @package Website Jasa Programmer
 */

// URL Base (sesuaikan dengan nama folder Anda)
define('BASE_URL', 'http://localhost/website-jasa/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

// Path Fisik
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('PORTFOLIO_PATH', UPLOAD_PATH . 'portfolio/');
define('ARTIKEL_PATH', UPLOAD_PATH . 'artikel/');

// Info Situs
define('SITE_NAME', 'JasaProgrammer.id');
define('SITE_DESC', 'Jasa Pembuatan Website, Aplikasi Android & Hosting Profesional');

// Session
define('SESSION_TIMEOUT', 3600); // 1 jam

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
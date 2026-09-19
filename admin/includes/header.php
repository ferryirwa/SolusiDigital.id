<?php
/**
 * Header Template Admin
 * 
 * @var array $pengaturan
 * @var mysqli $koneksi
 */

// Cek login
if (!isset($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . 'login.php');
}

// Cek session timeout
if (isset($_SESSION['login_at']) && (time() - $_SESSION['login_at'] > SESSION_TIMEOUT)) {
    session_destroy();
    session_start();
    $_SESSION['error_login'] = 'Sesi berakhir. Silakan login kembali.';
    redirect(ADMIN_URL . 'login.php');
}

// Refresh waktu login
$_SESSION['login_at'] = time();

$halaman = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($halaman) ?> — Admin <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed: 72px;
            --primary: #0D6EFD;
            --dark: #0A2540;
            --dark-hover: #0d2f52;
        }
        * { box-sizing: border-box; }
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            margin: 0;
            transition: padding-left 0.3s ease;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.3s ease, transform 0.3s ease;
            z-index: 1040;
            padding-bottom: 20px;
        }
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Brand */
        .sidebar .brand {
            padding: 18px 20px;
            font-size: 16px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            position: sticky;
            top: 0;
            background: var(--dark);
            z-index: 2;
            white-space: nowrap;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .sidebar .brand i {
            color: #FFC107;
            font-size: 26px;
            flex-shrink: 0;
            min-width: 26px;
            text-align: center;
        }
        .sidebar .brand .brand-text {
            transition: opacity 0.2s ease;
        }

        /* Menu Title */
        .sidebar .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            padding: 18px 20px 8px;
            color: rgba(255,255,255,0.4);
            letter-spacing: 1.2px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Menu Link */
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
            border-left: 3px solid transparent;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar a:hover {
            background: var(--dark-hover);
            color: #fff;
            border-left-color: #FFC107;
        }
        .sidebar a.active {
            background: rgba(13, 110, 253, 0.25);
            color: #fff;
            border-left-color: #FFC107;
            font-weight: 600;
        }
        .sidebar a i {
            font-size: 18px;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
            min-width: 22px;
        }
        .sidebar a .menu-text {
            transition: opacity 0.2s ease;
        }
        .sidebar a .badge-notif {
            margin-left: auto;
            background: #dc3545;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        /* ============ COLLAPSED STATE ============ */
        body.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed);
        }
        body.sidebar-collapsed .sidebar .brand-text,
        body.sidebar-collapsed .sidebar .menu-title,
        body.sidebar-collapsed .sidebar .menu-text,
        body.sidebar-collapsed .sidebar .badge-notif {
            opacity: 0;
            width: 0;
            overflow: hidden;
            pointer-events: none;
        }
        body.sidebar-collapsed .sidebar .menu-title {
            padding: 10px 0;
            height: 0;
        }
        body.sidebar-collapsed .sidebar a {
            padding: 13px 0;
            justify-content: center;
            gap: 0;
        }
        body.sidebar-collapsed .sidebar a i {
            font-size: 20px;
        }
        body.sidebar-collapsed .sidebar .brand {
            padding: 18px 0;
            justify-content: center;
            gap: 0;
        }

        /* Tooltip saat collapsed */
        body.sidebar-collapsed .sidebar a {
            position: relative;
        }
        body.sidebar-collapsed .sidebar a::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%) translateX(10px);
            background: #1a1a1a;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.2s ease;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        body.sidebar-collapsed .sidebar a:hover::after {
            opacity: 1;
            transform: translateY(-50%) translateX(5px);
        }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        body.sidebar-collapsed .main-content {
            margin-left: var(--sidebar-collapsed);
        }

        /* ============ TOPBAR ============ */
        .topbar {
            background: #fff;
            padding: 15px 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s;
        }
        .topbar .page-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* ============ TOGGLE BUTTON ============ */
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark);
            padding: 4px 8px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-toggle:hover {
            background: rgba(13, 110, 253, 0.1);
            color: var(--primary);
        }

        /* ============ CONTENT ============ */
        .content-area {
            padding: 25px;
        }

        /* ============ CARDS ============ */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        .stat-card .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .stat-card p {
            margin: 0;
            color: #6c757d;
            font-size: 13px;
        }

        /* ============ TABLE ============ */
        .table thead th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid #e9ecef;
        }
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.04);
        }

        /* ============ FORM ============ */
        .form-label {
            font-size: 14px;
            color: #495057;
            font-weight: 600;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #0D6EFD;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        /* ============ BREADCRUMB ============ */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        .breadcrumb a {
            color: #0D6EFD;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* ============ BADGE ============ */
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }

        /* ============ OVERLAY (MOBILE) ============ */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
        }
        .overlay.show {
            display: block;
        }

        /* ============ TIMELINE ============ */
        .timeline {
            position: relative;
            padding-left: 10px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 25px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline > div {
            position: relative;
        }

        /* ============ RATING STAR ============ */
        .rating-star {
            transition: all 0.2s;
        }
        .rating-star:hover {
            transform: scale(1.15);
        }

        /* ============ EDITOR ============ */
        #editor:focus {
            outline: none;
            border-color: #0D6EFD;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            /* Di mobile, collapsed state tidak berlaku */
            body.sidebar-collapsed .sidebar .brand-text,
            body.sidebar-collapsed .sidebar .menu-title,
            body.sidebar-collapsed .sidebar .menu-text,
            body.sidebar-collapsed .sidebar .badge-notif {
                opacity: 1;
                width: auto;
                pointer-events: auto;
            }
            body.sidebar-collapsed .sidebar a {
                padding: 11px 20px;
                justify-content: flex-start;
                gap: 12px;
            }
            body.sidebar-collapsed .sidebar a::after {
                display: none;
            }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="main-content">
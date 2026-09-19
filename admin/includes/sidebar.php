<?php
/**
 * Sidebar Menu Admin — Collapsible
 * 
 * @var mysqli $koneksi
 */

// Hitung notifikasi pesanan baru
$notif_baru = 0;
$q_notif = $koneksi->query("SELECT COUNT(*) as jml FROM pesanan WHERE status='baru'");
if ($q_notif) {
    $notif_baru = $q_notif->fetch_assoc()['jml'];
}

// Hitung pesan kontak belum dibaca
$notif_pesan = 0;
$q_pesan = $koneksi->query("SELECT COUNT(*) as jml FROM pesan_kontak WHERE status='belum'");
if ($q_pesan) {
    $notif_pesan = $q_pesan->fetch_assoc()['jml'];
}

$halaman_aktif = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar" id="sidebar">

    <!-- Brand / Logo -->
    <div class="brand">
        <i class="bi bi-code-slash"></i>
        <span class="brand-text">Admin Panel</span>
    </div>

    <!-- Menu Utama -->
    <div class="menu-title">Menu Utama</div>

    <a href="<?= ADMIN_URL ?>dashboard.php" 
       data-title="Dashboard"
       class="<?= $halaman_aktif == 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> 
        <span class="menu-text">Dashboard</span>
    </a>

    <a href="<?= ADMIN_URL ?>profil.php" 
       data-title="Profil Saya"
       class="<?= $halaman_aktif == 'profil' ? 'active' : '' ?>">
        <i class="bi bi-person-gear"></i> 
        <span class="menu-text">Profil Saya</span>
    </a>

    <!-- Konten Website -->
    <div class="menu-title">Konten Website</div>

    <a href="<?= ADMIN_URL ?>layanan.php" 
       data-title="Layanan"
       class="<?= in_array($halaman_aktif, ['layanan', 'layanan_form']) ? 'active' : '' ?>">
        <i class="bi bi-grid"></i> 
        <span class="menu-text">Layanan</span>
    </a>

    <a href="<?= ADMIN_URL ?>portfolio.php" 
       data-title="Portfolio"
       class="<?= in_array($halaman_aktif, ['portfolio', 'portfolio_form']) ? 'active' : '' ?>">
        <i class="bi bi-images"></i> 
        <span class="menu-text">Portfolio</span>
    </a>

    <a href="<?= ADMIN_URL ?>testimoni.php" 
       data-title="Testimoni"
       class="<?= in_array($halaman_aktif, ['testimoni', 'testimoni_form']) ? 'active' : '' ?>">
        <i class="bi bi-chat-quote"></i> 
        <span class="menu-text">Testimoni</span>
    </a>

    <a href="<?= ADMIN_URL ?>artikel.php" 
       data-title="Artikel"
       class="<?= in_array($halaman_aktif, ['artikel', 'artikel_form']) ? 'active' : '' ?>">
        <i class="bi bi-journal-text"></i> 
        <span class="menu-text">Artikel</span>
    </a>

    <!-- Pesanan & Pesan -->
    <div class="menu-title">Pesanan & Pesan</div>

    <a href="<?= ADMIN_URL ?>pesanan.php" 
       data-title="Pesanan<?= $notif_baru > 0 ? ' (' . $notif_baru . ' baru)' : '' ?>"
       class="<?= in_array($halaman_aktif, ['pesanan', 'pesanan_detail']) ? 'active' : '' ?>">
        <i class="bi bi-cart-check"></i> 
        <span class="menu-text">Pesanan</span>
        <?php if ($notif_baru > 0): ?>
            <span class="badge-notif"><?= $notif_baru ?></span>
        <?php endif; ?>
    </a>

    <a href="<?= ADMIN_URL ?>pesan_kontak.php" 
       data-title="Pesan Kontak<?= $notif_pesan > 0 ? ' (' . $notif_pesan . ' belum dibaca)' : '' ?>"
       class="<?= in_array($halaman_aktif, ['pesan_kontak', 'pesan_kontak_detail']) ? 'active' : '' ?>">
        <i class="bi bi-envelope"></i> 
        <span class="menu-text">Pesan Kontak</span>
        <?php if ($notif_pesan > 0): ?>
            <span class="badge-notif"><?= $notif_pesan ?></span>
        <?php endif; ?>
    </a>

    <!-- Pengaturan -->
    <div class="menu-title">Pengaturan</div>

    <a href="<?= ADMIN_URL ?>pengaturan.php" 
       data-title="Pengaturan Website"
       class="<?= $halaman_aktif == 'pengaturan' ? 'active' : '' ?>">
        <i class="bi bi-gear"></i> 
        <span class="menu-text">Pengaturan Website</span>
    </a>

    <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super'): ?>
        <a href="<?= ADMIN_URL ?>admin.php" 
           data-title="Kelola Admin"
           class="<?= in_array($halaman_aktif, ['admin', 'admin_form']) ? 'active' : '' ?>">
            <i class="bi bi-people"></i> 
            <span class="menu-text">Kelola Admin</span>
        </a>
    <?php endif; ?>

    <!-- Lainnya -->
    <div class="menu-title">Lainnya</div>

    <a href="<?= BASE_URL ?>" target="_blank" data-title="Lihat Website">
        <i class="bi bi-box-arrow-up-right"></i> 
        <span class="menu-text">Lihat Website</span>
    </a>

    <a href="<?= ADMIN_URL ?>logout.php" 
       data-title="Logout"
       onclick="return confirm('Yakin logout dari panel admin?')">
        <i class="bi bi-box-arrow-right"></i> 
        <span class="menu-text">Logout</span>
    </a>

</div>

<div class="overlay" id="overlay"></div>
<?php
require_once '../config/konfigurasi.php';
require_once '../config/koneksi.php';
require_once '../config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

$page_title = 'Tentang Kami — ' . SITE_NAME;
$page_desc  = 'Kenali lebih dekat tim dan visi kami dalam membantu transformasi digital bisnis Anda.';

require_once '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Tentang Kami</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                <li class="breadcrumb-item active">Tentang</li>
            </ol>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="subtitle text-primary fw-bold small text-uppercase">Tentang Kami</span>
                <h2 class="mb-3">Partner Digital Terpercaya untuk Bisnis Anda</h2>
                <p class="text-muted" style="line-height: 1.8;">
                    <strong><?= htmlspecialchars($pengaturan['nama_situs']) ?></strong> adalah tim developer profesional 
                    yang berfokus pada pembuatan website, aplikasi mobile, dan solusi digital lainnya. 
                    Kami telah membantu ratusan klien dari berbagai skala bisnis — mulai dari UMKM 
                    hingga perusahaan enterprise.
                </p>
                <p class="text-muted" style="line-height: 1.8;">
                    Dengan pengalaman lebih dari 5 tahun, kami berkomitmen memberikan hasil terbaik, 
                    tepat waktu, dan sesuai budget Anda.
                </p>
                <a href="<?= BASE_URL ?>kontak" class="btn btn-primary mt-3">
                    <i class="bi bi-chat-dots"></i> Hubungi Kami
                </a>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800" 
                     alt="Tim Kami" class="img-fluid rounded shadow">
            </div>
        </div>

        <!-- Visi Misi -->
        <div class="row g-4 mt-5">
            <div class="col-md-6">
                <div class="card-service h-100">
                    <div class="icon"><i class="bi bi-eye"></i></div>
                    <h5>Visi</h5>
                    <p>Menjadi partner digital terdepan yang membantu bisnis Indonesia bertransformasi 
                       dan bersaing di era digital.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-service h-100">
                    <div class="icon"><i class="bi bi-bullseye"></i></div>
                    <h5>Misi</h5>
                    <ul class="small ps-3 mb-0">
                        <li>Memberikan solusi digital berkualitas tinggi</li>
                        <li>Pelayanan ramah dan responsif 24/7</li>
                        <li>Harga terjangkau dengan hasil maksimal</li>
                        <li>Mendukung pertumbuhan UMKM Indonesia</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Nilai -->
        <div class="mt-5">
            <div class="section-title">
                <span class="subtitle">Nilai Kami</span>
                <h2>Yang Membuat Kami Berbeda</h2>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="icon mx-auto" style="width: 70px; height: 70px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h6 class="mt-3">Integritas</h6>
                    <p class="small text-muted">Jujur & transparan dalam setiap proyek</p>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="icon mx-auto" style="width: 70px; height: 70px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h6 class="mt-3">Cepat</h6>
                    <p class="small text-muted">Responsif & tepat waktu</p>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="icon mx-auto" style="width: 70px; height: 70px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <h6 class="mt-3">Kualitas</h6>
                    <p class="small text-muted">Hasil terbaik tanpa kompromi</p>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="icon mx-auto" style="width: 70px; height: 70px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h6 class="mt-3">Kolaborasi</h6>
                    <p class="small text-muted">Kerja sama sebagai partner, bukan vendor</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
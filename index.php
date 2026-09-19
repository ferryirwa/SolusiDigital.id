<?php
require_once 'config/konfigurasi.php';
require_once 'config/koneksi.php';
require_once 'config/fungsi.php';

/** @var array $pengaturan */
/** @var mysqli $koneksi */

// Meta
$page_title = SITE_NAME . ' — ' . SITE_DESC;
$page_desc  = 'Jasa pembuatan website profesional, aplikasi Android, hosting, dan layanan IT lainnya. Harga terjangkau, hasil berkualitas.';

// Ambil data
$layanan_unggulan = $koneksi->query("SELECT * FROM layanan WHERE status='aktif' ORDER BY urutan ASC LIMIT 6");
$portfolio_terbaru = $koneksi->query("SELECT * FROM portfolio ORDER BY created_at DESC LIMIT 6");
$testimoni_tampil = $koneksi->query("SELECT * FROM testimoni WHERE status='tampil' ORDER BY created_at DESC LIMIT 6");
$artikel_terbaru = $koneksi->query("SELECT * FROM artikel WHERE status='publish' ORDER BY created_at DESC LIMIT 3");

require_once 'includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1>Jasa Pembuatan Website & Aplikasi Profesional</h1>
                <p class="lead">
                    Wujudkan ide digital Anda bersama kami. Website modern, aplikasi Android, 
                    hosting cepat, dan solusi IT terpercaya dengan harga terjangkau.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= BASE_URL ?>pesan" class="btn btn-accent">
                        <i class="bi bi-cart-check"></i> Pesan Sekarang
                    </a>
                    <a href="<?= BASE_URL ?>portfolio" class="btn btn-outline-light">
                        <i class="bi bi-images"></i> Lihat Portfolio
                    </a>
                </div>
                
                <div class="row mt-5 g-4">
                    <div class="col-4">
                        <h3 class="text-warning mb-0">150+</h3>
                        <small>Proyek Selesai</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-warning mb-0">100+</h3>
                        <small>Klien Puas</small>
                    </div>
                    <div class="col-4">
                        <h3 class="text-warning mb-0">5+</h3>
                        <small>Tahun Pengalaman</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-illustration">
                    <i class="bi bi-code-square"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ LAYANAN ============ -->
<section class="section" id="layanan">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Layanan Kami</span>
            <h2>Solusi Digital untuk Bisnis Anda</h2>
            <p>Berbagai layanan profesional untuk mendukung transformasi digital bisnis Anda</p>
        </div>
        
        <div class="row g-4">
            <?php while ($l = $layanan_unggulan->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card-service">
                        <div class="icon">
                            <i class="bi <?= htmlspecialchars($l['icon']) ?>"></i>
                        </div>
                        <h5><?= htmlspecialchars($l['nama']) ?></h5>
                        <p><?= potong_teks($l['deskripsi_singkat'], 100) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="price">Mulai <?= rupiah($l['harga_mulai']) ?></div>
                            <a href="<?= BASE_URL ?>layanan/<?= $l['slug'] ?>" class="btn btn-outline-primary btn-sm">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>layanan" class="btn btn-primary">
                Lihat Semua Layanan <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============ KENAPA PILIH KAMI ============ -->
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Keunggulan</span>
            <h2>Kenapa Pilih Kami?</h2>
            <p>Kami berkomitmen memberikan layanan terbaik untuk kesuksesan digital Anda</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="icon mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                    <h5 class="mt-3">Pengerjaan Cepat</h5>
                    <p class="small text-muted">Proyek selesai tepat waktu tanpa mengorbankan kualitas</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="icon mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="mt-3">Aman & Terpercaya</h5>
                    <p class="small text-muted">Keamanan data dan sistem jadi prioritas utama kami</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="icon mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5 class="mt-3">Support 24/7</h5>
                    <p class="small text-muted">Tim support siap membantu kapan pun Anda butuh</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="icon mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #0D6EFD, #0A2540); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 32px;">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5 class="mt-3">Garansi Kepuasan</h5>
                    <p class="small text-muted">Revisi hingga Anda puas dengan hasilnya</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ PORTFOLIO ============ -->
<?php if ($portfolio_terbaru->num_rows > 0): ?>
<section class="section">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Portfolio</span>
            <h2>Proyek Terbaru Kami</h2>
            <p>Beberapa proyek yang telah kami kerjakan untuk klien dari berbagai industri</p>
        </div>
        
        <div class="row g-4">
            <?php while ($p = $portfolio_terbaru->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card-portfolio">
                        <div class="thumb">
                            <?php if (!empty($p['gambar']) && file_exists(PORTFOLIO_PATH . $p['gambar'])): ?>
                                <img src="<?= UPLOADS_URL ?>portfolio/<?= htmlspecialchars($p['gambar']) ?>" 
                                     alt="<?= htmlspecialchars($p['judul']) ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                    <i class="bi bi-image" style="font-size: 60px; opacity: 0.3;"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge-cat"><?= ucfirst(htmlspecialchars($p['kategori'])) ?></span>
                        </div>
                        <div class="body">
                            <h5><?= htmlspecialchars($p['judul']) ?></h5>
                            <?php if (!empty($p['klien'])): ?>
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($p['klien']) ?>
                                </small>
                            <?php endif; ?>
                            <p class="small text-muted mb-2">
                                <?= potong_teks($p['deskripsi'], 80) ?>
                            </p>
                            <?php if (!empty($p['teknologi'])): ?>
                                <div class="mb-3">
                                    <?php foreach (array_slice(explode(',', $p['teknologi']), 0, 3) as $tech): ?>
                                        <span class="tech-badge"><?= htmlspecialchars(trim($tech)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>portfolio/<?= $p['slug'] ?>" class="btn btn-outline-primary btn-sm w-100">
                                Lihat Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>portfolio" class="btn btn-primary">
                Lihat Semua Portfolio <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ TESTIMONI ============ -->
<?php if ($testimoni_tampil->num_rows > 0): ?>
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Testimoni</span>
            <h2>Apa Kata Klien Kami</h2>
            <p>Kepuasan klien adalah prioritas utama kami</p>
        </div>
        
        <div class="row g-4">
            <?php while ($t = $testimoni_tampil->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card-testimoni">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $t['rating'] ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="quote">"<?= htmlspecialchars($t['isi']) ?>"</p>
                        <div class="author">
                            <div class="avatar">
                                <?php if (!empty($t['foto']) && file_exists(PORTFOLIO_PATH . 'testimoni/' . $t['foto'])): ?>
                                    <img src="<?= UPLOADS_URL ?>portfolio/testimoni/<?= htmlspecialchars($t['foto']) ?>" 
                                         alt="<?= htmlspecialchars($t['nama']) ?>">
                                <?php else: ?>
                                    <?= strtoupper(substr($t['nama'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($t['nama']) ?></div>
                                <small class="text-muted">
                                    <?= htmlspecialchars($t['jabatan']) ?>
                                    <?php if (!empty($t['perusahaan'])): ?>
                                        · <?= htmlspecialchars($t['perusahaan']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ ARTIKEL ============ -->
<?php if ($artikel_terbaru->num_rows > 0): ?>
<section class="section">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Blog</span>
            <h2>Artikel Terbaru</h2>
            <p>Tips, tutorial, dan wawasan seputar dunia digital</p>
        </div>
        
        <div class="row g-4">
            <?php while ($a = $artikel_terbaru->fetch_assoc()): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card-portfolio">
                        <div class="thumb">
                            <?php if (!empty($a['gambar']) && file_exists(ARTIKEL_PATH . $a['gambar'])): ?>
                                <img src="<?= UPLOADS_URL ?>artikel/<?= htmlspecialchars($a['gambar']) ?>" 
                                     alt="<?= htmlspecialchars($a['judul']) ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                    <i class="bi bi-journal-text" style="font-size: 60px; opacity: 0.3;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <?= tanggal_indo($a['created_at']) ?>
                            </small>
                            <h5 class="mt-2"><?= htmlspecialchars($a['judul']) ?></h5>
                            <p class="small text-muted">
                                <?= potong_teks(strip_tags($a['konten']), 100) ?>
                            </p>
                            <a href="<?= BASE_URL ?>artikel/<?= $a['slug'] ?>" class="btn btn-outline-primary btn-sm">
                                Baca Selengkapnya <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?= BASE_URL ?>artikel" class="btn btn-primary">
                Lihat Semua Artikel <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ CTA ============ -->
<section class="container">
    <div class="cta-section text-center">
        <div class="container">
            <h2>Siap Memulai Proyek Digital Anda?</h2>
            <p class="lead mb-4">Konsultasi gratis! Ceritakan kebutuhan Anda, kami akan berikan solusi terbaik.</p>
            <a href="<?= BASE_URL ?>pesan" class="btn btn-accent btn-lg">
                <i class="bi bi-cart-check"></i> Pesan Sekarang
            </a>
            <a href="<?= link_wa($pengaturan['whatsapp'], 'Halo, saya ingin konsultasi gratis.') ?>" 
               target="_blank" class="btn btn-outline-light btn-lg ms-2">
                <i class="bi bi-whatsapp"></i> Chat WhatsApp
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
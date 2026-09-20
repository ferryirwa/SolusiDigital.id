<?php
/**
 * Footer Template — Frontend
 * 
 * @var array $pengaturan Data pengaturan website
 * @var mysqli $koneksi Koneksi database
 */

// Guard: pastikan $pengaturan ada dan array
if (!isset($pengaturan) || !is_array($pengaturan)) {
    $pengaturan = [
        'nama_situs' => defined('SITE_NAME') ? SITE_NAME : 'JasaProgrammer.id',
        'deskripsi'  => defined('SITE_DESC') ? SITE_DESC : '',
        'whatsapp'   => '6281234567890',
        'email'      => 'info@website.com',
        'alamat'     => 'Jakarta, Indonesia',
        'facebook'   => '',
        'instagram'  => '',
    ];
}
?>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5>
                    <i class="bi bi-code-slash text-primary"></i> 
                    <?= htmlspecialchars($pengaturan['nama_situs']) ?>
                </h5>
                <p class="small">
                    <?= htmlspecialchars($pengaturan['deskripsi']) ?>
                </p>
                <div class="social-links mt-3">
                    <?php if (!empty($pengaturan['facebook'])): ?>
                        <a href="<?= htmlspecialchars($pengaturan['facebook']) ?>" target="_blank">
                            <i class="bi bi-facebook"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($pengaturan['instagram'])): ?>
                        <a href="<?= htmlspecialchars($pengaturan['instagram']) ?>" target="_blank">
                            <i class="bi bi-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <a href="<?= link_wa($pengaturan['whatsapp']) ?>" target="_blank">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="mailto:<?= htmlspecialchars($pengaturan['email']) ?>">
                        <i class="bi bi-envelope"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h5>Menu</h5>
                <a href="<?= BASE_URL ?>">Home</a>
                <a href="<?= BASE_URL ?>layanan">Layanan</a>
                <a href="<?= BASE_URL ?>portfolio">Portfolio</a>
                <a href="<?= BASE_URL ?>artikel">Artikel</a>
                <a href="<?= BASE_URL ?>tentang">Tentang</a>
                <a href="<?= BASE_URL ?>kontak">Kontak</a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5>Layanan</h5>
                <?php
                $layanan_footer = $koneksi->query("SELECT nama, slug FROM layanan WHERE status='aktif' ORDER BY urutan ASC LIMIT 5");
                while ($lf = $layanan_footer->fetch_assoc()):
                ?>
                    <a href="<?= BASE_URL ?>layanan/<?= $lf['slug'] ?>">
                        <?= htmlspecialchars($lf['nama']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5>Kontak</h5>
                <p class="small mb-2">
                    <i class="bi bi-geo-alt-fill text-primary"></i> 
                    <?= htmlspecialchars($pengaturan['alamat']) ?>
                </p>
                <p class="small mb-2">
                    <i class="bi bi-envelope-fill text-primary"></i> 
                    <a href="mailto:<?= htmlspecialchars($pengaturan['email']) ?>" class="d-inline">
                        <?= htmlspecialchars($pengaturan['email']) ?>
                    </a>
                </p>
                <p class="small mb-2">
                    <i class="bi bi-whatsapp text-primary"></i> 
                    <a href="<?= link_wa($pengaturan['whatsapp']) ?>" target="_blank" class="d-inline">
                        <?= htmlspecialchars($pengaturan['whatsapp']) ?>
                    </a>
                </p>
            </div>
        </div>
        
     <div class="copyright">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($pengaturan['nama_situs']) ?>. 
    All rights reserved. | 
    <a href="<?= ADMIN_URL ?>login.php" class="d-inline text-muted small" title="Login Admin">
        <i class="bi bi-shield-lock"></i> Admin
    </a>
</div>
    </div>
</footer>

<!-- WhatsApp Floating Button -->
<a href="<?= link_wa($pengaturan['whatsapp'], 'Halo, saya ingin bertanya tentang layanan Anda.') ?>" 
   target="_blank" class="wa-float" title="Chat WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= ASSETS_URL ?>js/main.js"></script>
</body>
</html>
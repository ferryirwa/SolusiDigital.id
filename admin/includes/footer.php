        </div> <!-- end content-area -->
    </div> <!-- end main-content -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ============================================
    // ADMIN FOOTER SCRIPT
    // ============================================
    (function() {
        'use strict';

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        // ---------- Load state dari localStorage ----------
        const SIDEBAR_KEY = 'admin_sidebar_collapsed';
        if (localStorage.getItem(SIDEBAR_KEY) === 'true' && window.innerWidth > 991) {
            document.body.classList.add('sidebar-collapsed');
        }

        // ---------- Toggle Sidebar ----------
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Di mobile → pakai mode "show" (slide dari kiri)
                if (window.innerWidth <= 991) {
                    sidebar.classList.toggle('show');
                    if (overlay) overlay.classList.toggle('show');
                } 
                // Di desktop → toggle collapse (kecilkan)
                else {
                    document.body.classList.toggle('sidebar-collapsed');

                    // Simpan state ke localStorage
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                    localStorage.setItem(SIDEBAR_KEY, isCollapsed ? 'true' : 'false');

                    // Update icon toggle
                    updateToggleIcon(isCollapsed);
                }
            });
        }

        // ---------- Update icon toggle ----------
        function updateToggleIcon(isCollapsed) {
            if (!toggleBtn) return;
            const icon = toggleBtn.querySelector('i');
            if (!icon) return;

            if (isCollapsed) {
                icon.className = 'bi bi-list';
            } else {
                icon.className = 'bi bi-list';
            }
        }

        // ---------- Overlay click (mobile) ----------
        if (overlay && sidebar) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // ---------- Auto-close sidebar di mobile saat klik menu ----------
        if (sidebar) {
            sidebar.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        sidebar.classList.remove('show');
                        if (overlay) overlay.classList.remove('show');
                    }
                });
            });
        }

        // ---------- Handle resize window ----------
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Kalau pindah ke mobile, hapus state collapsed
                if (window.innerWidth <= 991) {
                    document.body.classList.remove('sidebar-collapsed');
                    if (sidebar) sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                } else {
                    // Kalau pindah ke desktop, restore state collapsed
                    const saved = localStorage.getItem(SIDEBAR_KEY);
                    if (saved === 'true') {
                        document.body.classList.add('sidebar-collapsed');
                    }
                }
            }, 200);
        });

        // ---------- Auto-hide Alert ----------
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            if (alerts.length > 0 && typeof bootstrap !== 'undefined') {
                alerts.forEach(function(el) {
                    try {
                        const alert = bootstrap.Alert.getOrCreateInstance(el);
                        alert.close();
                    } catch (err) {}
                });
            }
        }, 4000);

    })();
    </script>

</body>
</html>
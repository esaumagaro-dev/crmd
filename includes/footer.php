<?php if (isset($is_logged) && $is_logged): ?>
            </div><!-- /.main-content -->
        </main><!-- /.app-main -->
    </div><!-- /.app-layout -->
<?php endif; ?>

    <!-- Footer -->
    <footer class="app-footer mt-5 py-4">
        <div class="container-fluid text-center">
            <p class="mb-1">
                <i class="fas fa-shield-halved"></i> 2026 Cybersecurity Risk Management Dashboard
            </p>
            <p class="mb-1 small">
                Arusha Technical College | Developed by Vaileth Aloyce Mkaakaa
            </p>
            <p class="text-muted small">
                Final Year Project (Ordinary Diploma in Cybersecurity and Digital Forensics)
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/dashboard.js"></script>
    
    <script>
    // Sidebar toggle for mobile
    function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('show');
    }

    // Notifications toggle
    function toggleNotifications() {
        const panel = document.getElementById('notificationsPanel');
        if (panel) panel.classList.toggle('show');
    }

    // Close notifications when clicking outside
    document.addEventListener('click', function(e) {
        const panel = document.getElementById('notificationsPanel');
        if (panel && !panel.contains(e.target) && !e.target.closest('[onclick*="toggleNotifications"]')) {
            panel.classList.remove('show');
        }
    });

    // Show coming soon message
    function showComingSoon(feature) {
        alert(feature + ' module coming soon!');
    }

    // Global search
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = 'reports.php?search=' + encodeURIComponent(query);
                }
            }
        });
    }

    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
    </script>
</body>
</html>

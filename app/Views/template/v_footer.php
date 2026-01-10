</div>
    </main>

    <footer class="app-footer">
        &copy; <?= date('Y'); ?> <strong>WebGIS Kabupaten Ngawi</strong>. Dikembangkan dengan <i class="fas fa-heart text-danger mx-1"></i> dan CodeIgniter 4.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const navToggle = document.getElementById('navToggle');
        const navPanel = document.getElementById('navPanel');
        const closeNav = document.getElementById('closeNav');
        const backdrop = document.getElementById('navBackdrop');

        function toggleMenu() {
            navPanel.classList.toggle('open');
            backdrop.classList.toggle('active');
        }

        // Event Listeners
        navToggle.addEventListener('click', toggleMenu);
        closeNav.addEventListener('click', toggleMenu);
        
        // Klik di luar menu (backdrop) untuk menutup
        backdrop.addEventListener('click', toggleMenu);

        // Menutup menu jika tombol ESC ditekan
        document.addEventListener('keydown', function(event){
            if(event.key === "Escape" && navPanel.classList.contains('open')){
                toggleMenu();
            }
        });
    </script>
</body>
</html>
<?php
// views/partials/footer.php
?>
        </div> 
        
        <footer class="mt-auto py-4 text-center text-muted small">
            &copy; <?= date('Y') ?> Service Center App
        </footer>

    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        const wrapper = document.getElementById('wrapper');

        // Logic Toggle Sidebar
        if (sidebarCollapse && wrapper) {
            sidebarCollapse.addEventListener('click', function() {
                wrapper.classList.toggle('toggled');
            });
        }
        
        // Opsional: Otomatis sembunyi di layar kecil saat loading
        if (window.innerWidth <= 768) {
             // Biarkan CSS yang mengatur (sudah default hidden di CSS header)
        }
    });
</script>

</body>
</html>
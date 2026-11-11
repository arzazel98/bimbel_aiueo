            </div>
        </main>

        <!-- Modern Web Footer -->
        <footer class="mt-auto py-3 px-4 bg-white border-top text-muted small">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; <?= date('Y') ?> <strong class="text-dark">Bimbel AIUEO</strong> &bull; Sistem Pembelajaran & Manajemen
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center gap-1">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></span>
                        <span class="text-secondary" style="font-size: 11px;">Server Aktif</span>
                    </span>
                    <span class="text-secondary" style="font-size: 11px;">v2.0 Web Edition</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('appSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            });

            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
        }

        // Auto format tables: make table responsive if wrapped in card
        document.querySelectorAll('table:not(.table-responsive table)').forEach(function(table) {
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    });
</script>
</body>
</html>

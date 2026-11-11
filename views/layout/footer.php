<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'];
?>
    </div>
    <div class="bottom-nav">
        <?php if($role == 'siswa'): ?>
            <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i><span>Beranda</span>
            </a>
            <a href="pendaftaran.php" class="<?= $current_page == 'pendaftaran.php' ? 'active' : '' ?>">
                <i class="fas fa-book"></i><span>Daftar</span>
            </a>
            <a href="jadwal.php" class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar"></i><span>Jadwal</span>
            </a>
            <a href="pembayaran.php" class="<?= $current_page == 'pembayaran.php' ? 'active' : '' ?>">
                <i class="fas fa-credit-card"></i><span>Bayar</span>
            </a>
            <a href="profil.php" class="<?= $current_page == 'profil.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i><span>Profil</span>
            </a>
        <?php elseif($role == 'guru'): ?>
            <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i><span>Beranda</span>
            </a>
            <a href="presensi_guru.php" class="<?= $current_page == 'presensi_guru.php' ? 'active' : '' ?>">
                <i class="fas fa-fingerprint"></i><span>Presensi</span>
            </a>
            <a href="presensi_siswa.php" class="<?= $current_page == 'presensi_siswa.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i><span>Siswa</span>
            </a>
            <a href="laporan_honor.php" class="<?= $current_page == 'laporan_honor.php' ? 'active' : '' ?>">
                <i class="fas fa-money-bill"></i><span>Honor</span>
            </a>
        <?php elseif($role == 'admin'): ?>
    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i><span>Beranda</span>
    </a>
    
    <a href="kelas.php" class="<?= $current_page == 'kelas.php' ? 'active' : '' ?>">
        <i class="fas fa-chalkboard"></i><span>Kelas</span>
    </a>
    
    <a href="jadwal.php" class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar"></i><span>Jadwal</span>
    </a>
    
    <a href="presensi_monitoring.php" class="<?= $current_page == 'presensi_monitoring.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i><span>Monitoring</span>
    </a>
    
    <a href="laporan.php" class="<?= $current_page == 'laporan.php' ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i><span>Laporan</span>
    </a>
<?php endif; ?>
      
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Active menu highlight
    document.querySelectorAll('.bottom-nav a').forEach(link => {
        if(link.href === window.location.href) {
            link.classList.add('active');
        }
    });
</script>
</body>
</html>
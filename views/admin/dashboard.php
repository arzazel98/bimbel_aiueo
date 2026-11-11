<?php
$page_title = 'Dashboard Admin';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['admin']);

$db = getDB();

// Get statistics
$stats = [];

// Total siswa
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'siswa'");
$stats['siswa'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total guru
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'guru'");
$stats['guru'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total kelas aktif
$stmt = $db->query("SELECT COUNT(*) as total FROM kelas WHERE status = 'aktif'");
$stats['kelas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Pendapatan bulan ini
$stmt = $db->query("SELECT SUM(jumlah) as total FROM pembayaran WHERE MONTH(tanggal_bayar) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_bayar) = YEAR(CURRENT_DATE()) AND status = 'valid'");
$stats['pendapatan'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Pendaftaran bulan ini
$stmt = $db->query("SELECT COUNT(*) as total FROM pendaftaran WHERE MONTH(tanggal_daftar) = MONTH(CURRENT_DATE()) AND status_pendaftaran = 'disetujui'");
$stats['pendaftaran_baru'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold">Halo, Admin <?= htmlspecialchars($_SESSION['user']) ?>!</h4>
    <p class="text-muted small">Selamat datang di dashboard Bimbel AIUEO</p>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Siswa -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Total Siswa</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h2 class="mb-0 fw-bold" style="font-size: 32px; line-height: 1;"><?= $stats['siswa'] ?></h2>
                    <i class="fas fa-users opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 2: Total Guru -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Total Guru</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h2 class="mb-0 fw-bold" style="font-size: 32px; line-height: 1;"><?= $stats['guru'] ?></h2>
                    <i class="fas fa-chalkboard-user opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 3: Total Kelas -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Total Kelas</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h2 class="mb-0 fw-bold" style="font-size: 32px; line-height: 1;"><?= $stats['kelas'] ?></h2>
                    <i class="fas fa-book-open opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 4: Pendapatan -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Pendapatan Bulan Ini</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h5 class="mb-0 fw-bold" style="font-size: 20px; line-height: 1.2;">Rp <?= number_format($stats['pendapatan'], 0, ',', '.') ?></h5>
                    <i class="fas fa-money-bill-wave opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Menu -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-bolt text-primary me-2"></i>Akses Cepat</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-sm-4 col-md-2">
                <a href="kelas.php" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3 bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-chalkboard fs-3 text-primary mb-2"></i>
                        <div class="small fw-bold text-dark">Data Kelas</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="jadwal.php" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3 bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-calendar-alt fs-3 text-success mb-2"></i>
                        <div class="small fw-bold text-dark">Jadwal Belajar</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="presensi_monitoring.php" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3 bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-fingerprint fs-3 text-warning mb-2"></i>
                        <div class="small fw-bold text-dark">Monitoring Presensi</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="pembayaran.php" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3 bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-credit-card fs-3 text-info mb-2"></i>
                        <div class="small fw-bold text-dark">Verifikasi Bayar</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="laporan.php" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3 bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-file-alt fs-3 text-danger mb-2"></i>
                        <div class="small fw-bold text-dark">Laporan & Rekap</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="gaji_guru.php" class="text-decoration-none">
                    <div class="text-center p-3 rounded-3 bg-light h-100 d-flex flex-column justify-content-center align-items-center">
                        <i class="fas fa-wallet fs-3 text-purple mb-2" style="color: #9b59b6;"></i>
                        <div class="small fw-bold text-dark">Honor & Gaji Guru</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Tambahan -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <i class="fas fa-user-plus text-primary fs-4 mb-1"></i>
                        <div class="fw-bold fs-5"><?= $stats['pendaftaran_baru'] ?></div>
                        <small class="text-muted" style="font-size: 10px;">Pendaftaran Baru</small>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="text-center flex-grow-1">
                        <i class="fas fa-calendar-week text-success fs-4 mb-1"></i>
                        <div class="fw-bold fs-6"><?= date('F Y') ?></div>
                        <small class="text-muted" style="font-size: 10px;">Periode Berjalan</small>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="text-center flex-grow-1">
                        <i class="fas fa-chalkboard-user text-warning fs-4 mb-1"></i>
                        <div class="fw-bold fs-5"><?= $stats['guru'] ?></div>
                        <small class="text-muted" style="font-size: 10px;">Guru Aktif</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
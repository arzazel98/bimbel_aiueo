<?php
$page_title = 'Laporan Honor';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['guru']);

$db = getDB();
$user_id = $_SESSION['user_id'];

// Get filter
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Get honor data from database
$honor = $db->prepare("SELECT * FROM honor_guru WHERE guru_id = :guru_id AND bulan = :bulan AND tahun = :tahun");
$honor->execute(['guru_id' => $user_id, 'bulan' => $bulan, 'tahun' => $tahun]);
$honor_data = $honor->fetch(PDO::FETCH_ASSOC);

// Get attendance detail for selected month
$presensi = $db->prepare("SELECT p.*, k.nama_kelas 
                          FROM presensi p 
                          LEFT JOIN kelas k ON p.kelas_id = k.id 
                          WHERE p.user_id = :guru_id AND p.jenis = 'guru' 
                          AND MONTH(p.tanggal) = :bulan AND YEAR(p.tanggal) = :tahun 
                          ORDER BY p.tanggal DESC");
$presensi->execute(['guru_id' => $user_id, 'bulan' => $bulan, 'tahun' => $tahun]);
$presensi_list = $presensi->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_hadir = 0;
$total_izin = 0;
$total_alpha = 0;
foreach ($presensi_list as $p) {
    if ($p['status'] == 'hadir') $total_hadir++;
    elseif ($p['status'] == 'izin') $total_izin++;
    elseif ($p['status'] == 'alpha') $total_alpha++;
}

// Calculate honor
$honor_per_hari = 50000;
$honor_dasar = $total_hadir * $honor_per_hari;
$bonus = 0;
if ($total_hadir >= 20) {
    $bonus = 100000;
} elseif ($total_hadir >= 15) {
    $bonus = 50000;
}
$total_honor = $honor_dasar + $bonus;

// Nama bulan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Laporan Honor Bulanan</h5>
    <p class="text-muted small">Lihat rekapitulasi honor mengajar Anda</p>
</div>

<!-- Penjelasan -->
<div class="card border-0 rounded-4 shadow-sm mb-3 bg-info bg-opacity-10">
    <div class="card-body p-3">
        <div class="d-flex gap-2">
            <i class="fas fa-info-circle text-info fs-5"></i>
            <small class="text-muted">Honor dihitung berdasarkan jumlah kehadiran (Hadir) dikali Rp 50.000 per hari. Bonus diberikan jika kehadiran mencapai target (minimal 15 hari).</small>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-2">
            <div class="col-6">
                <label class="form-label small fw-bold">Bulan</label>
                <select name="bulan" class="form-select rounded-3">
                    <?php foreach($nama_bulan as $key => $nama): ?>
                        <option value="<?= $key ?>" <?= $bulan == $key ? 'selected' : '' ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label small fw-bold">Tahun</label>
                <select name="tahun" class="form-select rounded-3">
                    <?php for($i=2023; $i<=2030; $i++): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary rounded-pill w-100">
                    <i class="fas fa-search"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan Honor -->
<div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
    <div class="card-body text-center text-white p-4">
        <i class="fas fa-money-bill-wave fs-1 mb-2"></i>
        <h2 class="fw-bold mb-0">Rp <?= number_format($total_honor, 0, ',', '.') ?></h2>
        <small>Total Honor <?= $nama_bulan[$bulan] ?> <?= $tahun ?></small>
    </div>
</div>

<!-- Detail Perhitungan -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-calculator text-primary me-2"></i>Rincian Perhitungan</h6>
    </div>
    <div class="card-body">
        <div class="row g-3 text-center mb-3">
            <div class="col-4">
                <div class="bg-success bg-opacity-10 rounded-4 p-2">
                    <i class="fas fa-check-circle text-success fs-4"></i>
                    <h3 class="fw-bold text-success mb-0"><?= $total_hadir ?></h3>
                    <small class="text-muted">Hadir</small>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-warning bg-opacity-10 rounded-4 p-2">
                    <i class="fas fa-clock text-warning fs-4"></i>
                    <h3 class="fw-bold text-warning mb-0"><?= $total_izin ?></h3>
                    <small class="text-muted">Izin</small>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-danger bg-opacity-10 rounded-4 p-2">
                    <i class="fas fa-times-circle text-danger fs-4"></i>
                    <h3 class="fw-bold text-danger mb-0"><?= $total_alpha ?></h3>
                    <small class="text-muted">Alpha</small>
                </div>
            </div>
        </div>
        
        <div class="border-top pt-3">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Honor Dasar (<?= $total_hadir ?> hari × Rp 50.000)</span>
                <span class="fw-bold">Rp <?= number_format($honor_dasar, 0, ',', '.') ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Bonus Kehadiran</span>
                <span class="fw-bold text-success">+ Rp <?= number_format($bonus, 0, ',', '.') ?></span>
            </div>
            <div class="d-flex justify-content-between pt-2 border-top fw-bold">
                <span>TOTAL HONOR</span>
                <span class="text-success">Rp <?= number_format($total_honor, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Detail Presensi Harian -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Detail Presensi Harian - <?= $nama_bulan[$bulan] ?> <?= $tahun ?></h6>
    </div>
    <div class="card-body">
        <?php if(empty($presensi_list)): ?>
            <div class="text-center py-4">
                <i class="fas fa-fingerprint fa-3x text-muted mb-2"></i>
                <p class="text-muted small mb-0">Belum ada data presensi untuk periode ini</p>
            </div>
        <?php else: ?>
            <?php foreach($presensi_list as $p): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= date('d F Y', strtotime($p['tanggal'])) ?></strong>
                        <div class="small text-muted">Kelas: <?= $p['nama_kelas'] ?? '-' ?></div>
                    </div>
                    <span class="badge <?= $p['status'] == 'hadir' ? 'bg-success' : ($p['status'] == 'izin' ? 'bg-warning' : 'bg-danger') ?> rounded-pill">
                        <?= ucfirst($p['status']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
            
            <div class="mt-3 pt-2 border-top bg-light rounded-3 p-2">
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Total Hadir: <?= $total_hadir ?> hari</span>
                    <span class="text-muted">Total Izin: <?= $total_izin ?> hari</span>
                    <span class="text-muted">Total Alpha: <?= $total_alpha ?> hari</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tombol Cetak -->
<div class="mt-3">
    <button onclick="window.print()" class="btn btn-outline-primary rounded-pill w-100">
        <i class="fas fa-print"></i> Cetak Laporan
    </button>
</div>

<style>
@media print {
    .bottom-nav, .btn-logout, .app-header .btn-logout, form, .btn-outline-primary, .bg-info.bg-opacity-10 {
        display: none !important;
    }
    .mobile-container {
        max-width: 100%;
        margin: 0;
        padding: 0;
        border-radius: 0;
        box-shadow: none;
    }
    .card {
        break-inside: avoid;
    }
}
</style>

<?php require_once '../layout/footer.php'; ?>
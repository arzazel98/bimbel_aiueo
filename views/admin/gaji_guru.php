<?php
$page_title = 'Gaji Guru';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['admin']);

$db = getDB();

// Ambil daftar guru
$guru_list = $db->query("SELECT id, nama_lengkap, username, email FROM users WHERE role = 'guru' AND status = 'aktif' ORDER BY nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);

// Filter dari form
$selected_guru = $_GET['guru_id'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Data gaji
$gaji_data = null;
$detail_presensi = [];

if ($selected_guru) {
    // Ambil data honor dari tabel honor_guru
    $stmt = $db->prepare("SELECT * FROM honor_guru WHERE guru_id = :guru_id AND bulan = :bulan AND tahun = :tahun");
    $stmt->execute(['guru_id' => $selected_guru, 'bulan' => $bulan, 'tahun' => $tahun]);
    $gaji_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ambil detail presensi guru per hari
    $presensi = $db->prepare("SELECT p.*, k.nama_kelas 
                              FROM presensi p 
                              LEFT JOIN kelas k ON p.kelas_id = k.id 
                              WHERE p.user_id = :guru_id AND p.jenis = 'guru' 
                              AND MONTH(p.tanggal) = :bulan AND YEAR(p.tanggal) = :tahun 
                              ORDER BY p.tanggal DESC");
    $presensi->execute(['guru_id' => $selected_guru, 'bulan' => $bulan, 'tahun' => $tahun]);
    $detail_presensi = $presensi->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung statistik
    $total_hadir = 0;
    $total_izin = 0;
    $total_alpha = 0;
    foreach ($detail_presensi as $p) {
        if ($p['status'] == 'hadir') $total_hadir++;
        elseif ($p['status'] == 'izin') $total_izin++;
        elseif ($p['status'] == 'alpha') $total_alpha++;
    }
}

// Nama bulan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

require_once '../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="fas fa-money-bill-wave text-primary me-2"></i>Gaji / Honor Guru</h5>
</div>

<!-- Penjelasan Singkat -->
<div class="card border-0 rounded-4 shadow-sm mb-3 bg-info bg-opacity-10">
    <div class="card-body p-3">
        <div class="d-flex gap-2">
            <i class="fas fa-info-circle text-info fs-5"></i>
            <small class="text-muted">Halaman ini digunakan untuk melihat dan mengelola honor/gaji guru berdasarkan kehadiran mereka. Honor dihitung dari jumlah kehadiran guru dikali honor per hari (Rp 50.000).</small>
        </div>
    </div>
</div>

<!-- Form Filter -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="mb-3">
                <label class="form-label small fw-bold">Pilih Guru</label>
                <select name="guru_id" class="form-select rounded-3" required>
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach($guru_list as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $selected_guru == $g['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nama_lengkap']) ?> (<?= $g['username'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row g-2 mb-3">
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
            </div>
            
            <button type="submit" class="btn btn-primary rounded-pill w-100">
                <i class="fas fa-search"></i> Tampilkan Gaji
            </button>
        </form>
    </div>
</div>

<?php if($selected_guru && $gaji_data): ?>
    <!-- Ringkasan Gaji -->
    <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
        <div class="card-body text-center text-white p-4">
            <i class="fas fa-money-bill-wave fs-1 mb-2"></i>
            <h2 class="fw-bold mb-0">Rp <?= number_format($gaji_data['total_honor'], 0, ',', '.') ?></h2>
            <small>Total Honor <?= $nama_bulan[$bulan] ?> <?= $tahun ?></small>
        </div>
    </div>
    
    <!-- Detail Perhitungan Gaji -->
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-calculator text-primary me-2"></i>Rincian Perhitungan Honor</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-4 text-center">
                    <div class="bg-success bg-opacity-10 rounded-4 p-3">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                        <h3 class="fw-bold text-success mb-0"><?= $gaji_data['total_hadir'] ?? 0 ?></h3>
                        <small class="text-muted">Total Hadir</small>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="bg-info bg-opacity-10 rounded-4 p-3">
                        <i class="fas fa-coins text-info fs-4"></i>
                        <h5 class="fw-bold text-info mb-0">Rp <?= number_format($gaji_data['honor_per_hadir'] ?? 50000, 0, ',', '.') ?></h5>
                        <small class="text-muted">Honor/Hari</small>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="bg-primary bg-opacity-10 rounded-4 p-3">
                        <i class="fas fa-chart-line text-primary fs-4"></i>
                        <h5 class="fw-bold text-primary mb-0">Rp <?= number_format(($gaji_data['total_hadir'] ?? 0) * ($gaji_data['honor_per_hadir'] ?? 50000), 0, ',', '.') ?></h5>
                        <small class="text-muted">Honor Dasar</small>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Honor Dasar (<?= $gaji_data['total_hadir'] ?? 0 ?> hari × Rp <?= number_format($gaji_data['honor_per_hadir'] ?? 50000, 0, ',', '.') ?>)</span>
                    <span class="fw-bold">Rp <?= number_format(($gaji_data['total_hadir'] ?? 0) * ($gaji_data['honor_per_hadir'] ?? 50000), 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Bonus</span>
                    <span class="fw-bold">Rp <?= number_format($gaji_data['bonus'] ?? 0, 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top fw-bold">
                    <span>TOTAL HONOR</span>
                    <span class="text-success">Rp <?= number_format($gaji_data['total_honor'], 0, ',', '.') ?></span>
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
            <?php if(empty($detail_presensi)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-fingerprint fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Belum ada data presensi untuk periode ini</p>
                </div>
            <?php else: ?>
                <?php foreach($detail_presensi as $p): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <strong><?= date('d F Y', strtotime($p['tanggal'])) ?></strong>
                            <div class="small text-muted">Kelas: <?= $p['nama_kelas'] ?? '-' ?></div>
                        </div>
                        <span class="badge <?= $p['status'] == 'hadir' ? 'bg-success' : ($p['status'] == 'izin' ? 'bg-warning' : 'bg-danger') ?> rounded-pill">
                            <?= $p['status'] == 'hadir' ? 'Hadir' : ($p['status'] == 'izin' ? 'Izin' : 'Alpha') ?>
                            <?php if($p['status'] == 'hadir'): ?>
                                <i class="fas fa-check ms-1"></i>
                            <?php endif; ?>
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

<?php elseif($selected_guru && !$gaji_data): ?>
    <!-- Belum ada data gaji -->
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
            <h6 class="fw-bold">Belum Ada Data Gaji</h6>
            <p class="text-muted small">Belum ada data honor untuk guru ini pada periode <?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
            <p class="text-muted small">Pastikan guru sudah melakukan presensi pada periode tersebut.</p>
        </div>
    </div>
    
<?php elseif(!$selected_guru): ?>
    <!-- Belum pilih guru -->
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-user-graduate fa-4x text-muted mb-3"></i>
            <h6 class="fw-bold">Pilih Guru Terlebih Dahulu</h6>
            <p class="text-muted small">Silakan pilih guru, bulan, dan tahun di atas untuk melihat data gaji.</p>
        </div>
    </div>
<?php endif; ?>

<!-- Tombol Aksi (Jika Ada Data) -->
<?php if($selected_guru && $gaji_data): ?>
<div class="mt-3">
    <button onclick="window.print()" class="btn btn-outline-primary rounded-pill w-100">
        <i class="fas fa-print"></i> Cetak / Export PDF
    </button>
</div>
<?php endif; ?>

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
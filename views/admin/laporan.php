<?php
$page_title = 'Laporan Bulanan';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['admin']);

$db = getDB();

// Get filter parameters
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$export = $_GET['export'] ?? false;

// Pastikan bulan dalam format 2 digit dengan leading zero
$bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

// Nama bulan dalam Bahasa Indonesia
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Get payment report per bulan
$pendapatan_per_bulan = $db->prepare("SELECT MONTH(tanggal_bayar) as bulan, SUM(jumlah) as total 
                                      FROM pembayaran 
                                      WHERE YEAR(tanggal_bayar) = :tahun AND status = 'valid'
                                      GROUP BY MONTH(tanggal_bayar)");
$pendapatan_per_bulan->execute(['tahun' => $tahun]);
$pendapatan_data = $pendapatan_per_bulan->fetchAll(PDO::FETCH_ASSOC);

// Convert to array with month as key for easy lookup
$pendapatan_by_bulan = [];
foreach ($pendapatan_data as $pd) {
    $month_key = str_pad($pd['bulan'], 2, '0', STR_PAD_LEFT);
    $pendapatan_by_bulan[$month_key] = $pd['total'];
}

// Total pendapatan bulan ini
$pendapatan_bulan_ini = $db->prepare("SELECT SUM(jumlah) as total FROM pembayaran 
                                      WHERE MONTH(tanggal_bayar) = :bulan AND YEAR(tanggal_bayar) = :tahun AND status = 'valid'");
$pendapatan_bulan_ini->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$total_pendapatan = $pendapatan_bulan_ini->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total honor guru bulan ini
$honor_bulan_ini = $db->prepare("SELECT SUM(total_honor) as total FROM honor_guru WHERE bulan = :bulan AND tahun = :tahun");
$honor_bulan_ini->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$total_honor = $honor_bulan_ini->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total siswa baru bulan ini
$siswa_baru = $db->prepare("SELECT COUNT(*) as total FROM users 
                            WHERE role = 'siswa' AND MONTH(created_at) = :bulan AND YEAR(created_at) = :tahun");
$siswa_baru->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$total_siswa_baru = $siswa_baru->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total kelas aktif
$kelas_aktif = $db->query("SELECT COUNT(*) as total FROM kelas WHERE status = 'aktif'")->fetch(PDO::FETCH_ASSOC)['total'];

// Presensi bulan ini
$presensi_siswa = $db->prepare("SELECT COUNT(*) as total FROM presensi 
                                WHERE jenis = 'siswa' AND MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun");
$presensi_siswa->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$total_presensi_siswa = $presensi_siswa->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$presensi_guru = $db->prepare("SELECT COUNT(*) as total FROM presensi 
                               WHERE jenis = 'guru' AND MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun");
$presensi_guru->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$total_presensi_guru = $presensi_guru->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Daftar pembayaran detail
$detail_pembayaran = $db->prepare("SELECT p.*, u.nama_lengkap, k.nama_kelas 
                                   FROM pembayaran p 
                                   JOIN users u ON p.siswa_id = u.id 
                                   JOIN pendaftaran pd ON p.pendaftaran_id = pd.id
                                   JOIN kelas k ON pd.kelas_id = k.id
                                   WHERE MONTH(p.tanggal_bayar) = :bulan AND YEAR(p.tanggal_bayar) = :tahun AND p.status = 'valid'
                                   ORDER BY p.tanggal_bayar DESC");
$detail_pembayaran->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$data_pembayaran = $detail_pembayaran->fetchAll(PDO::FETCH_ASSOC);

// Get max value for progress bar
$max_pendapatan = !empty($pendapatan_by_bulan) ? max($pendapatan_by_bulan) : 1;

require_once '../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Laporan Bulanan</h5>
    <button onclick="window.print()" class="btn btn-danger rounded-pill btn-sm">
        <i class="fas fa-file-pdf"></i> Export PDF
    </button>
</div>

<!-- Filter Form -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-2">
            <div class="col-5">
                <select name="bulan" class="form-select rounded-3">
                    <?php foreach($nama_bulan as $key => $nama): ?>
                        <option value="<?= $key ?>" <?= $bulan == $key ? 'selected' : '' ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4">
                <select name="tahun" class="form-select rounded-3">
                    <?php for($i=2023; $i<=2030; $i++): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-3">
                <button type="submit" class="btn btn-primary rounded-pill w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Laporan Header -->
<div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="card-body text-center">
        <h4 class="fw-bold">BIMBEL AIUEO</h4>
        <p class="mb-0">Laporan Bulanan - <?= $nama_bulan[$bulan] ?> <?= $tahun ?></p>
        <small class="opacity-75">Dicetak: <?= date('d/m/Y H:i') ?></small>
    </div>
</div>

<!-- Ringkasan Utama -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card border-0 rounded-4 shadow-sm text-center p-3" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
            <small>Pendapatan</small>
            <h4 class="fw-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-6">
        <div class="card border-0 rounded-4 shadow-sm text-center p-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <small>Honor Guru</small>
            <h4 class="fw-bold">Rp <?= number_format($total_honor, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-6">
        <div class="card border-0 rounded-4 shadow-sm text-center p-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <small>Siswa Baru</small>
            <h4 class="fw-bold"><?= $total_siswa_baru ?> orang</h4>
        </div>
    </div>
    <div class="col-6">
        <div class="card border-0 rounded-4 shadow-sm text-center p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <small>Kelas Aktif</small>
            <h4 class="fw-bold"><?= $kelas_aktif ?> kelas</h4>
        </div>
    </div>
</div>

<!-- Statistik Kehadiran -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-fingerprint text-primary me-2"></i>Statistik Kehadiran</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6 text-center">
                <div class="bg-info bg-opacity-10 rounded-4 p-3">
                    <i class="fas fa-user-graduate fs-4 text-info"></i>
                    <h5 class="fw-bold mb-0"><?= $total_presensi_siswa ?></h5>
                    <small class="text-muted">Presensi Siswa</small>
                </div>
            </div>
            <div class="col-6 text-center">
                <div class="bg-success bg-opacity-10 rounded-4 p-3">
                    <i class="fas fa-chalkboard-user fs-4 text-success"></i>
                    <h5 class="fw-bold mb-0"><?= $total_presensi_guru ?></h5>
                    <small class="text-muted">Presensi Guru</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pendapatan per Bulan Chart -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Pendapatan per Bulan <?= $tahun ?></h6>
    </div>
    <div class="card-body">
        <?php foreach($nama_bulan as $key => $nama): ?>
            <?php $pendapatan = $pendapatan_by_bulan[$key] ?? 0; ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between small">
                    <span><?= $nama ?></span>
                    <span>Rp <?= number_format($pendapatan, 0, ',', '.') ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: <?= ($pendapatan / $max_pendapatan) * 100 ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Detail Pembayaran -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-receipt text-primary me-2"></i>Detail Pembayaran Bulan Ini</h6>
    </div>
    <div class="card-body">
        <?php if(empty($data_pembayaran)): ?>
            <div class="text-center py-4">
                <i class="fas fa-credit-card fa-3x text-muted mb-2"></i>
                <p class="text-muted">Belum ada data pembayaran</p>
            </div>
        <?php else: ?>
            <?php foreach($data_pembayaran as $d): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= htmlspecialchars($d['nama_lengkap']) ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($d['nama_kelas']) ?> | <?= date('d/m/Y', strtotime($d['tanggal_bayar'])) ?></div>
                    </div>
                    <span class="badge bg-success rounded-pill">Rp <?= number_format($d['jumlah'], 0, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
            <div class="mt-3 pt-2 border-top">
                <div class="d-flex justify-content-between fw-bold">
                    <span>TOTAL</span>
                    <span>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
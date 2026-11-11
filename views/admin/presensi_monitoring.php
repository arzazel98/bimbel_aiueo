<?php
$page_title = 'Monitoring Presensi';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['admin']);

$db = getDB();

// Get filter parameters
$filter_role = $_GET['role'] ?? 'siswa';
$filter_type = $_GET['filter_type'] ?? 'hari'; // hari, bulan, tahun
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Build query based on filter
if ($filter_type == 'hari') {
    $presensi = $db->prepare("SELECT p.*, u.nama_lengkap, u.username, k.nama_kelas 
                              FROM presensi p 
                              JOIN users u ON p.user_id = u.id 
                              LEFT JOIN kelas k ON p.kelas_id = k.id 
                              WHERE u.role = :role AND DATE(p.tanggal) = :tanggal
                              ORDER BY p.tanggal DESC");
    $presensi->execute(['role' => $filter_role, 'tanggal' => $tanggal]);
    $data_presensi = $presensi->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary for daily
    $total_hadir = 0;
    $total_izin = 0;
    $total_alpha = 0;
    foreach ($data_presensi as $p) {
        if ($p['status'] == 'hadir') $total_hadir++;
        elseif ($p['status'] == 'izin') $total_izin++;
        elseif ($p['status'] == 'alpha') $total_alpha++;
    }
    $total_all = $total_hadir + $total_izin + $total_alpha;
    
} elseif ($filter_type == 'bulan') {
    $presensi = $db->prepare("SELECT u.id, u.nama_lengkap, u.username,
                              COUNT(CASE WHEN p.status = 'hadir' THEN 1 END) as total_hadir,
                              COUNT(CASE WHEN p.status = 'izin' THEN 1 END) as total_izin,
                              COUNT(CASE WHEN p.status = 'alpha' THEN 1 END) as total_alpha
                              FROM users u 
                              LEFT JOIN presensi p ON p.user_id = u.id AND MONTH(p.tanggal) = :bulan AND YEAR(p.tanggal) = :tahun
                              WHERE u.role = :role AND u.status = 'aktif'
                              GROUP BY u.id
                              ORDER BY total_hadir DESC");
    $presensi->execute(['role' => $filter_role, 'bulan' => $bulan, 'tahun' => $tahun]);
    $data_presensi = $presensi->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary for monthly
    $total_hadir = 0;
    $total_izin = 0;
    $total_alpha = 0;
    foreach ($data_presensi as $p) {
        $total_hadir += $p['total_hadir'];
        $total_izin += $p['total_izin'];
        $total_alpha += $p['total_alpha'];
    }
    $total_all = $total_hadir + $total_izin + $total_alpha;
    
} else {
    $presensi = $db->prepare("SELECT u.id, u.nama_lengkap, u.username,
                              COUNT(CASE WHEN p.status = 'hadir' THEN 1 END) as total_hadir,
                              COUNT(CASE WHEN p.status = 'izin' THEN 1 END) as total_izin,
                              COUNT(CASE WHEN p.status = 'alpha' THEN 1 END) as total_alpha
                              FROM users u 
                              LEFT JOIN presensi p ON p.user_id = u.id AND YEAR(p.tanggal) = :tahun
                              WHERE u.role = :role AND u.status = 'aktif'
                              GROUP BY u.id
                              ORDER BY total_hadir DESC");
    $presensi->execute(['role' => $filter_role, 'tahun' => $tahun]);
    $data_presensi = $presensi->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary for yearly
    $total_hadir = 0;
    $total_izin = 0;
    $total_alpha = 0;
    foreach ($data_presensi as $p) {
        $total_hadir += $p['total_hadir'];
        $total_izin += $p['total_izin'];
        $total_alpha += $p['total_alpha'];
    }
    $total_all = $total_hadir + $total_izin + $total_alpha;
}

require_once '../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Filter Presensi</h5>
</div>

<!-- Filter Form -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <!-- Role Selection -->
            <div class="mb-3">
                <label class="form-label small fw-bold">Role</label>
                <div class="d-flex gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" value="siswa" id="role_siswa" <?= $filter_role == 'siswa' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="role_siswa">Siswa</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" value="guru" id="role_guru" <?= $filter_role == 'guru' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="role_guru">Guru</label>
                    </div>
                </div>
            </div>
            
            <!-- Filter Type -->
            <div class="mb-3">
                <label class="form-label small fw-bold">Tipe Filter</label>
                <div class="d-flex gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" value="hari" id="filter_hari" <?= $filter_type == 'hari' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="filter_hari">Per Hari</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" value="bulan" id="filter_bulan" <?= $filter_type == 'bulan' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="filter_bulan">Per Bulan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="filter_type" value="tahun" id="filter_tahun" <?= $filter_type == 'tahun' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="filter_tahun">Per Tahun</label>
                    </div>
                </div>
            </div>
            
            <!-- Date Inputs -->
            <?php if($filter_type == 'hari'): ?>
            <div class="mb-3">
                <label class="form-label small fw-bold">Tanggal</label>
                <input type="date" name="tanggal" class="form-control rounded-3" value="<?= $tanggal ?>" onchange="this.form.submit()">
            </div>
            <?php elseif($filter_type == 'bulan'): ?>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small fw-bold">Bulan</label>
                    <select name="bulan" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="01" <?= $bulan == '01' ? 'selected' : '' ?>>Januari</option>
                        <option value="02" <?= $bulan == '02' ? 'selected' : '' ?>>Februari</option>
                        <option value="03" <?= $bulan == '03' ? 'selected' : '' ?>>Maret</option>
                        <option value="04" <?= $bulan == '04' ? 'selected' : '' ?>>April</option>
                        <option value="05" <?= $bulan == '05' ? 'selected' : '' ?>>Mei</option>
                        <option value="06" <?= $bulan == '06' ? 'selected' : '' ?>>Juni</option>
                        <option value="07" <?= $bulan == '07' ? 'selected' : '' ?>>Juli</option>
                        <option value="08" <?= $bulan == '08' ? 'selected' : '' ?>>Agustus</option>
                        <option value="09" <?= $bulan == '09' ? 'selected' : '' ?>>September</option>
                        <option value="10" <?= $bulan == '10' ? 'selected' : '' ?>>Oktober</option>
                        <option value="11" <?= $bulan == '11' ? 'selected' : '' ?>>November</option>
                        <option value="12" <?= $bulan == '12' ? 'selected' : '' ?>>Desember</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Tahun</label>
                    <select name="tahun" class="form-select rounded-3" onchange="this.form.submit()">
                        <?php for($i=2023; $i<=2030; $i++): ?>
                            <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <?php else: ?>
            <div class="mb-3">
                <label class="form-label small fw-bold">Tahun</label>
                <select name="tahun" class="form-select rounded-3" onchange="this.form.submit()">
                    <?php for($i=2023; $i<=2030; $i++): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Ringkasan Presensi -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Ringkasan Kehadiran</h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-4">
                <div class="bg-success bg-opacity-10 rounded-4 p-3">
                    <i class="fas fa-check-circle text-success fs-4"></i>
                    <h3 class="fw-bold text-success mb-0"><?= $total_hadir ?></h3>
                    <small class="text-muted">Hadir</small>
                    <div class="small text-muted"><?= $total_all > 0 ? round(($total_hadir/$total_all)*100) : 0 ?>%</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-warning bg-opacity-10 rounded-4 p-3">
                    <i class="fas fa-clock text-warning fs-4"></i>
                    <h3 class="fw-bold text-warning mb-0"><?= $total_izin ?></h3>
                    <small class="text-muted">Izin</small>
                    <div class="small text-muted"><?= $total_all > 0 ? round(($total_izin/$total_all)*100) : 0 ?>%</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-danger bg-opacity-10 rounded-4 p-3">
                    <i class="fas fa-times-circle text-danger fs-4"></i>
                    <h3 class="fw-bold text-danger mb-0"><?= $total_alpha ?></h3>
                    <small class="text-muted">Alpha</small>
                    <div class="small text-muted"><?= $total_all > 0 ? round(($total_alpha/$total_all)*100) : 0 ?>%</div>
                </div>
            </div>
        </div>
        <div class="progress mt-3" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: <?= $total_all > 0 ? ($total_hadir/$total_all)*100 : 0 ?>%"></div>
            <div class="progress-bar bg-warning" style="width: <?= $total_all > 0 ? ($total_izin/$total_all)*100 : 0 ?>%"></div>
            <div class="progress-bar bg-danger" style="width: <?= $total_all > 0 ? ($total_alpha/$total_all)*100 : 0 ?>%"></div>
        </div>
    </div>
</div>

<!-- Daftar Presensi -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-list text-primary me-2"></i>Detail Presensi</h6>
    </div>
    <div class="card-body">
        <?php if(empty($data_presensi)): ?>
            <div class="text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada data presensi</p>
            </div>
        <?php else: ?>
            <?php foreach($data_presensi as $p): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= htmlspecialchars($p['nama_lengkap']) ?></strong>
                        <?php if($filter_type == 'hari'): ?>
                            <div class="small text-muted">
                                <?= date('d M Y H:i', strtotime($p['tanggal'])) ?> | 
                                Kelas: <?= $p['nama_kelas'] ?? '-' ?>
                            </div>
                        <?php else: ?>
                            <div class="small text-muted">
                                <?= $p['total_hadir'] ?? 0 ?> hadir, <?= $p['total_izin'] ?? 0 ?> izin, <?= $p['total_alpha'] ?? 0 ?> alpha
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($filter_type == 'hari'): ?>
                        <span class="badge <?= $p['status'] == 'hadir' ? 'bg-success' : ($p['status'] == 'izin' ? 'bg-warning' : 'bg-danger') ?> rounded-pill">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-primary rounded-pill">
                            <?= round((($p['total_hadir'] ?? 0) / max(1, ($p['total_hadir'] ?? 0) + ($p['total_izin'] ?? 0) + ($p['total_alpha'] ?? 0))) * 100) ?>%
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
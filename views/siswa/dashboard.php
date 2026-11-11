<?php
$page_title = 'Dashboard Siswa';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['siswa']);

$db = getDB();
$user_id = $_SESSION['user_id'];

// Get student data
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['nama_lengkap' => $_SESSION['user'] ?? 'Siswa', 'status' => 'aktif'];

// Get registered classes
$kelas_terdaftar = $db->prepare("SELECT p.*, k.nama_kelas, k.mata_pelajaran, k.harga_per_bulan,
                                  (SELECT status FROM pembayaran WHERE pendaftaran_id = p.id AND status = 'valid' LIMIT 1) as status_bayar
                                  FROM pendaftaran p 
                                  JOIN kelas k ON p.kelas_id = k.id 
                                  WHERE p.siswa_id = :siswa_id AND p.status_pendaftaran = 'disetujui'");
$kelas_terdaftar->execute(['siswa_id' => $user_id]);
$kelas_list = $kelas_terdaftar->fetchAll(PDO::FETCH_ASSOC);

// Get pending registration
$pending = $db->prepare("SELECT p.*, k.nama_kelas, k.mata_pelajaran, k.harga_per_bulan 
                         FROM pendaftaran p 
                         JOIN kelas k ON p.kelas_id = k.id 
                         WHERE p.siswa_id = :siswa_id AND p.status_pendaftaran = 'pending'");
$pending->execute(['siswa_id' => $user_id]);
$pending_list = $pending->fetchAll(PDO::FETCH_ASSOC);

// Get today's schedule
$today = date('l');
$hari_map = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$today_id = $hari_map[$today] ?? 'Senin';

$jadwal_hari = $db->prepare("SELECT j.*, k.nama_kelas, k.mata_pelajaran, u.nama_lengkap as guru_name
                             FROM jadwal j 
                             JOIN kelas k ON j.kelas_id = k.id 
                             LEFT JOIN guru_kelas gk ON gk.kelas_id = k.id
                             LEFT JOIN users u ON u.id = gk.guru_id
                             WHERE j.hari = :hari AND k.id IN (
                                 SELECT kelas_id FROM pendaftaran WHERE siswa_id = :siswa_id AND status_pendaftaran = 'disetujui'
                             )
                             ORDER BY j.jam_mulai");
$jadwal_hari->execute(['hari' => $today_id, 'siswa_id' => $user_id]);
$jadwal_list = $jadwal_hari->fetchAll(PDO::FETCH_ASSOC);

// Get pending payment
$tagihan = $db->prepare("SELECT p.*, k.nama_kelas, k.mata_pelajaran, k.harga_per_bulan 
                         FROM pendaftaran p 
                         JOIN kelas k ON p.kelas_id = k.id 
                         WHERE p.siswa_id = :siswa_id AND p.status_pendaftaran = 'disetujui'
                         AND NOT EXISTS (SELECT 1 FROM pembayaran WHERE pendaftaran_id = p.id AND status = 'valid')");
$tagihan->execute(['siswa_id' => $user_id]);
$tagihan_list = $tagihan->fetchAll(PDO::FETCH_ASSOC);
$total_tagihan = array_sum(array_column($tagihan_list, 'harga_per_bulan'));

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Halo, <?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Siswa') ?>! 👋</h4>
    <p class="text-muted small">Selamat datang di Portal Siswa Bimbel AIUEO</p>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Kelas Diikuti</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h2 class="mb-0 fw-bold" style="font-size: 32px;"><?= count($kelas_list) ?></h2>
                    <i class="fas fa-book-open opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Total Tagihan Aktif</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h5 class="mb-0 fw-bold" style="font-size: 20px;">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></h5>
                    <i class="fas fa-credit-card opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Jadwal Hari Ini</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h2 class="mb-0 fw-bold" style="font-size: 32px;"><?= count($jadwal_list) ?></h2>
                    <i class="fas fa-calendar-check opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body d-flex flex-column justify-content-between p-3 text-white">
                <small class="opacity-75 fw-semibold" style="font-size: 12px;">Status Akun</small>
                <div class="d-flex justify-content-between align-items-end mt-3">
                    <h5 class="mb-0 fw-bold" style="font-size: 20px;"><?= ucfirst($siswa['status'] ?? 'Aktif') ?></h5>
                    <i class="fas fa-user-check opacity-50 fs-2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($pending_list)): ?>
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-warning bg-opacity-10 border-warning border-opacity-25">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-clock text-warning fs-3"></i>
            <div>
                <strong class="d-block text-dark">Pendaftaran Menunggu Konfirmasi</strong>
                <p class="small text-muted mb-0">Anda memiliki <?= count($pending_list) ?> pendaftaran kelas yang sedang diproses dan diverifikasi oleh admin.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!empty($tagihan_list)): ?>
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-danger bg-opacity-10 border-danger border-opacity-25">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-money-bill-wave text-danger fs-3"></i>
                <div>
                    <strong class="d-block text-dark">Ada Tagihan Belum Dibayar</strong>
                    <p class="small text-muted mb-0">Total tagihan Anda sebesar <strong>Rp <?= number_format($total_tagihan, 0, ',', '.') ?></strong>.</p>
                </div>
            </div>
            <a href="pembayaran.php" class="btn btn-sm btn-danger rounded-pill px-4">
                <i class="fas fa-credit-card me-1"></i> Bayar Sekarang
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 2-Column Responsive Layout for Desktop -->
<div class="row g-4 mb-4">
    <!-- Jadwal Hari Ini -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Belajar Hari Ini (<?= $today_id ?>)</h6>
                <a href="jadwal.php" class="btn btn-sm btn-light rounded-pill text-primary" style="font-size: 11.5px;">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if(empty($jadwal_list)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3 opacity-50"></i>
                        <h6 class="fw-semibold text-muted">Tidak ada jadwal untuk hari ini</h6>
                        <p class="text-muted small mb-0">Selamat beristirahat atau review materi belajar Anda! 🎉</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mata Pelajaran & Kelas</th>
                                    <th>Waktu & Ruang</th>
                                    <th>Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($jadwal_list as $j): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($j['mata_pelajaran'] ?? '') ?></div>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill"><?= htmlspecialchars($j['nama_kelas'] ?? '') ?></span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-dark">
                                                <i class="far fa-clock text-muted me-1"></i><?= substr($j['jam_mulai'] ?? '00:00', 0, 5) ?> - <?= substr($j['jam_selesai'] ?? '00:00', 0, 5) ?>
                                            </div>
                                            <small class="text-muted"><i class="fas fa-door-open text-muted me-1"></i><?= htmlspecialchars($j['ruangan'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold text-secondary"><i class="fas fa-chalkboard-user text-muted me-1"></i><?= htmlspecialchars($j['guru_name'] ?? 'Belum ditentukan') ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kelas Saya -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-chalkboard text-primary me-2"></i>Kelas Terdaftar</h6>
                <a href="pendaftaran.php" class="btn btn-sm btn-light rounded-pill text-primary" style="font-size: 11.5px;">Daftar Kelas</a>
            </div>
            <div class="card-body">
                <?php if(empty($kelas_list)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fa-3x text-muted mb-3 opacity-50"></i>
                        <h6 class="fw-semibold text-muted">Belum ada kelas yang diikuti</h6>
                        <p class="text-muted small mb-3">Pilih dan daftar kelas bimbel favorit Anda sekarang.</p>
                        <a href="pendaftaran.php" class="btn btn-sm btn-primary rounded-pill px-3">Daftar Kelas Sekarang</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach($kelas_list as $k): ?>
                            <div class="p-3 rounded-3 border bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="d-block text-dark"><?= htmlspecialchars($k['mata_pelajaran'] ?? '') ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($k['nama_kelas'] ?? '') ?></small>
                                </div>
                                <span class="badge <?= ($k['status_bayar'] ?? '') == 'valid' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill">
                                    <i class="fas <?= ($k['status_bayar'] ?? '') == 'valid' ? 'fa-check-circle' : 'fa-hourglass-half' ?> me-1"></i>
                                    <?= ($k['status_bayar'] ?? '') == 'valid' ? 'Lunas' : 'Belum Bayar' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
require_once '../layout/footer.php'; 
?>
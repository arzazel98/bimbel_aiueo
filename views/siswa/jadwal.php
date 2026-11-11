<?php
$page_title = 'Jadwal Kelas';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['siswa']);

$db = getDB();
$user_id = $_SESSION['user_id'];

// Ambil hari dari GET, default ke hari ini
$hari_selected = isset($_GET['hari']) ? $_GET['hari'] : date('l');
$hari_map = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$hari_id = isset($hari_map[$hari_selected]) ? $hari_map[$hari_selected] : 'Senin';

// Ambil semua kelas yang SUDAH DISETUJUI oleh admin
$kelas_disetujui = $db->prepare("SELECT kelas_id FROM pendaftaran WHERE siswa_id = :siswa_id AND status_pendaftaran = 'disetujui'");
$kelas_disetujui->execute(['siswa_id' => $user_id]);
$approved_ids = $kelas_disetujui->fetchAll(PDO::FETCH_COLUMN);

// Get schedules for approved classes pada hari yang dipilih
$jadwal_list = [];
if (!empty($approved_ids)) {
    $placeholders = implode(',', array_fill(0, count($approved_ids), '?'));
    $sql = "SELECT j.*, k.nama_kelas, k.mata_pelajaran, u.nama_lengkap as guru_name
            FROM jadwal j 
            JOIN kelas k ON j.kelas_id = k.id 
            JOIN guru_kelas gk ON gk.kelas_id = k.id
            JOIN users u ON u.id = gk.guru_id
            WHERE j.hari = ? AND j.kelas_id IN ($placeholders)
            ORDER BY j.jam_mulai";
    $stmt = $db->prepare($sql);
    $params = array_merge([$hari_id], $approved_ids);
    $stmt->execute($params);
    $jadwal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get pending registrations count
$pending = $db->prepare("SELECT COUNT(*) as total FROM pendaftaran WHERE siswa_id = :siswa_id AND status_pendaftaran = 'pending'");
$pending->execute(['siswa_id' => $user_id]);
$pending_count = $pending->fetch(PDO::FETCH_ASSOC)['total'];

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Jadwal Kelas Saya</h5>
    <p class="text-muted small">Jadwal akan muncul setelah admin memverifikasi pembayaran</p>
</div>

<!-- Alert untuk pendaftaran yang masih pending -->
<?php if($pending_count > 0): ?>
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-warning bg-opacity-10">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-clock text-warning fs-5"></i>
            <div>
                <strong class="small">Menunggu Verifikasi Admin</strong>
                <p class="small text-muted mb-0">Anda memiliki <?= $pending_count ?> pendaftaran yang menunggu verifikasi pembayaran. Jadwal akan muncul setelah diverifikasi.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hari Selector - SEMUA HARI BISA DIKLIK -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <?php 
            $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            foreach($hari_list as $h): 
            ?>
                <div class="col">
                    <a href="?hari=<?= $h ?>" class="text-decoration-none">
                        <div class="btn w-100 rounded-pill <?= $hari_id == $h ? 'btn-primary' : 'btn-outline-secondary' ?>" style="font-size: 12px; padding: 8px 4px;">
                            <?= substr($h, 0, 3) ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Jadwal Hari Ini -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-calendar-day text-primary me-2"></i>Jadwal <?= $hari_id ?></h6>
    </div>
    <div class="card-body">
        <?php if(empty($jadwal_list)): ?>
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-2"></i>
                <?php if($pending_count > 0): ?>
                    <p class="text-muted small mb-0">Belum ada jadwal</p>
                    <p class="text-muted small">Pembayaran Anda sedang diverifikasi admin.</p>
                <?php elseif(empty($approved_ids)): ?>
                    <p class="text-muted small mb-0">Anda belum terdaftar di kelas manapun</p>
                    <a href="pendaftaran.php" class="btn btn-sm btn-primary rounded-pill mt-2">Daftar Kelas</a>
                <?php else: ?>
                    <p class="text-muted small mb-0">Tidak ada jadwal untuk hari <?= $hari_id ?></p>
                    <p class="text-muted small">Mungkin admin belum membuat jadwal untuk kelas ini.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach($jadwal_list as $j): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= htmlspecialchars($j['mata_pelajaran']) ?></strong>
                        <div class="small text-muted">
                            <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                        </div>
                        <div class="small text-muted">Guru: <?= htmlspecialchars($j['guru_name']) ?></div>
                        <div class="small text-muted">Ruang: <?= $j['ruangan'] ?? '-' ?></div>
                    </div>
                    <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($j['nama_kelas']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
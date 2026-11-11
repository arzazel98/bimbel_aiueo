<?php
$page_title = 'Jadwal Kelas';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['siswa']);

$db = getDB();
$user_id = $_SESSION['user_id'];

// Ambil hari dari GET atau default hari ini
$hari_list_all = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$hari_param = $_GET['hari'] ?? '';
if (in_array($hari_param, $hari_list_all)) {
    $hari_id = $hari_param;
} else {
    $hari_map = [
        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
    ];
    $hari_id = $hari_map[date('l')] ?? 'Senin';
}

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
            LEFT JOIN guru_kelas gk ON gk.kelas_id = k.id
            LEFT JOIN users u ON u.id = gk.guru_id
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
$pending_res = $pending->fetch(PDO::FETCH_ASSOC);
$pending_count = (int)($pending_res['total'] ?? 0);

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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kelas & Mata Pelajaran</th>
                            <th>Waktu Belajar</th>
                            <th>Guru Pengajar</th>
                            <th>Ruangan</th>
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
                                <div class="fw-semibold text-dark"><i class="far fa-clock text-muted me-1"></i><?= substr($j['jam_mulai'] ?? '00:00', 0, 5) ?> - <?= substr($j['jam_selesai'] ?? '00:00', 0, 5) ?></div>
                            </td>
                            <td>
                                <span class="fw-semibold text-secondary"><i class="fas fa-chalkboard-user text-muted me-1"></i><?= htmlspecialchars($j['guru_name'] ?? 'Belum ditentukan') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><i class="fas fa-door-open text-muted me-1"></i><?= htmlspecialchars($j['ruangan'] ?? '-') ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
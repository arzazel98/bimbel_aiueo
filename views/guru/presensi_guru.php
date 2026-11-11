<?php
$page_title = 'Presensi Guru';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['guru']);

$db = getDB();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Cek apakah sudah presensi hari ini
$cek = $db->prepare("SELECT id, status FROM presensi WHERE user_id = :user_id AND jenis = 'guru' AND DATE(tanggal) = CURDATE()");
$cek->execute(['user_id' => $user_id]);
$sudah_presensi = $cek->fetch();

// Handle presensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    
    if ($sudah_presensi) {
        // Update presensi yang sudah ada
        $stmt = $db->prepare("UPDATE presensi SET status = :status, waktu = NOW() WHERE id = :id");
        if ($stmt->execute(['status' => $status, 'id' => $sudah_presensi['id']])) {
            $message = "Presensi berhasil diperbarui!";
            // Refresh data
            $cek->execute(['user_id' => $user_id]);
            $sudah_presensi = $cek->fetch();
        } else {
            $error = "Gagal memperbarui presensi!";
        }
    } else {
        // Insert presensi baru
        $stmt = $db->prepare("INSERT INTO presensi (user_id, tanggal, jenis, status) VALUES (:user_id, CURDATE(), 'guru', :status)");
        if ($stmt->execute(['user_id' => $user_id, 'status' => $status])) {
            $message = "Presensi berhasil dicatat!";
            // Refresh data
            $cek->execute(['user_id' => $user_id]);
            $sudah_presensi = $cek->fetch();
        } else {
            $error = "Gagal mencatat presensi!";
        }
    }
}

// Get presensi history bulan ini
$history = $db->prepare("SELECT * FROM presensi 
                         WHERE user_id = :user_id AND jenis = 'guru' 
                         AND MONTH(tanggal) = MONTH(CURDATE()) 
                         ORDER BY tanggal DESC");
$history->execute(['user_id' => $user_id]);
$history_list = $history->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Presensi Guru</h5>
    <p class="text-muted small">Catat kehadiran Anda hari ini</p>
</div>

<?php if($message): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
        <i class="fas fa-check-circle"></i> <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Status Presensi Hari Ini -->
<div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body text-center text-white p-4">
        <i class="fas fa-calendar-day fs-2 mb-2"></i>
        <h5 class="fw-bold mb-0"><?= date('l, d F Y') ?></h5>
        <div class="mt-3">
            <?php if($sudah_presensi): ?>
                <div class="mb-2">
                    <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                        <i class="fas fa-check-circle text-success"></i> Status: <?= ucfirst($sudah_presensi['status']) ?>
                    </span>
                </div>
                <p class="small mb-0 opacity-75">Anda sudah melakukan presensi hari ini</p>
            <?php else: ?>
                <p class="small mb-3 opacity-75">Anda belum melakukan presensi hari ini</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Form Presensi -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-fingerprint text-primary me-2"></i>Catat Kehadiran</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-4">
                    <button type="submit" name="status" value="hadir" class="btn btn-success rounded-pill w-100 py-3" <?= $sudah_presensi ? 'disabled' : '' ?>>
                        <i class="fas fa-check-circle fs-4 d-block mb-1"></i>
                        <small>Hadir</small>
                    </button>
                </div>
                <div class="col-4">
                    <button type="submit" name="status" value="izin" class="btn btn-warning rounded-pill w-100 py-3" <?= $sudah_presensi ? 'disabled' : '' ?>>
                        <i class="fas fa-clock fs-4 d-block mb-1"></i>
                        <small>Izin</small>
                    </button>
                </div>
                <div class="col-4">
                    <button type="submit" name="status" value="alpha" class="btn btn-danger rounded-pill w-100 py-3" <?= $sudah_presensi ? 'disabled' : '' ?>>
                        <i class="fas fa-times-circle fs-4 d-block mb-1"></i>
                        <small>Alpha</small>
                    </button>
                </div>
            </div>
        </form>
        
        <?php if($sudah_presensi): ?>
            <div class="text-center mt-3">
                <small class="text-muted">*Presensi hanya bisa dilakukan sekali sehari. Hubungi admin jika ada perubahan.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Riwayat Presensi Bulan Ini -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>Riwayat Presensi Bulan Ini</h6>
    </div>
    <div class="card-body">
        <?php if(empty($history_list)): ?>
            <div class="text-center py-4">
                <i class="fas fa-fingerprint fa-3x text-muted mb-2"></i>
                <p class="text-muted small mb-0">Belum ada riwayat presensi</p>
            </div>
        <?php else: ?>
            <?php foreach($history_list as $h): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= date('d F Y', strtotime($h['tanggal'])) ?></strong>
                        <div class="small text-muted"><?= date('H:i', strtotime($h['waktu'])) ?></div>
                    </div>
                    <span class="badge <?= $h['status'] == 'hadir' ? 'bg-success' : ($h['status'] == 'izin' ? 'bg-warning' : 'bg-danger') ?> rounded-pill">
                        <?= ucfirst($h['status']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
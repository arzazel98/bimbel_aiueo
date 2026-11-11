<?php
$page_title = 'Presensi Siswa';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['guru']);

$db = getDB();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get classes taught by this teacher
$kelas_list = $db->prepare("SELECT k.id, k.nama_kelas, k.mata_pelajaran 
                            FROM guru_kelas gk 
                            JOIN kelas k ON gk.kelas_id = k.id 
                            WHERE gk.guru_id = :guru_id AND k.status = 'aktif'");
$kelas_list->execute(['guru_id' => $user_id]);
$kelas = $kelas_list->fetchAll(PDO::FETCH_ASSOC);

$selected_kelas = $_GET['kelas_id'] ?? ($kelas[0]['id'] ?? 0);
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Get students in selected class
$siswa_list = [];
if ($selected_kelas) {
    $siswa_list = $db->prepare("SELECT u.id, u.nama_lengkap, u.username, p.id as pendaftaran_id
                                FROM pendaftaran p
                                JOIN users u ON p.siswa_id = u.id
                                WHERE p.kelas_id = :kelas_id AND p.status_pendaftaran = 'disetujui'");
    $siswa_list->execute(['kelas_id' => $selected_kelas]);
    $siswa = $siswa_list->fetchAll(PDO::FETCH_ASSOC);
    
    // Get existing attendance for selected date
    $presensi_existing = [];
    if (!empty($siswa)) {
        $ids = array_column($siswa, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT user_id, status FROM presensi 
                              WHERE user_id IN ($placeholders) AND jenis = 'siswa' AND DATE(tanggal) = ?");
        $params = array_merge($ids, [$tanggal]);
        $stmt->execute($params);
        $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($existing as $e) {
            $presensi_existing[$e['user_id']] = $e['status'];
        }
    }
}

// Handle presensi submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['presensi'])) {
    $presensi_data = $_POST['presensi'];
    $kelas_id = $_POST['kelas_id'];
    $tanggal_post = $_POST['tanggal'];
    
    $success = 0;
    foreach ($presensi_data as $siswa_id => $status) {
        // Check if attendance already exists
        $cek = $db->prepare("SELECT id FROM presensi WHERE user_id = :user_id AND jenis = 'siswa' AND DATE(tanggal) = :tanggal");
        $cek->execute(['user_id' => $siswa_id, 'tanggal' => $tanggal_post]);
        
        if ($cek->fetch()) {
            // Update
            $stmt = $db->prepare("UPDATE presensi SET status = :status, waktu = NOW() WHERE user_id = :user_id AND jenis = 'siswa' AND DATE(tanggal) = :tanggal");
            if ($stmt->execute(['status' => $status, 'user_id' => $siswa_id, 'tanggal' => $tanggal_post])) {
                $success++;
            }
        } else {
            // Insert
            $stmt = $db->prepare("INSERT INTO presensi (user_id, kelas_id, tanggal, jenis, status) VALUES (:user_id, :kelas_id, :tanggal, 'siswa', :status)");
            if ($stmt->execute(['user_id' => $siswa_id, 'kelas_id' => $kelas_id, 'tanggal' => $tanggal_post, 'status' => $status])) {
                $success++;
            }
        }
    }
    
    if ($success > 0) {
        $message = "Presensi siswa berhasil disimpan!";
        // Refresh data
        $presensi_existing = [];
        $stmt = $db->prepare("SELECT user_id, status FROM presensi 
                              WHERE user_id IN ($placeholders) AND jenis = 'siswa' AND DATE(tanggal) = ?");
        $params = array_merge(array_column($siswa, 'id'), [$tanggal]);
        $stmt->execute($params);
        $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($existing as $e) {
            $presensi_existing[$e['user_id']] = $e['status'];
        }
    } else {
        $error = "Gagal menyimpan presensi!";
    }
}

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Presensi Siswa</h5>
    <p class="text-muted small">Catat kehadiran siswa di kelas Anda</p>
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

<!-- Filter Form -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="mb-3">
                <label class="form-label small fw-bold">Pilih Kelas</label>
                <select name="kelas_id" class="form-select rounded-3" onchange="this.form.submit()">
                    <?php foreach($kelas as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selected_kelas == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?> - <?= htmlspecialchars($k['mata_pelajaran']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Tanggal</label>
                <input type="date" name="tanggal" class="form-control rounded-3" value="<?= $tanggal ?>" onchange="this.form.submit()">
            </div>
        </form>
    </div>
</div>

<!-- Form Presensi Siswa -->
<?php if($selected_kelas && !empty($siswa)): ?>
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-users text-primary me-2"></i>Daftar Siswa - <?= date('d F Y', strtotime($tanggal)) ?></h6>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="kelas_id" value="<?= $selected_kelas ?>">
            <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
            
            <?php foreach($siswa as $s): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= htmlspecialchars($s['nama_lengkap']) ?></strong>
                        <div class="small text-muted">@<?= htmlspecialchars($s['username']) ?></div>
                    </div>
                    <div class="btn-group" role="group">
                        <input type="radio" name="presensi[<?= $s['id'] ?>]" value="hadir" 
                               class="btn-check" id="hadir_<?= $s['id'] ?>" 
                               <?= (isset($presensi_existing[$s['id']]) && $presensi_existing[$s['id']] == 'hadir') ? 'checked' : '' ?>>
                        <label class="btn btn-sm btn-outline-success rounded-start-pill" for="hadir_<?= $s['id'] ?>">
                            <i class="fas fa-check"></i> Hadir
                        </label>
                        
                        <input type="radio" name="presensi[<?= $s['id'] ?>]" value="izin" 
                               class="btn-check" id="izin_<?= $s['id'] ?>"
                               <?= (isset($presensi_existing[$s['id']]) && $presensi_existing[$s['id']] == 'izin') ? 'checked' : '' ?>>
                        <label class="btn btn-sm btn-outline-warning" for="izin_<?= $s['id'] ?>">
                            <i class="fas fa-clock"></i> Izin
                        </label>
                        
                        <input type="radio" name="presensi[<?= $s['id'] ?>]" value="alpha" 
                               class="btn-check" id="alpha_<?= $s['id'] ?>"
                               <?= (isset($presensi_existing[$s['id']]) && $presensi_existing[$s['id']] == 'alpha') ? 'checked' : '' ?>>
                        <label class="btn btn-sm btn-outline-danger rounded-end-pill" for="alpha_<?= $s['id'] ?>">
                            <i class="fas fa-times"></i> Alpha
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-primary rounded-pill w-100 mt-3">
                <i class="fas fa-save"></i> Simpan Presensi
            </button>
        </form>
    </div>
</div>
<?php elseif($selected_kelas && empty($siswa)): ?>
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-3"></i>
            <h6 class="fw-bold">Belum Ada Siswa</h6>
            <p class="text-muted small">Belum ada siswa yang terdaftar di kelas ini.</p>
        </div>
    </div>
<?php elseif(empty($kelas)): ?>
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
            <h6 class="fw-bold">Belum Ada Kelas</h6>
            <p class="text-muted small">Anda belum terdaftar sebagai pengajar di kelas manapun.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../layout/footer.php'; ?>
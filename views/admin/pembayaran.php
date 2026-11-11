<?php
$page_title = 'Verifikasi Pembayaran';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['admin']);

$db = getDB();
$message = '';
$error = '';

// Update payment status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'validasi') {
        // Mulai transaction
        $db->beginTransaction();
        
        try {
            // 1. Update status pembayaran jadi valid
            $stmt = $db->prepare("UPDATE pembayaran SET status = 'valid', verifikasi_oleh = :admin, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['admin' => $_SESSION['user_id'], 'id' => $_POST['id']]);
            
            // 2. Ambil pendaftaran_id dari pembayaran
            $getPendaftaran = $db->prepare("SELECT pendaftaran_id FROM pembayaran WHERE id = :id");
            $getPendaftaran->execute(['id' => $_POST['id']]);
            $pendaftaran_id = $getPendaftaran->fetch(PDO::FETCH_ASSOC)['pendaftaran_id'];
            
            // 3. Update status pendaftaran menjadi disetujui
            $updatePendaftaran = $db->prepare("UPDATE pendaftaran SET status_pendaftaran = 'disetujui' WHERE id = :pendaftaran_id");
            $updatePendaftaran->execute(['pendaftaran_id' => $pendaftaran_id]);
            
            // Commit transaction
            $db->commit();
            
            $message = "✅ Pembayaran berhasil divalidasi! Siswa sekarang terdaftar dan bisa melihat jadwal.";
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Gagal memvalidasi: " . $e->getMessage();
        }
        
    } elseif ($_POST['action'] == 'tolak') {
        $stmt = $db->prepare("UPDATE pembayaran SET status = 'ditolak', catatan = :catatan, updated_at = NOW() WHERE id = :id");
        if ($stmt->execute(['catatan' => $_POST['catatan'] ?? '', 'id' => $_POST['id']])) {
            $message = "Pembayaran ditolak!";
        } else {
            $error = "Gagal menolak!";
        }
    }
}

// Get all pending payments
$pembayaran = $db->prepare("SELECT p.*, 
                            u.nama_lengkap as siswa_nama, 
                            u.username,
                            k.nama_kelas, 
                            k.mata_pelajaran,
                            pd.tanggal_daftar,
                            pd.status_pendaftaran
                            FROM pembayaran p 
                            JOIN users u ON p.siswa_id = u.id 
                            JOIN pendaftaran pd ON p.pendaftaran_id = pd.id
                            JOIN kelas k ON pd.kelas_id = k.id
                            WHERE p.status = 'pending'
                            ORDER BY p.created_at DESC");
$pembayaran->execute();
$data_pembayaran = $pembayaran->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Verifikasi Pembayaran</h5>
</div>

<?php if($message): ?>
    <div class="alert alert-success rounded-4"><?= $message ?></div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger rounded-4"><?= $error ?></div>
<?php endif; ?>

<?php if(empty($data_pembayaran)): ?>
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
            <h6 class="fw-bold">Tidak Ada Pembayaran Pending</h6>
            <p class="text-muted small">Semua pembayaran sudah diverifikasi.</p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info rounded-4">
        <i class="fas fa-info-circle"></i> Ada <strong><?= count($data_pembayaran) ?></strong> pembayaran yang menunggu verifikasi.
    </div>
    
    <div class="row g-3 mb-4">
    <?php foreach($data_pembayaran as $p): ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($p['siswa_nama']) ?></h6>
                            <span class="badge bg-light text-muted border">@<?= htmlspecialchars($p['username']) ?></span>
                        </div>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                            <i class="fas fa-hourglass-half me-1"></i> Menunggu Verifikasi
                        </span>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Kelas & Mapel:</span>
                                <strong><?= htmlspecialchars($p['nama_kelas']) ?></strong> (<?= $p['mata_pelajaran'] ?>)
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Total Transfer:</span>
                                <strong class="text-success fs-6">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Metode:</span>
                                <span class="text-uppercase fw-semibold"><?= $p['metode_pembayaran'] ?></span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Status Akun:</span>
                                <span class="badge <?= $p['status_pendaftaran'] == 'pending' ? 'bg-warning text-dark' : 'bg-success' ?> rounded-pill">
                                    <?= $p['status_pendaftaran'] == 'pending' ? 'Pending Approval' : 'Aktif' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 pt-2 border-top">
                    <form method="POST" class="flex-grow-1">
                        <input type="hidden" name="action" value="validasi">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-success rounded-pill w-100 py-2" onclick="return confirm('Validasi pembayaran ini? Siswa akan langsung aktif.')">
                            <i class="fas fa-check-circle me-1"></i> Setujui & Validasi
                        </button>
                    </form>
                    <form method="POST" class="flex-grow-1">
                        <input type="hidden" name="action" value="tolak">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger rounded-pill w-100 py-2" onclick="return confirm('Tolak pembayaran ini?')">
                            <i class="fas fa-times-circle me-1"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../layout/footer.php'; ?>
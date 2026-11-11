<?php
$page_title = 'Pembayaran';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['siswa']);

$db = getDB();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pendaftaran_id'])) {
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $jumlah = $_POST['jumlah'];
    $metode = $_POST['metode'];
    
    // Get pendaftaran info
    $stmt = $db->prepare("SELECT k.harga_per_bulan FROM pendaftaran p JOIN kelas k ON p.kelas_id = k.id WHERE p.id = :id");
    $stmt->execute(['id' => $pendaftaran_id]);
    $harga = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($harga && $jumlah >= $harga['harga_per_bulan']) {
        $stmt = $db->prepare("INSERT INTO pembayaran (pendaftaran_id, siswa_id, jumlah, tanggal_bayar, metode_pembayaran, status) 
                              VALUES (:pendaftaran_id, :siswa_id, :jumlah, :tanggal, :metode, 'pending')");
        
        if ($stmt->execute([
            'pendaftaran_id' => $pendaftaran_id,
            'siswa_id' => $user_id,
            'jumlah' => $jumlah,
            'tanggal' => date('Y-m-d'),
            'metode' => $metode
        ])) {
            $message = "Pembayaran berhasil dikirim! Menunggu verifikasi admin.";
        } else {
            $error = "Pembayaran gagal, silakan coba lagi!";
        }
    } else {
        $error = "Jumlah pembayaran kurang! Minimal Rp " . number_format($harga['harga_per_bulan'], 0, ',', '.');
    }
}

// Get pending payments (belum dibayar) - pendaftaran yang sudah disetujui tapi belum bayar
$tagihan = $db->prepare("SELECT p.*, k.nama_kelas, k.mata_pelajaran, k.harga_per_bulan 
                         FROM pendaftaran p 
                         JOIN kelas k ON p.kelas_id = k.id 
                         WHERE p.siswa_id = :siswa_id AND p.status_pendaftaran = 'pending'
                         AND NOT EXISTS (SELECT 1 FROM pembayaran WHERE pendaftaran_id = p.id AND status = 'valid')");
$tagihan->execute(['siswa_id' => $user_id]);
$tagihan_list = $tagihan->fetchAll(PDO::FETCH_ASSOC);
$total_tagihan = array_sum(array_column($tagihan_list, 'harga_per_bulan'));

// Get payment history
$riwayat = $db->prepare("SELECT p.*, k.nama_kelas, k.mata_pelajaran 
                         FROM pembayaran p 
                         JOIN pendaftaran pd ON p.pendaftaran_id = pd.id
                         JOIN kelas k ON pd.kelas_id = k.id
                         WHERE p.siswa_id = :siswa_id 
                         ORDER BY p.created_at DESC");
$riwayat->execute(['siswa_id' => $user_id]);
$riwayat_list = $riwayat->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Pembayaran</h5>
    <p class="text-muted small">Kelola pembayaran kelas Anda</p>
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

<!-- Ringkasan Tagihan -->
<div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
    <div class="card-body text-center text-white p-4">
        <i class="fas fa-receipt fs-1 mb-2"></i>
        <h2 class="fw-bold mb-0">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></h2>
        <small>Total Tagihan Belum Dibayar</small>
        <div class="mt-2">
            <span class="badge bg-light text-dark rounded-pill"><?= count($tagihan_list) ?> Tagihan</span>
        </div>
    </div>
</div>

<!-- Tagihan Detail -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-clock text-warning me-2"></i>Tagihan Belum Dibayar</h6>
    </div>
    <div class="card-body">
        <?php if(empty($tagihan_list)): ?>
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                <p class="text-muted small mb-0">Tidak ada tagihan. Semua sudah lunas! 🎉</p>
            </div>
        <?php else: ?>
            <?php foreach($tagihan_list as $index => $t): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 <?= $index < count($tagihan_list)-1 ? 'border-bottom' : '' ?>">
                    <div>
                        <strong><?= htmlspecialchars($t['mata_pelajaran']) ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($t['nama_kelas']) ?></div>
                        <div class="small fw-bold text-danger mt-1">Rp <?= number_format($t['harga_per_bulan'], 0, ',', '.') ?></div>
                    </div>
                    <button class="btn btn-primary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#bayarModal<?= $t['id'] ?>">
                        <i class="fas fa-credit-card"></i> Bayar Sekarang
                    </button>
                </div>
                
                <!-- Modal Bayar -->
                <div class="modal fade" id="bayarModal<?= $t['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <form method="POST">
                                <input type="hidden" name="pendaftaran_id" value="<?= $t['id'] ?>">
                                <div class="modal-header border-0">
                                    <h6 class="fw-bold">Konfirmasi Pembayaran</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="bg-light rounded-3 p-3 mb-3">
                                        <p class="small mb-1">Kelas: <strong><?= htmlspecialchars($t['mata_pelajaran']) ?></strong></p>
                                        <p class="small mb-1">Tagihan: <strong class="text-danger">Rp <?= number_format($t['harga_per_bulan'], 0, ',', '.') ?></strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Jumlah Bayar</label>
                                        <input type="number" name="jumlah" class="form-control rounded-3" value="<?= $t['harga_per_bulan'] ?>" required>
                                        <small class="text-muted">Minimal Rp <?= number_format($t['harga_per_bulan'], 0, ',', '.') ?></small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Metode Pembayaran</label>
                                        <select name="metode" class="form-select rounded-3" required>
                                            <option value="transfer">🏦 Transfer Bank</option>
                                            <option value="tunai">💵 Tunai (ke Admin)</option>
                                            <option value="kartu_kredit">💳 Kartu Kredit</option>
                                        </select>
                                    </div>
                                    <div class="alert alert-info small rounded-3">
                                        <i class="fas fa-info-circle"></i> Pembayaran akan diverifikasi oleh admin. Status akan berubah menjadi "Lunas" setelah diverifikasi.
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary rounded-pill">Kirim Pembayaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Riwayat Pembayaran -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>Riwayat Pembayaran</h6>
    </div>
    <div class="card-body">
        <?php if(empty($riwayat_list)): ?>
            <div class="text-center py-4">
                <i class="fas fa-credit-card fa-3x text-muted mb-2"></i>
                <p class="text-muted small mb-0">Belum ada riwayat pembayaran</p>
            </div>
        <?php else: ?>
            <?php foreach($riwayat_list as $r): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <strong><?= htmlspecialchars($r['mata_pelajaran']) ?></strong>
                        <div class="small text-muted"><?= date('d M Y', strtotime($r['tanggal_bayar'])) ?> | <?= $r['metode_pembayaran'] ?></div>
                    </div>
                    <div class="text-end">
                        <div class="small fw-bold">Rp <?= number_format($r['jumlah'], 0, ',', '.') ?></div>
                        <span class="badge <?= $r['status'] == 'valid' ? 'bg-success' : ($r['status'] == 'pending' ? 'bg-warning' : 'bg-danger') ?> rounded-pill">
                            <?= $r['status'] == 'valid' ? '✓ Lunas' : ($r['status'] == 'pending' ? '⏳ Pending' : '✗ Ditolak') ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
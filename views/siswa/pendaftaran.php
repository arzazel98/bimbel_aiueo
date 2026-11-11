<?php
$page_title = 'Pendaftaran Kelas';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['siswa']);

$db = getDB();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kelas_id'])) {
    $kelas_id = $_POST['kelas_id'];
    
    // Check if already registered
    $check = $db->prepare("SELECT id FROM pendaftaran WHERE siswa_id = :siswa_id AND kelas_id = :kelas_id");
    $check->execute(['siswa_id' => $user_id, 'kelas_id' => $kelas_id]);
    
    if ($check->fetch()) {
        $error = "Anda sudah mendaftar kelas ini!";
    } else {
        // Cek kuota kelas
        $kuota = $db->prepare("SELECT k.kuota, COUNT(p.id) as terdaftar 
                               FROM kelas k 
                               LEFT JOIN pendaftaran p ON k.id = p.kelas_id AND p.status_pendaftaran = 'disetujui'
                               WHERE k.id = :kelas_id
                               GROUP BY k.id");
        $kuota->execute(['kelas_id' => $kelas_id]);
        $data_kuota = $kuota->fetch(PDO::FETCH_ASSOC);
        
        if ($data_kuota && $data_kuota['terdaftar'] >= $data_kuota['kuota']) {
            $error = "Maaf, kuota kelas sudah penuh!";
        } else {
            $invoice = "INV-" . date('Ymd') . "-" . rand(1000, 9999);
            $stmt = $db->prepare("INSERT INTO pendaftaran (siswa_id, kelas_id, tanggal_daftar, invoice_number, status_pendaftaran) 
                                  VALUES (:siswa_id, :kelas_id, :tanggal, :invoice, 'pending')");
            
            if ($stmt->execute([
                'siswa_id' => $user_id,
                'kelas_id' => $kelas_id,
                'tanggal' => date('Y-m-d'),
                'invoice' => $invoice
            ])) {
                // Ambil ID pendaftaran yang baru dibuat
                $pendaftaran_id = $db->lastInsertId();
                
                // Ambil harga kelas
                $harga = $db->prepare("SELECT harga_per_bulan FROM kelas WHERE id = :id");
                $harga->execute(['id' => $kelas_id]);
                $harga_kelas = $harga->fetch(PDO::FETCH_ASSOC);
                
                // Langsung buat record pembayaran
                $bayar = $db->prepare("INSERT INTO pembayaran (pendaftaran_id, siswa_id, jumlah, tanggal_bayar, metode_pembayaran, status) 
                                       VALUES (:pendaftaran_id, :siswa_id, :jumlah, :tanggal, 'transfer', 'pending')");
                $bayar->execute([
                    'pendaftaran_id' => $pendaftaran_id,
                    'siswa_id' => $user_id,
                    'jumlah' => $harga_kelas['harga_per_bulan'],
                    'tanggal' => date('Y-m-d')
                ]);
                
                $message = "Pendaftaran berhasil! Silakan tunggu verifikasi pembayaran dari admin.";
            } else {
                $error = "Pendaftaran gagal, silakan coba lagi!";
            }
        }
    }
}

// Get all available classes (belum didaftar, kuota masih tersedia, dan aktif)
$kelas_tersedia = $db->prepare("SELECT k.*, 
                                 (SELECT COUNT(*) FROM pendaftaran WHERE kelas_id = k.id AND status_pendaftaran = 'disetujui') as terdaftar
                                 FROM kelas k 
                                 WHERE k.status = 'aktif' 
                                 AND k.id NOT IN (SELECT kelas_id FROM pendaftaran WHERE siswa_id = :siswa_id)
                                 AND (SELECT COUNT(*) FROM pendaftaran WHERE kelas_id = k.id AND status_pendaftaran = 'disetujui') < k.kuota
                                 ORDER BY k.nama_kelas");
$kelas_tersedia->execute(['siswa_id' => $user_id]);
$kelas_list = $kelas_tersedia->fetchAll(PDO::FETCH_ASSOC);

// Get classes that student already registered (pending or approved)
$kelas_didaftar = $db->prepare("SELECT k.id, k.nama_kelas, k.mata_pelajaran, p.status_pendaftaran
                                FROM pendaftaran p 
                                JOIN kelas k ON p.kelas_id = k.id 
                                WHERE p.siswa_id = :siswa_id");
$kelas_didaftar->execute(['siswa_id' => $user_id]);
$kelas_sudah = $kelas_didaftar->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Pendaftaran Kelas</h5>
    <p class="text-muted small">Pilih kelas yang ingin Anda ikuti</p>
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

<!-- Kelas yang Sudah Didftar -->
<?php if(!empty($kelas_sudah)): ?>
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-check-circle text-success me-2"></i>Kelas yang Sudah Anda Daftar</h6>
    </div>
    <div class="card-body">
        <?php foreach($kelas_sudah as $ks): ?>
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <div>
                    <strong><?= htmlspecialchars($ks['mata_pelajaran']) ?></strong>
                    <div class="small text-muted"><?= htmlspecialchars($ks['nama_kelas']) ?></div>
                </div>
                <span class="badge <?= $ks['status_pendaftaran'] == 'disetujui' ? 'bg-success' : 'bg-warning' ?> rounded-pill">
                    <?= $ks['status_pendaftaran'] == 'disetujui' ? 'Aktif' : 'Menunggu Verifikasi' ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Kelas yang Tersedia -->
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="fw-bold mb-0"><i class="fas fa-book-open text-primary me-2"></i>Kelas Tersedia</h6>
    </div>
    <div class="card-body">
        <?php if(empty($kelas_list)): ?>
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                <p class="text-muted small mb-0">Tidak ada kelas yang tersedia saat ini.</p>
                <p class="text-muted small">Anda sudah mendaftar semua kelas atau kuota sudah penuh.</p>
            </div>
        <?php else: ?>
            <?php foreach($kelas_list as $k): ?>
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <strong><?= htmlspecialchars($k['mata_pelajaran']) ?></strong>
                    <div class="small text-muted"><?= htmlspecialchars($k['nama_kelas']) ?></div>
                    <div class="small text-muted mt-1">
                        <span class="badge bg-primary rounded-pill">Kuota: <?= $k['terdaftar'] ?>/<?= $k['kuota'] ?></span>
                        <span class="badge bg-success rounded-pill">Rp <?= number_format($k['harga_per_bulan'], 0, ',', '.') ?>/bulan</span>
                    </div>
                </div>
                <form method="POST" onsubmit="return confirm('Yakin ingin mendaftar kelas <?= htmlspecialchars($k['nama_kelas']) ?>?')">
                    <input type="hidden" name="kelas_id" value="<?= $k['id'] ?>">
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm">
                        <i class="fas fa-plus"></i> Daftar
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
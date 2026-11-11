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
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

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
                             JOIN guru_kelas gk ON gk.kelas_id = k.id
                             JOIN users u ON u.id = gk.guru_id
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

<style>
    /* Pasang pondasi Flexbox pada pembungkus HP (entah itu .phone atau .mobile-frame) */
    .phone, .mobile-frame, body > div.container-fluid {
        height: 740px !important;
        max-height: 740px !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        position: relative !important;
    }

    /* Paksa area konten ini mengambil semua sisa ruang yang ada, KECUALI jatah menu bawah */
    .app-body-wrapper {
        flex: 1 !important;
        overflow-y: auto !important; /* Hanya bagian ini yang boleh nge-scroll */
        padding: 15px 5px 20px 5px !important;
    }

    /* Sembunyikan scrollbar bawaan browser agar mirip aplikasi native hp */
    .app-body-wrapper::-webkit-scrollbar {
        width: 4px;
    }
    .app-body-wrapper::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    /* Trik jitu: Tangkap elemen footer/menu bawaan dari layout/footer.php dan kunci mati di paling bawah */
    footer, nav, .navbar, .bottom-nav, [class*="footer"], [class*="nav"] {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        width: 100% !important;
        z-index: 9999 !important;
        background-color: #ffffff !important; /* Beri background putih biar konten di belakangnya ga nerawang */
        box-shadow: 0 -4px 10px rgba(0,0,0,0.08) !important; /* Efek bayangan halus di atas menu */
    }
</style>

<div class="app-body-wrapper">

    <div class="mb-4">
        <h4 class="fw-bold">Halo, <?= htmlspecialchars($siswa['nama_lengkap']) ?>!</h4>
        <p class="text-muted small">Dashboard Siswa Bimbel AIUEO</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100px;">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <small class="text-white opacity-75" style="font-size: 11px;">Kelas Diikuti</small>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <h2 class="mb-0 fw-bold text-white" style="font-size: 32px;"><?= count($kelas_list) ?></h2>
                        <i class="fas fa-book-open text-white opacity-50" style="font-size: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); min-height: 100px;">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <small class="text-white opacity-75" style="font-size: 11px;">Tagihan</small>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <h5 class="mb-0 fw-bold text-white" style="font-size: 14px;">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></h5>
                        <i class="fas fa-credit-card text-white opacity-50" style="font-size: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($pending_list)): ?>
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-warning bg-opacity-10">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-clock text-warning fs-5"></i>
                <div>
                    <strong class="small">Pendaftaran Menunggu Konfirmasi</strong>
                    <p class="small text-muted mb-0">Anda memiliki <?= count($pending_list) ?> pendaftaran yang sedang diproses admin.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($tagihan_list)): ?>
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-danger bg-opacity-10">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-money-bill-wave text-danger fs-5"></i>
                <div class="flex-grow-1">
                    <strong class="small">Ada Tagihan Belum Dibayar</strong>
                    <p class="small text-muted mb-0">Total Rp <?= number_format($total_tagihan, 0, ',', '.') ?></p>
                </div>
                <a href="pembayaran.php" class="btn btn-sm btn-danger rounded-pill">Bayar</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Jadwal Hari Ini (<?= $today_id ?>)</h6>
        </div>
        <div class="card-body">
            <?php if(empty($jadwal_list)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-2"></i>
                    <p class="text-muted small mb-0">Tidak ada jadwal untuk hari ini</p>
                    <p class="text-muted small">Selamat beristirahat! 🎉</p>
                </div>
            <?php else: ?>
                <?php foreach($jadwal_list as $j): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <strong><?= htmlspecialchars($j['mata_pelajaran']) ?></strong>
                            <div class="small text-muted">
                                <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?> | 
                                Guru: <?= htmlspecialchars($j['guru_name']) ?>
                            </div>
                            <div class="small text-muted">Ruang: <?= $j['ruangan'] ?? '-' ?></div>
                        </div>
                        <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($j['nama_kelas']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm mb-5">
        <div class="card-header bg-white border-0 pt-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-chalkboard text-primary me-2"></i>Kelas Saya</h6>
        </div>
        <div class="card-body">
            <?php if(empty($kelas_list)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-book-open fa-3x text-muted mb-2"></i>
                    <p class="text-muted small mb-0">Belum ada kelas yang diikuti</p>
                    <a href="pendaftaran.php" class="btn btn-sm btn-primary rounded-pill mt-2">Daftar Kelas Sekarang</a>
                </div>
            <?php else: ?>
                <?php foreach($kelas_list as $k): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <strong><?= htmlspecialchars($k['mata_pelajaran']) ?></strong>
                            <div class="small text-muted"><?= htmlspecialchars($k['nama_kelas']) ?></div>
                        </div>
                        <span class="badge <?= $k['status_bayar'] == 'valid' ? 'bg-success' : 'bg-warning' ?> rounded-pill">
                            <?= $k['status_bayar'] == 'valid' ? 'Lunas' : 'Belum Bayar' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div> <?php 
// File ini berisi bottom menu navigasi kamu. 
// Aturan CSS di atas otomatis mendeteksi elemen di dalam sini dan menempelkannya secara permanen di bawah frame HP.
require_once '../layout/footer.php'; 
?>
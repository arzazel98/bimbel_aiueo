<?php
$page_title = 'Profil Saya';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['siswa']);

$db = getDB();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($nama_lengkap) && !empty($email)) {
        $stmt = $db->prepare("UPDATE users SET nama_lengkap = :nama, nomor_telepon = :telp, alamat = :alamat, email = :email WHERE id = :id");
        if ($stmt->execute([
            'nama' => $nama_lengkap,
            'telp' => $nomor_telepon,
            'alamat' => $alamat,
            'email' => $email,
            'id' => $user_id
        ])) {
            $_SESSION['user'] = $nama_lengkap;
            $message = "Profil berhasil diperbarui!";
            // Refresh data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Gagal memperbarui profil!";
        }
    } else {
        $error = "Nama lengkap dan email wajib diisi!";
    }
}

require_once '../layout/header.php';
?>

<div class="mb-4">
    <h5 class="fw-bold mb-0">Profil Saya</h5>
    <p class="text-muted small">Kelola informasi akun Anda</p>
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

<div class="row g-4 mb-4">
    <!-- Left Column: Profile Avatar & Stats -->
    <div class="col-12 col-lg-4">
        <!-- Profile Card -->
        <div class="card border-0 rounded-4 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-center text-white p-4">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 86px; height: 86px;">
                    <i class="fas fa-user-graduate fa-3x" style="color: #667eea;"></i>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h5>
                <small class="opacity-75 d-block mb-2">@<?= htmlspecialchars($siswa['username']) ?></small>
                <span class="badge bg-light text-dark rounded-pill px-3 py-1">Siswa Aktif</span>
            </div>
        </div>

        <!-- Informasi Akun -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <i class="fas fa-book-open text-primary fs-4 mb-1"></i>
                        <div class="fw-bold small">Kelas</div>
                        <div class="small text-muted" id="totalKelas">-</div>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="text-center flex-grow-1">
                        <i class="fas fa-credit-card text-success fs-4 mb-1"></i>
                        <div class="fw-bold small">Status</div>
                        <div class="small text-muted">Aktif</div>
                    </div>
                    <div class="vr opacity-25"></div>
                    <div class="text-center flex-grow-1">
                        <i class="fas fa-calendar-alt text-warning fs-4 mb-1"></i>
                        <div class="fw-bold small">Bergabung</div>
                        <div class="small text-muted"><?= date('M Y', strtotime($siswa['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Edit Profile Form -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-edit text-primary me-2"></i>Perbarui Data Profil</h6>
                <small class="text-muted">Pastikan data kontak dan alamat Anda selalu akurat.</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Username</label>
                            <input type="text" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($siswa['username'] ?? '') ?>" disabled>
                            <small class="text-muted" style="font-size: 11px;">Username tidak dapat diubah</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Member Sejak</label>
                            <input type="text" class="form-control rounded-3 bg-light" value="<?= !empty($siswa['created_at']) ? date('d F Y', strtotime($siswa['created_at'])) : '-' ?>" disabled>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-control rounded-3" value="<?= htmlspecialchars($siswa['nama_lengkap'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Email *</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?= htmlspecialchars($siswa['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="nomor_telepon" class="form-control rounded-3" value="<?= htmlspecialchars($siswa['nomor_telepon'] ?? '') ?>" placeholder="Contoh: 08123456789">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Alamat Rumah</label>
                            <textarea name="alamat" class="form-control rounded-3" rows="3" placeholder="Masukkan alamat lengkap"><?= htmlspecialchars($siswa['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Get total kelas from database via AJAX
fetch('../../controllers/get_kelas_count.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('totalKelas').innerText = data.total || '0';
    })
    .catch(err => console.log(err));
</script>

<?php require_once '../layout/footer.php'; ?>
<?php
$page_title = 'Manajemen Kelas';
require_once '../../config/config.php';
require_once '../../config/database.php';
checkRole(['admin']);

$db = getDB();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $stmt = $db->prepare("INSERT INTO kelas (nama_kelas, mata_pelajaran, deskripsi, kuota, harga_per_bulan) 
                                  VALUES (:nama, :mapel, :deskripsi, :kuota, :harga)");
            if ($stmt->execute([
                'nama' => $_POST['nama_kelas'],
                'mapel' => $_POST['mata_pelajaran'],
                'deskripsi' => $_POST['deskripsi'],
                'kuota' => $_POST['kuota'],
                'harga' => $_POST['harga_per_bulan']
            ])) {
                $message = "Kelas berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan kelas!";
            }
        } elseif ($_POST['action'] == 'edit') {
            $stmt = $db->prepare("UPDATE kelas SET nama_kelas = :nama, mata_pelajaran = :mapel, deskripsi = :deskripsi, 
                                  kuota = :kuota, harga_per_bulan = :harga WHERE id = :id");
            if ($stmt->execute([
                'id' => $_POST['id'],
                'nama' => $_POST['nama_kelas'],
                'mapel' => $_POST['mata_pelajaran'],
                'deskripsi' => $_POST['deskripsi'],
                'kuota' => $_POST['kuota'],
                'harga' => $_POST['harga_per_bulan']
            ])) {
                $message = "Kelas berhasil diupdate!";
            }
        } elseif ($_POST['action'] == 'delete') {
            $stmt = $db->prepare("DELETE FROM kelas WHERE id = :id");
            if ($stmt->execute(['id' => $_POST['id']])) {
                $message = "Kelas berhasil dihapus!";
            }
        } elseif ($_POST['action'] == 'assign_guru') {
            $stmt = $db->prepare("INSERT INTO guru_kelas (guru_id, kelas_id, tahun_ajaran) VALUES (:guru_id, :kelas_id, :tahun)");
            if ($stmt->execute([
                'guru_id' => $_POST['guru_id'],
                'kelas_id' => $_POST['kelas_id'],
                'tahun' => date('Y') . '/' . (date('Y')+1)
            ])) {
                $message = "Guru berhasil ditugaskan!";
            } else {
                $error = "Gagal menugaskan guru!";
            }
        }
    }
}

// Get all classes
$kelas = $db->query("SELECT k.*, 
                     (SELECT COUNT(*) FROM pendaftaran WHERE kelas_id = k.id AND status_pendaftaran = 'disetujui') as terdaftar 
                     FROM kelas k ORDER BY k.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get all teachers
$guru = $db->query("SELECT id, nama_lengkap FROM users WHERE role = 'guru' AND status = 'aktif'")->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Manajemen Kelas</h5>
    <button class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addKelasModal">
        <i class="fas fa-plus"></i> Kelas Baru
    </button>
</div>

<?php if($message): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php foreach($kelas as $k): ?>
<div class="card border-0 rounded-4 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($k['nama_kelas']) ?></h6>
                <small class="text-muted"><?= htmlspecialchars($k['mata_pelajaran']) ?></small>
                <div class="mt-2">
                    <span class="badge bg-primary rounded-pill">Kuota: <?= $k['terdaftar'] ?>/<?= $k['kuota'] ?></span>
                    <span class="badge bg-success rounded-pill">Rp <?= number_format($k['harga_per_bulan'], 0, ',', '.') ?></span>
                </div>
                <p class="small text-muted mt-2 mb-0"><?= htmlspecialchars($k['deskripsi']) ?></p>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="editKelas(<?= htmlspecialchars(json_encode($k)) ?>)"><i class="fas fa-edit"></i> Edit</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignGuruModal" onclick="setKelasId(<?= $k['id'] ?>)"><i class="fas fa-user-plus"></i> Assign Guru</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteKelas(<?= $k['id'] ?>)"><i class="fas fa-trash"></i> Hapus</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal Add Kelas -->
<div class="modal fade" id="addKelasModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header border-0">
                    <h6 class="fw-bold">Tambah Kelas Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Mata Pelajaran</label>
                        <input type="text" name="mata_pelajaran" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label small">Kuota</label>
                            <input type="number" name="kuota" class="form-control rounded-3" value="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Harga/Bulan</label>
                            <input type="number" name="harga_per_bulan" class="form-control rounded-3" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Assign Guru -->
<div class="modal fade" id="assignGuruModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <form method="POST">
                <input type="hidden" name="action" value="assign_guru">
                <input type="hidden" name="kelas_id" id="assign_kelas_id">
                <div class="modal-header border-0">
                    <h6 class="fw-bold">Assign Guru ke Kelas</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Pilih Guru</label>
                        <select name="guru_id" class="form-select rounded-3" required>
                            <option value="">Pilih Guru...</option>
                            <?php foreach($guru as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editKelas(kelas) {
    // Implement edit functionality
    alert('Edit kelas: ' + kelas.nama_kelas);
}

function deleteKelas(id) {
    if(confirm('Yakin ingin menghapus kelas ini?')) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function setKelasId(id) {
    document.getElementById('assign_kelas_id').value = id;
}
</script>

<?php require_once '../layout/footer.php'; ?>
<?php
// controllers/get_kelas_count.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['total' => 0]);
    exit();
}

require_once '../config/database.php';
$db = getDB();
if (!$db) {
    echo json_encode(['total' => 0]);
    exit();
}

try {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM pendaftaran WHERE siswa_id = :siswa_id AND status_pendaftaran = 'disetujui'");
    $stmt->execute(['siswa_id' => $_SESSION['user_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['total' => (int)($res['total'] ?? 0)]);
} catch (Exception $e) {
    echo json_encode(['total' => 0]);
}
exit();

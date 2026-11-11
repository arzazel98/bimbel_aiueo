<?php
// update_status.php
require_once 'config/database.php';
session_start();

$db = getDB();
$id = $_GET['id'] ?? 0;

if($id) {
    // Update status pendaftaran menjadi disetujui
    $stmt = $db->prepare("UPDATE pendaftaran SET status_pendaftaran = 'disetujui' WHERE id = :id");
    if($stmt->execute(['id' => $id])) {
        echo "<script>alert('Status berhasil diubah menjadi DISETUJUI!'); window.location.href='cek_status_cepat.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah status!'); window.location.href='cek_status_cepat.php';</script>";
    }
} else {
    header("Location: cek_status_cepat.php");
}
?>
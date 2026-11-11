<?php
// index.php
session_start();

// Debug: lihat session yang tersimpan
error_log("Session di index.php: " . print_r($_SESSION, true));

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    
    switch($role) {
        case 'admin':
            header("Location: views/admin/dashboard.php");
            break;
        case 'guru':
            header("Location: views/guru/dashboard.php");
            break;
        case 'siswa':
            header("Location: views/siswa/dashboard.php");
            break;
        default:
            header("Location: views/auth/login.php");
    }
} else {
    header("Location: views/auth/login.php");
}
exit();
?>
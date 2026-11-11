<?php
// config/config.php
// FUNGSI: Untuk konfigurasi aplikasi (session, helper functions, dll)

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/bimbel_aiueo/');
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Bimbel AIUEO');
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function getCurrentRole() {
    return $_SESSION['role'] ?? null;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function checkRole($allowedRoles = []) {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'views/auth/login.php');
    }
    if (!empty($allowedRoles) && !in_array(getCurrentRole(), $allowedRoles)) {
        redirect(BASE_URL . 'index.php');
    }
}
?>
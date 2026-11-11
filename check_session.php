<?php
// check_session.php
session_start();
echo "<h2>Session Debug</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Reset Session?</h2>";
echo "<a href='?reset=1'>Klik untuk reset session</a>";

if (isset($_GET['reset'])) {
    session_destroy();
    echo "<p style='color:green'>Session telah direset! Silakan <a href='views/auth/login.php'>login kembali</a></p>";
}
?>
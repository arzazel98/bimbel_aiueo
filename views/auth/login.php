<?php
// views/auth/login.php

session_start();

// hapus session lama
if (isset($_SESSION['user_id'])) {
    session_destroy();
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require_once '../../config/database.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {

        try {

            $db = getDB();

            if (!$db) {
                throw new Exception("Tidak dapat terhubung ke MySQL. Pastikan Laragon sudah berjalan (klik 'Start All' di Laragon).");
            }

            $stmt = $db->prepare("
                SELECT * 
                FROM users 
                WHERE username = :username 
                AND status = 'aktif'
            ");

            $stmt->execute([
                'username' => $username
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {

                $valid = false;

                // password hash
                if (password_verify($password, $user['password'])) {
                    $valid = true;
                }

                // md5
                elseif (md5($password) == $user['password']) {
                    $valid = true;
                }

                // plain text
                elseif ($password == $user['password']) {
                    $valid = true;
                }

                if ($valid) {

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user'] = $user['nama_lengkap'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];

                    switch($user['role']) {

                        case 'admin':
                            header("Location: ../../views/admin/dashboard.php");
                            break;

                        case 'guru':
                            header("Location: ../../views/guru/dashboard.php");
                            break;

                        case 'siswa':
                            header("Location: ../../views/siswa/dashboard.php");
                            break;

                        default:
                            header("Location: ../../index.php");
                    }

                    exit();

                } else {

                    $error = "Password salah!";

                }

            } else {

                $error = "Username tidak ditemukan!";

            }

        } catch (PDOException $e) {

            $error = "Database Error : " . $e->getMessage();

        } catch (Throwable $e) {

            $error = "Terjadi kesalahan : " . $e->getMessage();

        }

    } else {

        $error = "Harap isi username dan password!";

    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Bimbel AIUEO</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --brand-primary: #FF2F7D;
            --brand-secondary: #7928CA;
            --brand-gradient: linear-gradient(135deg, #FF2F7D 0%, #FF6090 100%);
            --brand-hero: linear-gradient(145deg, #FF2F7D 0%, #7928CA 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: #1e293b;
        }

        .auth-container {
            width: 100%;
            max-width: 980px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 60px -15px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* Hero Banner Left Column */
        .auth-hero {
            background: var(--brand-hero);
            padding: 50px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            pointer-events: none;
        }

        .auth-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .hero-logo-box {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 10px;
            margin-bottom: 24px;
        }

        .hero-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .hero-feature-item {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
            background: rgba(255, 255, 255, 0.12);
            padding: 12px 18px;
            border-radius: 16px;
            backdrop-filter: blur(8px);
            font-size: 13.5px;
            font-weight: 500;
        }

        .hero-feature-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
            color: #ffe4e6;
        }

        /* Form Column */
        .auth-form-side {
            padding: 48px 44px;
        }

        @media (max-width: 575.98px) {
            .auth-form-side {
                padding: 32px 20px;
            }
        }

        .form-floating-custom {
            position: relative;
            margin-bottom: 18px;
        }

        .form-floating-custom .input-icon {
            position: absolute;
            top: 50%;
            left: 18px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            transition: all 0.2s ease;
            pointer-events: none;
        }

        .form-floating-custom .form-control {
            padding: 14px 18px 14px 48px;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            background-color: #f8fafc;
        }

        .form-floating-custom .form-control:focus {
            background-color: #ffffff;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(255, 47, 125, 0.12);
        }

        .form-floating-custom .form-control:focus + .input-icon {
            color: var(--brand-primary);
        }

        .toggle-password-btn {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: color 0.2s ease;
        }

        .toggle-password-btn:hover {
            color: #475569;
        }

        .btn-submit {
            background: var(--brand-gradient);
            border: none;
            color: white;
            font-weight: 700;
            font-size: 15px;
            padding: 14px;
            border-radius: 16px;
            width: 100%;
            box-shadow: 0 8px 20px -4px rgba(255, 47, 125, 0.45);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #e0236a 0%, #eb4d7f 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -4px rgba(255, 47, 125, 0.55);
            color: white;
        }

        .demo-accounts-pill {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="row g-0">
        <!-- Left Banner Column -->
        <div class="col-lg-5 d-none d-lg-flex auth-hero">
            <div>
                <div class="hero-logo-box">
                    <img src="logo.png" alt="Logo Bimbel AIUEO" onerror="this.src='logo.jpeg'">
                </div>
                <h3 class="fw-bold mb-2">Bimbel AIUEO</h3>
                <p class="opacity-80 small mb-4">Sistem Manajemen Pembelajaran & Portal Akademik Modern Terpadu.</p>

                <div class="mt-4">
                    <div class="hero-feature-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Program belajar terstruktur & terarah</span>
                    </div>
                    <div class="hero-feature-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Pantau jadwal & presensi secara real-time</span>
                    </div>
                    <div class="hero-feature-item">
                        <i class="fas fa-shield-heart"></i>
                        <span>Pembayaran aman & transparan</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top border-white border-opacity-25 small opacity-75">
                &copy; <?= date('Y') ?> Bimbel AIUEO. All Rights Reserved.
            </div>
        </div>

        <!-- Right Form Column -->
        <div class="col-12 col-lg-7 auth-form-side">
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="d-lg-none hero-logo-box mb-0" style="width: 44px; height: 44px; border-radius: 12px;">
                        <img src="logo.png" alt="Logo" onerror="this.src='logo.jpeg'">
                    </div>
                    <h3 class="fw-bold text-dark mb-0">Selamat Datang! 👋</h3>
                </div>
                <p class="text-muted small">Silakan masukkan username dan password Anda untuk masuk ke sistem.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 py-3 px-3 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle fs-5"></i>
                        <span class="small fw-semibold"><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-floating-custom">
                    <input 
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Masukkan Username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="off"
                        required
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="form-floating-custom">
                    <input 
                        type="password"
                        name="password"
                        id="passwordInput"
                        class="form-control"
                        placeholder="Masukkan Password"
                        autocomplete="off"
                        required
                    >
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="toggle-password-btn" id="togglePasswordBtn" aria-label="Lihat Password">
                        <i class="far fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-submit mb-4">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i> Masuk Sekarang
                </button>

                <p class="text-center text-muted small mb-4">
                    Belum punya akun? 
                    <a href="register.php" class="text-decoration-none fw-bold" style="color: var(--brand-primary);">Daftar Siswa Baru</a>
                </p>

                <div class="demo-accounts-pill">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-info-circle text-primary"></i>
                        <strong style="font-size: 11.5px;">Bantuan Akun Demo:</strong>
                    </div>
                    <div class="d-flex flex-column gap-1 text-muted" style="font-size: 11px;">
                        <div>&bull; <strong>Admin</strong>: username <code>admin</code> | password <code>admin123</code></div>
                        <div>&bull; <strong>Guru</strong>: username <code>budi.guru</code> / <code>ani.guru</code> | password <code>guru123</code></div>
                        <div>&bull; <strong>Siswa</strong>: username <code>bibi</code> | password <code>123456</code></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Password Visibility
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    }
</script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require_once '../../config/database.php';

    $username = trim($_POST['username'] ?? '');
    $email_or_phone = trim($_POST['email_or_phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // otomatis email / nomor
    $email = (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL))
        ? $email_or_phone
        : $username . '@gmail.com';

    $nomor_telepon = (!filter_var($email_or_phone, FILTER_VALIDATE_EMAIL))
        ? $email_or_phone
        : '';

    $nama_lengkap = $username;
    $alamat = '';

    // validasi
    if (empty($username) || empty($email_or_phone) || empty($password)) {

        $error = "Harap isi semua field!";

    } elseif (strlen($password) < 6) {

        $error = "Password minimal 6 karakter!";

    } else {

        try {

            $db = getDB();

            if (!$db) {
                throw new Exception("Tidak dapat terhubung ke MySQL. Pastikan Laragon sudah berjalan (klik 'Start All' di Laragon).");
            }

            // cek user
            $stmt = $db->prepare("
                SELECT id 
                FROM users 
                WHERE username = :username 
                OR email = :email
            ");

            $stmt->execute([
                'username' => $username,
                'email' => $email
            ]);

            if ($stmt->fetch()) {

                $error = "Username atau email sudah terdaftar!";

            } else {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("
                    INSERT INTO users
                    (
                        username,
                        password,
                        email,
                        nama_lengkap,
                        nomor_telepon,
                        alamat,
                        role,
                        status
                    )
                    VALUES
                    (
                        :username,
                        :password,
                        :email,
                        :nama_lengkap,
                        :nomor_telepon,
                        :alamat,
                        'siswa',
                        'aktif'
                    )
                ");

                $insert = $stmt->execute([
                    'username' => $username,
                    'password' => $hashed_password,
                    'email' => $email,
                    'nama_lengkap' => $nama_lengkap,
                    'nomor_telepon' => $nomor_telepon,
                    'alamat' => $alamat
                ]);

                if ($insert) {

                    $success = "Pendaftaran berhasil! Mengalihkan ke login...";

                    echo '
                    <meta http-equiv="refresh" content="2;url=login.php">
                    ';

                } else {

                    $error = "Pendaftaran gagal!";

                }
            }

        } catch (PDOException $e) {

            $error = "Database Error : " . $e->getMessage();

        } catch (Throwable $e) {

            $error = "Terjadi kesalahan : " . $e->getMessage();

        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Siswa — Bimbel AIUEO</title>

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
                <p class="opacity-80 small mb-4">Mulai Perjalanan Belajar Anak dengan Metode Interaktif & Menyenangkan.</p>

                <div class="mt-4">
                    <div class="hero-feature-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Pendaftaran cepat dalam hitungan detik</span>
                    </div>
                    <div class="hero-feature-item">
                        <i class="fas fa-book-reader"></i>
                        <span>Akses penuh jadwal & modul kelas</span>
                    </div>
                    <div class="hero-feature-item">
                        <i class="fas fa-star"></i>
                        <span>Guru pengajar profesional & berdedikasi</span>
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
                    <h3 class="fw-bold text-dark mb-0">Daftar Akun Baru 🚀</h3>
                </div>
                <p class="text-muted small">Buat akun siswa Anda untuk mulai memilih kelas dan jadwal belajar.</p>
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

            <?php if(!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 py-3 px-3 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span class="small fw-semibold"><?= htmlspecialchars($success) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-floating-custom">
                    <input 
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Nama Pengguna (Username)"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="off"
                        required
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="form-floating-custom">
                    <input 
                        type="text"
                        name="email_or_phone"
                        class="form-control"
                        placeholder="Email atau Nomor WhatsApp / Telepon"
                        value="<?= htmlspecialchars($_POST['email_or_phone'] ?? '') ?>"
                        autocomplete="off"
                        required
                    >
                    <i class="fas fa-envelope input-icon"></i>
                </div>

                <div class="form-floating-custom">
                    <input 
                        type="password"
                        name="password"
                        id="registerPassword"
                        class="form-control"
                        placeholder="Buat Password (Minimal 6 karakter)"
                        autocomplete="off"
                        required
                    >
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="toggle-password-btn" id="toggleRegPasswordBtn" aria-label="Lihat Password">
                        <i class="far fa-eye" id="toggleRegPasswordIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-submit mb-4">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </button>

                <p class="text-center text-muted small mb-0">
                    Sudah memiliki akun? 
                    <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--brand-primary);">Masuk di Sini</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Password Visibility
    const toggleRegBtn = document.getElementById('toggleRegPasswordBtn');
    const regPasswordInput = document.getElementById('registerPassword');
    const toggleRegIcon = document.getElementById('toggleRegPasswordIcon');

    if (toggleRegBtn && regPasswordInput && toggleRegIcon) {
        toggleRegBtn.addEventListener('click', function() {
            if (regPasswordInput.type === 'password') {
                regPasswordInput.type = 'text';
                toggleRegIcon.classList.remove('fa-eye');
                toggleRegIcon.classList.add('fa-eye-slash');
            } else {
                regPasswordInput.type = 'password';
                toggleRegIcon.classList.remove('fa-eye-slash');
                toggleRegIcon.classList.add('fa-eye');
            }
        });
    }
</script>
</body>
</html>
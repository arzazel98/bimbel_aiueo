<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'siswa';
$username = $_SESSION['username'] ?? '';
$fullName = $_SESSION['user'] ?? 'Pengguna';
$current_page = basename($_SERVER['PHP_SELF']);

// Role metadata
$roleData = [
    'admin' => ['label' => 'Administrator', 'badge' => 'bg-danger text-white', 'icon' => 'fa-shield-halved'],
    'guru'  => ['label' => 'Guru Pengajar', 'badge' => 'bg-success text-white', 'icon' => 'fa-chalkboard-user'],
    'siswa' => ['label' => 'Siswa Bimbel',  'badge' => 'bg-primary text-white', 'icon' => 'fa-user-graduate']
][$role] ?? ['label' => ucfirst($role), 'badge' => 'bg-secondary text-white', 'icon' => 'fa-user'];

// Menu items based on role
$menuItems = [];
if ($role === 'admin') {
    $menuItems = [
        ['url' => 'dashboard.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'],
        ['url' => 'kelas.php', 'icon' => 'fas fa-chalkboard', 'label' => 'Data Kelas'],
        ['url' => 'jadwal.php', 'icon' => 'fas fa-calendar-alt', 'label' => 'Jadwal Belajar'],
        ['url' => 'pembayaran.php', 'icon' => 'fas fa-receipt', 'label' => 'Verifikasi Bayar'],
        ['url' => 'presensi_monitoring.php', 'icon' => 'fas fa-user-check', 'label' => 'Presensi Monitoring'],
        ['url' => 'gaji_guru.php', 'icon' => 'fas fa-wallet', 'label' => 'Honor & Gaji Guru'],
        ['url' => 'laporan.php', 'icon' => 'fas fa-file-invoice', 'label' => 'Laporan & Rekap']
    ];
} elseif ($role === 'guru') {
    $menuItems = [
        ['url' => 'dashboard.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'],
        ['url' => 'presensi_guru.php', 'icon' => 'fas fa-fingerprint', 'label' => 'Presensi Mengajar'],
        ['url' => 'presensi_siswa.php', 'icon' => 'fas fa-users', 'label' => 'Presensi Siswa'],
        ['url' => 'laporan_honor.php', 'icon' => 'fas fa-money-bill-wave', 'label' => 'Laporan Honor']
    ];
} elseif ($role === 'siswa') {
    $menuItems = [
        ['url' => 'dashboard.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'],
        ['url' => 'pendaftaran.php', 'icon' => 'fas fa-graduation-cap', 'label' => 'Pendaftaran Kelas'],
        ['url' => 'jadwal.php', 'icon' => 'fas fa-calendar-days', 'label' => 'Jadwal Saya'],
        ['url' => 'pembayaran.php', 'icon' => 'fas fa-credit-card', 'label' => 'Pembayaran SPP'],
        ['url' => 'profil.php', 'icon' => 'fas fa-user-circle', 'label' => 'Profil Siswa']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Bimbel AIUEO' ?> — Portal Belajar</title>
    
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
            --sidebar-width: 260px;
            --topbar-height: 70px;
            --bg-body: #f4f6fa;
            --text-dark: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Layout Architecture */
        .app-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar Styling */
        .app-sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
        }

        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--brand-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            box-shadow: 0 8px 16px -4px rgba(255, 47, 125, 0.4);
            overflow: hidden;
            flex-shrink: 0;
        }

        .brand-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-info h6 {
            margin: 0;
            font-weight: 800;
            font-size: 17px;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-info small {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Sidebar Navigation Menu */
        .sidebar-menu-wrapper {
            flex: 1;
            padding: 18px 14px;
            overflow-y: auto;
        }

        .sidebar-heading {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #94a3b8;
            padding: 10px 14px 6px;
            margin-bottom: 4px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-radius: 12px;
            color: #64748b;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .sidebar-nav-link i {
            font-size: 17px;
            width: 22px;
            text-align: center;
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .sidebar-nav-link:hover {
            color: var(--brand-primary);
            background: rgba(255, 47, 125, 0.06);
            transform: translateX(3px);
        }

        .sidebar-nav-link:hover i {
            color: var(--brand-primary);
        }

        .sidebar-nav-link.active {
            color: #ffffff;
            background: var(--brand-gradient);
            box-shadow: 0 8px 20px -4px rgba(255, 47, 125, 0.45);
        }

        .sidebar-nav-link.active i {
            color: #ffffff;
        }

        /* Sidebar User Card */
        .sidebar-user {
            padding: 14px 16px;
            margin: 12px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
        }

        .sidebar-user-meta {
            overflow: hidden;
            flex: 1;
        }

        .sidebar-user-meta .name {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-meta .role {
            font-size: 11px;
            color: #64748b;
        }

        /* Main Content Wrapper */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Topbar Header */
        .app-topbar {
            height: var(--topbar-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-toggle-btn:hover {
            background: #f1f5f9;
            color: var(--brand-primary);
        }

        .page-header-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .page-header-subtitle {
            font-size: 12px;
            color: #64748b;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .date-badge {
            background: #f8fafc;
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-badge i {
            color: var(--brand-primary);
        }

        .user-dropdown-btn {
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-dropdown-btn:hover {
            background: #f8fafc;
        }

        /* Page Content Body */
        .app-body {
            flex: 1;
            padding: 24px;
        }

        /* Modern Card Enhancements */
        .card {
            border: 1px solid rgba(226, 232, 240, 0.7) !important;
            box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.04) !important;
            border-radius: 18px !important;
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.07) !important;
        }

        .card-header {
            background-color: transparent !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7) !important;
            padding: 16px 20px !important;
        }

        .rounded-4 {
            border-radius: 18px !important;
        }

        .rounded-3 {
            border-radius: 12px !important;
        }

        /* Modern Button & Input Styling */
        .btn {
            font-weight: 600;
            border-radius: 12px;
            padding: 9px 18px;
            font-size: 13.5px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--brand-gradient);
            border: none;
            box-shadow: 0 4px 14px rgba(255, 47, 125, 0.35);
        }

        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, #e0236a 0%, #eb4d7f 100%);
            box-shadow: 0 6px 18px rgba(255, 47, 125, 0.45);
            transform: translateY(-1px);
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 13.5px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(255, 47, 125, 0.12);
        }

        /* Table Styling */
        .table {
            vertical-align: middle;
        }

        .table th {
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
        }

        .table td {
            padding: 14px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Overlay for Mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            z-index: 1035;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }

            .app-sidebar.show {
                transform: translateX(0);
            }

            .app-main {
                margin-left: 0;
            }

            .app-topbar {
                padding: 0 16px;
            }

            .app-body {
                padding: 16px;
            }

            .date-badge {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="app-layout">
    <!-- Sidebar Navigation -->
    <aside class="app-sidebar" id="appSidebar">
        <!-- Brand / Logo -->
        <a href="dashboard.php" class="sidebar-brand">
            <div class="brand-logo-box">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="brand-info">
                <h6>Bimbel AIUEO</h6>
                <small>Learning Portal</small>
            </div>
        </a>

        <!-- Navigation Menu -->
        <div class="sidebar-menu-wrapper">
            <div class="sidebar-heading">Menu Utama</div>
            <nav class="nav flex-column">
                <?php foreach ($menuItems as $item): 
                    $isActive = ($current_page === $item['url']);
                ?>
                    <a href="<?= $item['url'] ?>" class="sidebar-nav-link <?= $isActive ? 'active' : '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-heading mt-4">Akun & Sistem</div>
            <nav class="nav flex-column">
                <a href="../../controllers/AuthController.php?action=logout" class="sidebar-nav-link text-danger">
                    <i class="fas fa-sign-out-alt text-danger"></i>
                    <span>Keluar (Logout)</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar User Footer -->
        <div class="sidebar-user">
            <div class="user-avatar-circle">
                <?= strtoupper(substr($fullName, 0, 1)) ?>
            </div>
            <div class="sidebar-user-meta">
                <div class="name"><?= htmlspecialchars($fullName) ?></div>
                <div class="role">
                    <span class="badge <?= $roleData['badge'] ?> rounded-pill" style="font-size: 9.5px; padding: 3px 8px;">
                        <?= $roleData['label'] ?>
                    </span>
                </div>
            </div>
            <a href="../../controllers/AuthController.php?action=logout" class="text-secondary p-1" title="Logout">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content Area -->
    <div class="app-main">
        <!-- Topbar Header -->
        <header class="app-topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h5 class="page-header-title"><?= $page_title ?? 'Dashboard' ?></h5>
                    <span class="page-header-subtitle d-none d-sm-inline">Bimbel AIUEO &bull; Portal <?= $roleData['label'] ?></span>
                </div>
            </div>

            <div class="topbar-right">
                <div class="date-badge">
                    <i class="fas fa-calendar-day"></i>
                    <span id="currentDateDisplay"><?= date('l, d M Y') ?></span>
                </div>

                <div class="dropdown">
                    <button class="user-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar-circle" style="width: 34px; height: 34px; font-size: 13px;">
                            <?= strtoupper(substr($fullName, 0, 1)) ?>
                        </div>
                        <span class="d-none d-md-inline fw-semibold small text-dark"><?= htmlspecialchars($fullName) ?></span>
                        <i class="fas fa-chevron-down text-muted" style="font-size: 11px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-4 mt-2 p-2" style="min-width: 200px;">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <div class="fw-bold small"><?= htmlspecialchars($fullName) ?></div>
                            <small class="text-muted" style="font-size: 11px;">@<?= htmlspecialchars($username) ?></small>
                        </li>
                        <?php if ($role === 'siswa'): ?>
                            <li><a class="dropdown-item rounded-3 py-2 small" href="profil.php"><i class="fas fa-user-circle me-2 text-primary"></i>Profil Saya</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item rounded-3 py-2 small text-danger" href="../../controllers/AuthController.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <main class="app-body">
            <div class="container-fluid p-0">

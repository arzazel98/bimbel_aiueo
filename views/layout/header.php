<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title><?= $page_title ?? 'Bimbel AIUEO' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #f0e8ec;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px;
        }
        
        /* Mobile Frame Utama */
        .mobile-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            min-height: 85vh;
            position: relative;
        }
        
        /* Header dengan gradasi pink */
        .app-header {
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            padding: 20px 20px;
            color: white;
            border-radius: 0 0 28px 28px;
        }
        
        /* Bottom Navigation */
        .bottom-nav {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            display: flex;
            justify-content: space-around;
            padding: 12px 16px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }
        
        .bottom-nav a {
            color: #aaa;
            text-decoration: none;
            font-size: 11px;
            text-align: center;
            transition: all 0.2s;
            flex: 1;
        }
        
        .bottom-nav a.active,
        .bottom-nav a:hover {
            color: #FF1493;
        }
        
        .bottom-nav i {
            font-size: 22px;
            display: block;
            margin-bottom: 4px;
        }
        
        /* Content Area */
        .content {
            padding: 20px 16px;
            padding-bottom: 80px;
            min-height: 70vh;
        }
        
        /* Logout Button */
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            border-radius: 30px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        /* Custom Scroll */
        ::-webkit-scrollbar {
            width: 4px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #FF69B4;
            border-radius: 10px;
        }
        
        /* Utility */
        .bg-purple {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
        
        .rounded-4 {
            border-radius: 20px !important;
        }
        
        .rounded-3 {
            border-radius: 16px !important;
        }
    </style>
</head>
<body>
<div class="mobile-container">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-graduation-cap fs-5"></i>
                <h6 class="mb-0 mt-1 fw-bold">Bimbel AIUEO</h6>
                <small class="opacity-75" style="font-size: 10px;"><?= ucfirst($_SESSION['role']) ?></small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-medium"><?= htmlspecialchars($_SESSION['user']) ?></span>
                <a href="../../controllers/AuthController.php?action=logout" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="content">
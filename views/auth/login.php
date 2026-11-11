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

    <title>Login - Bimbel AIUEO</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            background:#dcdcdc;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            font-family:'Poppins',sans-serif;
        }

        .phone{
            width:360px;
            height:740px;
            border-radius:35px;
            padding:25px;
            background:linear-gradient(180deg,#ff2f7d,#ff7aa2);
            box-shadow:0 15px 35px rgba(0,0,0,0.25);
            position:relative;
            overflow:hidden;
            display:flex;
            flex-direction:column;
        }

        .notif{
            width:100%;
            padding:12px;
            border-radius:15px;
            font-size:13px;
            color:white;
            text-align:center;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
            margin-top:10px;
        }

        .notif.error{
            background:#ff3b3b;
        }

        .logo{
            width:160px;
            height:160px;
            background:#f5f5f5;
            border-radius:50%;
            margin:60px auto 45px;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 4px 10px rgba(0,0,0,0.05);
            padding:25px;
        }

        .logo img{
            max-width:100%;
            max-height:100%;
            object-fit:contain;
        }

        .form input{
            width:100%;
            padding:16px 20px;
            margin-bottom:16px;
            border:none;
            border-radius:20px;
            background:#e3e7ee;
            font-size:15px;
            outline:none;
            color:#333;
        }

        .form input::placeholder{
            color:#8e9bb0;
        }

        .form button{
            width:100%;
            padding:16px;
            border-radius:30px;
            border:none;
            background:#ffb3c6;
            color:white;
            font-weight:600;
            font-size:15px;
            cursor:pointer;
            margin-top:5px;
            box-shadow:0 4px 10px rgba(0,0,0,0.05);
            transition:0.3s;
        }

        .form button:hover{
            opacity:0.9;
        }

        .switch{
            text-align:center;
            margin-top:22px;
            color:white;
            font-size:14px;
        }

        .switch a{
            color:#007bff;
            font-weight:600;
            text-decoration:none;
        }

        .divider{
            display:flex;
            align-items:center;
            margin:25px 0 20px;
            color:white;
            font-size:13px;
        }

        .divider span{
            flex:1;
            height:1px;
            background:rgba(255,255,255,0.6);
        }

        .divider p{
            margin:0 10px;
        }

        .social{
            display:flex;
            justify-content:center;
        }

        .google-btn{
            width:100%;
            border:none;
            border-radius:20px;
            background:white;
            padding:14px;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:12px;
            font-size:14px;
            font-weight:600;
            color:#444;
            cursor:pointer;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
            transition:0.3s;
        }

        .google-btn:hover{
            transform:translateY(-2px);
        }

        .google-btn img{
            width:22px;
            height:22px;
        }

    </style>

</head>

<body>

<div class="phone">

    <?php if(!empty($error)): ?>
        <div class="notif error">
            <?= $error; ?>
        </div>
    <?php endif; ?>

    <div class="logo">
        <img src="logo.png" alt="Logo Bimbel AIUEO">
    </div>

    <form action="" method="POST" class="form">

        <input 
            type="text"
            name="username"
            placeholder="Username"
            autocomplete="off"
            required
        >

        <input 
            type="password"
            name="password"
            placeholder="Password"
            autocomplete="off"
            required
        >

        <button type="submit">
            Masuk
        </button>

    </form>

    <p class="switch">
        Belum punya akun?
        <a href="register.php">Daftar</a>
    </p>

    <div class="divider">
        <span></span>
        <p>atau lanjut dengan</p>
        <span></span>
    </div>

    <div class="social">

        <button type="button" class="google-btn">
            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg">
            <span>Google</span>
        </button>

    </div>

</div>

</body>
</html>
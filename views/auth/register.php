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

                    $success = "Pendaftaran berhasil!";

                    echo '
                    <meta http-equiv="refresh" content="2;url=login.php">
                    ';

                } else {

                    $error = "Pendaftaran gagal!";

                }
            }

        } catch (PDOException $e) {

            $error = "Database Error : " . $e->getMessage();

        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Bimbel AIUEO</title>

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

        .notif.success{
            background:#28c76f;
        }

        .logo{
            width:160px;
            height:160px;
            background:#f5f5f5;
            border-radius:50%;
            margin:50px auto 40px;
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

    <?php if(!empty($success)): ?>
        <div class="notif success">
            <?= $success; ?>
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
            type="text"
            name="email_or_phone"
            placeholder="Email atau nomor telepon"
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
            Daftar
        </button>

    </form>

    <p class="switch">
        Sudah punya akun?
        <a href="login.php">Masuk</a>
    </p>

    <div class="divider">
        <span></span>
        <p>atau daftar dengan</p>
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
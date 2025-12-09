<?php
// views/customers/register.php
require_once '../../config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $no_hp = trim($_POST['no_hp']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Nama, email, dan password wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id_pelanggan FROM pelanggan WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert ke database
            $stmt = $pdo->prepare("INSERT INTO pelanggan (nama, email, password, no_hp) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nama, $email, $hashedPassword, $no_hp])) {
                $message = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal menyimpan data. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Service Elektronik ABC</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
    
    <style>
        /* --- ANIMASI & RESET --- */
        * {
            box-sizing: border-box;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.9) translateY(20px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* --- BODY STYLE --- */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            /* Gradient Background yang sama dengan Login agar konsisten */
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- CONTAINER KARTU --- */
        .container {
            width: 100%;
            max-width: 500px;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            animation: popIn 0.8s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            margin: 20px;
        }

        /* --- TYPOGRAPHY --- */
        h2 {
            color: #1e3c72;
            text-align: center;
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        p {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        /* --- FORM ELEMENTS --- */
        .form-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #444;
            transition: color 0.3s;
        }

        .form-group:focus-within label {
            color: #23a6d5; /* Ubah warna label saat input aktif */
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #23a6d5;
            background-color: #fff;
            outline: none;
            box-shadow: 0 4px 10px rgba(35, 166, 213, 0.1);
            transform: translateY(-2px);
        }

        /* --- BUTTON --- */
        button {
            width: 100%;
            padding: 12px;
            margin-top: 1rem;
            background: linear-gradient(to right, #11998e, #38ef7d); /* Hijau Segar */
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
            background: linear-gradient(to right, #38ef7d, #11998e);
        }

        /* --- ALERT MESSAGES --- */
        .message {
            padding: 12px;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            animation: popIn 0.5s ease;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            animation: shake 0.5s ease;
        }

        /* --- NAVIGATION LINKS --- */
        .nav-links {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            border-top: 1px solid #eee;
            padding-top: 1.5rem;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }

        .login-link {
            color: #23a6d5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .back-link:hover, .login-link:hover {
            text-decoration: underline;
            color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Daftar Akun Baru</h2>
        <p>Bergabunglah dengan kami untuk layanan terbaik.</p>

        <?php if ($message): ?>
            <div class="message success">✅ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" placeholder="Cth: Budi Santoso" required>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Cth: budi@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="no_hp">No. HP (WhatsApp)</label>
                <input type="text" id="no_hp" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" placeholder="Cth: 08123456789">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Buat password yang aman" required>
            </div>
            
            <button type="submit">Daftar Sekarang</button>
        </form>

        <div class="nav-links">
            <a href="../../public/index.php" class="back-link">← Kembali ke Beranda</a>
            <a href="../auth/login.php" class="login-link">Sudah punya akun? Login</a>
        </div>
    </div>
</body>
</html>
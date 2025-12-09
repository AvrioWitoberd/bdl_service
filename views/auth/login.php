<?php
// views/auth/login.php
session_start();

// Jika sudah login, redirect ke index (struktur ini dipertahankan)
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}
$loginAs = $_GET['login_as'] ?? 'pelanggan'; // Ganti default ke pelanggan, biar user-friendly
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Service Elektronik ABC</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        /* --- RESET & ANIMATION KEYFRAMES --- */
        * {
            box-sizing: border-box;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        /* --- CONTAINER STYLE --- */
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: slideInUp 0.8s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            backdrop-filter: blur(10px);
        }

        /* --- HEADINGS --- */
        h2 {
            color: #1e3c72;
            margin-bottom: 2rem;
            font-size: 2rem;
            border-bottom: none;
            font-weight: 600;
            letter-spacing: 1px;
        }

        h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: #23a6d5;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        /* --- FORM ELEMENTS --- */
        form {
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
            margin-top: 1.2rem;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .input-group:focus-within label {
            color: #23a6d5;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 0.5rem;
            border: 2px solid #eee;
            border-radius: 10px;
            background-color: #f9f9f9;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #23a6d5;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(35, 166, 213, 0.15);
            outline: none;
            transform: translateY(-2px);
        }

        /* --- BUTTON STYLE --- */
        input[type="submit"] {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            margin-top: 2rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        }

        input[type="submit"]:hover {
            background: linear-gradient(to right, #2a5298, #1e3c72);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(30, 60, 114, 0.4);
        }

        input[type="submit"]:active {
            transform: scale(0.98);
        }

        /* --- ERROR MESSAGE --- */
        .error-message {
            color: #d8000c;
            background-color: #ffbaba;
            border: none;
            border-left: 5px solid #d8000c;
            padding: 10px;
            margin-top: 1.5rem;
            border-radius: 4px;
            text-align: center;
            font-size: 0.9rem;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        /* --- LINK STYLE --- */
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #1e3c72;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link:hover {
            text-decoration: underline;
            color: #23a6d5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 Login Area</h2>

        <form action="../../controllers/AuthController.php" method="post">
            <input type="hidden" name="action" value="login">

            <label for="login_as">Login Sebagai:</label>
            <select name="login_as" id="login_as" required onchange="updateUsernameLabel()">
                <option value="pelanggan" <?php if($loginAs === 'pelanggan') echo 'selected'; ?>>Pelanggan (Email)</option>
                <option value="teknisi" <?php if($loginAs === 'teknisi') echo 'selected'; ?>>Teknisi (Email)</option>
                <option value="admin" <?php if($loginAs === 'admin') echo 'selected'; ?>>Admin (Email)</option>
            </select>

            <label for="username" id="username_label">
                Email:
            </label>
            <input
                type="email"
                id="username"
                name="username"
                required
                placeholder="Contoh: user@email.com"
            >

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">

            <input type="submit" value="Masuk Sekarang">
        </form>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                ⚠️ Kredensial tidak valid atau jenis login salah.
            </div>
        <?php endif; ?>

        <!-- 🔥 Tambahin link ke register & index -->
        <a href="../customers/register.php" class="back-link">Belum punya akun? Daftar di sini</a>
        <a href="../../index.php" class="back-link">← Kembali ke Beranda</a>
    </div>

    <script>
        // Fungsi untuk update label & input type saat pilih role
        function updateUsernameLabel() {
            const select = document.getElementById('login_as');
            const input = document.getElementById('username'); // ✅ FIX: ID input sesuai HTML
            const label = document.getElementById('username_label'); // ✅ FIX: ID label sesuai HTML

            // Karena sekarang semua role login pake email, gak usah ubah type
            // Cukup update placeholder dan label text biar jelas
            if (select.value === 'pelanggan' || select.value === 'teknisi' || select.value === 'admin') {
                input.type = 'email'; // Tetap email
                input.placeholder = 'Contoh: user@email.com';
                label.textContent = 'Email:';
            }
        }
    </script>
</body>
</html>
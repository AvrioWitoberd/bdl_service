<?php
// controllers/AuthController.php
require_once '../models/User.php';

// 🔥 Langkah 1: DAPATKAN $pdo DARI require_once (yg return $pdo)
$pdo = require_once '../config/database.php';

// 🔥 Langkah 2: CEK DULU KONEKSI NYA OK
if (!$pdo) {
    die("❌ Database connection failed. Check config/database.php and PostgreSQL service.");
}

// 🔥 Langkah 3: KASIH $pdo KE CONSTRUCTOR User()
$user = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $login_as = $_POST['login_as']; // 'admin', 'pelanggan', 'teknisi'

    $success = false;
    if ($login_as === 'admin') {
        $success = $user->authenticateAdmin($username, $password);
    } elseif ($login_as === 'pelanggan') {
        $success = $user->authenticatePelanggan($username, $password);
    } elseif ($login_as === 'teknisi') {
        $success = $user->authenticateTeknisi($username, $password);
    }

    if ($success) {
        // 🔁 REDIRECT BERDASARKAN ROLE
        $role = $_SESSION['role'];
        if ($role === 'admin') {
            $redirect = '../views/dashboard.php'; // Atau '../views/admin/dashboard.php' kalo bikin halaman khusus
        } elseif ($role === 'pelanggan') {
            $redirect = '../views/services/list.php'; // Lihat history service
        } elseif ($role === 'teknisi') {
            $redirect = '../views/services/list.php'; // Lihat service assigned to them
        } else {
            $redirect = '../public/index.php'; // Fallback
        }

        header("Location: $redirect");
        exit;
    } else {
        header("Location: ../views/auth/login.php?error=1&login_as=" . urlencode($login_as));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../views/auth/login.php");
    exit;
}
?>
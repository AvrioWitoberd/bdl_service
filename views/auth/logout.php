<?php
session_start();

// 1. Hapus semua session
$_SESSION = [];

// 2. Hapus cookie session biar bersih total
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session
session_destroy();

// 4. Balik ke halaman Login (Sesuaikan path ini kalau perlu)
header("Location: /bdl_service/views/auth/login.php");
exit;
?>
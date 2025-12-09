<?php
// config/database.php
$host = 'localhost';
$port = '5433'; // sesuai hasil test_db.php lo ✅
$dbname = 'aplikasi_service_elektronik';
$username = 'postgres';
$password = 'sekiro25';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    $error_msg = "[DB ERROR] Connection failed: " . $e->getMessage();
    error_log($error_msg);
    $pdo = false; // jangan die(), biar bisa di-handle
}

return $pdo; // 🔑 WAJIB ADA — ini yang sering hilang!
?>
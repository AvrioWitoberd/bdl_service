<?php
// test_db.php
$pdo = require_once 'config/database.php';

if ($pdo) {
    echo "<pre>✅ KONEKSI BERHASIL!\n";
    $ver = $pdo->query("SELECT version()")->fetchColumn();
    echo "PostgreSQL: " . htmlspecialchars($ver) . "\n";
    
    $count = $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
    echo "Jumlah pelanggan: $count\n";
    echo "</pre>";
} else {
    echo "❌ GAGAL — lihat error di log atau di atas";
}
?>
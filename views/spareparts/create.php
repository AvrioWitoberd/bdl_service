<?php
// views/spareparts/create.php

if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Sparepart.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil error & data lama
$message = $_GET['error'] ?? '';
$old = $_SESSION['old_form'] ?? [];
if (isset($_SESSION['old_form'])) unset($_SESSION['old_form']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Spare Part</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../../views/partials/header.php'; ?>
    
    <div class="container" style="padding: 20px;">
        <h1>Add Spare Part</h1>
        
        <?php if ($message): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../controllers/SparepartController.php">
            <input type="hidden" name="action" value="create">
            
            <label for="nama_sparepart">Name:</label><br>
            <input type="text" id="nama_sparepart" name="nama_sparepart" value="<?php echo htmlspecialchars($old['nama_sparepart'] ?? ''); ?>" required><br><br>

            <label for="stok">Stock:</label><br>
            <input type="number" id="stok" name="stok" value="<?php echo htmlspecialchars($old['stok'] ?? ''); ?>" min="0" required><br><br>

            <label for="harga">Price:</label><br>
            <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($old['harga'] ?? ''); ?>" step="0.01" min="0" required><br><br>

            <label for="merek">Brand:</label><br>
            <input type="text" id="merek" name="merek" value="<?php echo htmlspecialchars($old['merek'] ?? ''); ?>"><br><br>

            <input type="submit" value="Create Spare Part">
        </form>
        
        <a href="list.php">Back to Spare Parts</a>
    </div>
    
    <?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
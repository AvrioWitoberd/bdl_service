<?php
// views/customers/create.php

// === PERBAIKAN PATH & DOBEL INCLUDE ===
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Pelanggan.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);

// === AMBIL DATA DARI SESSION (JIKA ADA ERROR SEBELUMNYA) ===
$message = $_SESSION['error_message'] ?? ''; // Ambil pesan error dari Session (Bukan GET)
$old = $_SESSION['old_form'] ?? []; // Ambil data input lama

// Bersihkan session agar form bersih saat refresh manual
if (isset($_SESSION['old_form'])) unset($_SESSION['old_form']);
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Customer</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../../views/partials/header.php'; ?>
    
    <div class="container" style="padding: 20px;">
        <h1>Add Customer</h1>
        
        <?php if ($message): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../controllers/CustomerController.php">
            <input type="hidden" name="action" value="create">
            
            <label for="nama">Name:</label><br>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($old['nama'] ?? ''); ?>" required><br><br>

            <label for="no_hp">Phone:</label><br>
            <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($old['no_hp'] ?? ''); ?>" required><br><br>

            <label for="alamat">Address:</label><br>
            <textarea id="alamat" name="alamat" required><?php echo htmlspecialchars($old['alamat'] ?? ''); ?></textarea><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <input type="submit" value="Create Customer">
        </form>
        
        <a href="list.php">Back to Customers</a>
    </div>
    
    <?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
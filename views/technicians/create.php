<?php
// views/technicians/create.php

if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}

require_once __DIR__ . '/../../models/Teknisi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// === AMBIL ERROR & DATA LAMA DARI SESSION ===
$message = $_GET['error'] ?? '';
$old = $_SESSION['old_form'] ?? [];

// Hapus session lama agar form bersih kalau direfresh manual
if (isset($_SESSION['old_form'])) unset($_SESSION['old_form']);

// Helper sederhana untuk checkbox active
// Default Checked (true) jika form baru buka.
// Jika dari error submit, ikuti inputan user.
$isActive = true; 
if (!empty($old)) {
    $isActive = isset($old['status_aktif']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Technician</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../../views/partials/header.php'; ?>
    
    <div class="container" style="padding: 20px;">
        <h1>Add Technician</h1>
        
        <?php if ($message): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../controllers/TechnicianController.php">
            <input type="hidden" name="action" value="create">
            
            <label for="nama_teknisi">Name:</label><br>
            <input type="text" id="nama_teknisi" name="nama_teknisi" value="<?php echo htmlspecialchars($old['nama_teknisi'] ?? ''); ?>" required><br><br>

            <label for="keahlian">Expertise:</label><br>
            <textarea id="keahlian" name="keahlian"><?php echo htmlspecialchars($old['keahlian'] ?? ''); ?></textarea><br><br>

            <label for="no_hp">Phone:</label><br>
            <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($old['no_hp'] ?? ''); ?>"><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <label>
                <input type="checkbox" name="status_aktif" value="1" <?php if($isActive) echo 'checked'; ?>> Active
            </label><br><br>

            <input type="submit" value="Create Technician">
        </form>
        
        <a href="list.php">Back to Technicians</a>
    </div>
    
    <?php include __DIR__ . '/../../views/partials/footer.php'; ?>
</body>
</html>
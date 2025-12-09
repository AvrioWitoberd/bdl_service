<?php
// views/technicians/create.php
require_once '../../config/database.php';
require_once '../../models/Teknisi.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$teknisiModel = new Teknisi($pdo);
$message = $message ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Technician</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Add Technician</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="../../controllers/TechnicianController.php">
        <input type="hidden" name="action" value="create">
        <label for="nama_teknisi">Name:</label><br>
        <input type="text" id="nama_teknisi" name="nama_teknisi" value="<?php echo htmlspecialchars($_POST['nama_teknisi'] ?? ''); ?>" required><br><br>

        <label for="keahlian">Expertise:</label><br>
        <textarea id="keahlian" name="keahlian"><?php echo htmlspecialchars($_POST['keahlian'] ?? ''); ?></textarea><br><br>

        <label for="no_hp">Phone:</label><br>
        <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>"><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <label><input type="checkbox" name="status_aktif" value="1" <?php if((int)($_POST['status_aktif'] ?? 1)) echo 'checked'; ?>> Active</label><br><br>

        <input type="submit" value="Create Technician">
    </form>
    <a href="list.php">Back to Technicians</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
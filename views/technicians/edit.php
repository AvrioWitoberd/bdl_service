<?php
// views/technicians/edit.php
require_once '../../config/database.php';
require_once '../../models/Teknisi.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$teknisiModel = new Teknisi($pdo);
$id = $_GET['id'];
$teknisi = $teknisiModel->getById($id);

if (!$teknisi) {
    die("Technician not found.");
}

$message = $message ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Technician</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Edit Technician</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="../../controllers/TechnicianController.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id_teknisi" value="<?php echo $teknisi['id_teknisi']; ?>">
        <label for="nama_teknisi">Name:</label><br>
        <input type="text" id="nama_teknisi" name="nama_teknisi" value="<?php echo htmlspecialchars($teknisi['nama_teknisi']); ?>" required><br><br>

        <label for="keahlian">Expertise:</label><br>
        <textarea id="keahlian" name="keahlian"><?php echo htmlspecialchars($teknisi['keahlian']); ?></textarea><br><br>

        <label for="no_hp">Phone:</label><br>
        <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($teknisi['no_hp']); ?>"><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teknisi['email']); ?>" required><br><br>

        <label><input type="checkbox" name="status_aktif" value="1" <?php if($teknisi['status_aktif']) echo 'checked'; ?>> Active</label><br><br>

        <input type="submit" value="Update Technician">
    </form>
    <a href="list.php">Back to Technicians</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
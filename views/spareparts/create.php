<?php
// views/spareparts/create.php
require_once '../../config/database.php';
require_once '../../models/Sparepart.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$sparepartModel = new Sparepart($pdo);
$message = $message ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Spare Part</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Add Spare Part</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="../../controllers/SparepartController.php">
        <input type="hidden" name="action" value="create">
        <label for="nama_sparepart">Name:</label><br>
        <input type="text" id="nama_sparepart" name="nama_sparepart" value="<?php echo htmlspecialchars($_POST['nama_sparepart'] ?? ''); ?>" required><br><br>

        <label for="stok">Stock:</label><br>
        <input type="number" id="stok" name="stok" value="<?php echo (int)($_POST['stok'] ?? 0); ?>" min="0" required><br><br>

        <label for="harga">Price:</label><br>
        <input type="number" id="harga" name="harga" value="<?php echo (float)($_POST['harga'] ?? 0.00); ?>" step="0.01" min="0" required><br><br>

        <label for="merek">Brand:</label><br>
        <input type="text" id="merek" name="merek" value="<?php echo htmlspecialchars($_POST['merek'] ?? ''); ?>"><br><br>

        <input type="submit" value="Create Spare Part">
    </form>
    <a href="list.php">Back to Spare Parts</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
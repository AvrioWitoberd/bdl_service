<?php
// views/spareparts/edit.php
require_once '../../config/database.php';
require_once '../../models/Sparepart.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$sparepartModel = new Sparepart($pdo);
$id = $_GET['id'];
$sparepart = $sparepartModel->getById($id);

if (!$sparepart) {
    die("Spare part not found.");
}

$message = $message ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Spare Part</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Edit Spare Part</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="../../controllers/SparepartController.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id_sparepart" value="<?php echo $sparepart['id_sparepart']; ?>">
        <label for="nama_sparepart">Name:</label><br>
        <input type="text" id="nama_sparepart" name="nama_sparepart" value="<?php echo htmlspecialchars($sparepart['nama_sparepart']); ?>" required><br><br>

        <label for="stok">Stock:</label><br>
        <input type="number" id="stok" name="stok" value="<?php echo $sparepart['stok']; ?>" min="0" required><br><br>

        <label for="harga">Price:</label><br>
        <input type="number" id="harga" name="harga" value="<?php echo $sparepart['harga']; ?>" step="0.01" min="0" required><br><br>

        <label for="merek">Brand:</label><br>
        <input type="text" id="merek" name="merek" value="<?php echo htmlspecialchars($sparepart['merek']); ?>"><br><br>

        <input type="submit" value="Update Spare Part">
    </form>
    <a href="list.php">Back to Spare Parts</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
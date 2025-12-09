<?php
// views/customers/edit.php
require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$id = $_GET['id'];
$pelanggan = $pelangganModel->getById($id);

if (!$pelanggan) {
    die("Customer not found.");
}

$message = $message ?? ''; // Ambil dari controller jika ada
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Edit Customer</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="../../controllers/CustomerController.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id_pelanggan" value="<?php echo $pelanggan['id_pelanggan']; ?>">
        <label for="nama">Name:</label><br>
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($pelanggan['nama']); ?>" required><br><br>

        <label for="no_hp">Phone:</label><br>
        <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($pelanggan['no_hp']); ?>" required><br><br>

        <label for="alamat">Address:</label><br>
        <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($pelanggan['alamat']); ?></textarea><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($pelanggan['email']); ?>" required><br><br>

        <input type="submit" value="Update Customer">
    </form>
    <a href="list.php">Back to Customers</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
<?php
// views/customers/create.php
require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$message = $message ?? ''; // Ambil dari controller jika ada
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Customer</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Add Customer</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="../../controllers/CustomerController.php">
        <input type="hidden" name="action" value="create">
        <label for="nama">Name:</label><br>
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required><br><br>

        <label for="no_hp">Phone:</label><br>
        <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" required><br><br>

        <label for="alamat">Address:</label><br>
        <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <input type="submit" value="Create Customer">
    </form>
    <a href="list.php">Back to Customers</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
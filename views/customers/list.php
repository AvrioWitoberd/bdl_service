<?php
// views/customers/list.php
require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teknisi')) {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$pelanggan = $pelangganModel->getAll($limit, $offset, $search);
$totalPelanggan = $pelangganModel->countAll($search);
$totalPages = ceil($totalPelanggan / $limit);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <script src="../../public/js/main.js"></script>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Customers</h1>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="create.php">Add Customer</a>
    <?php endif; ?>
    <form method="GET" action="">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search customers...">
        <button type="submit">Search</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Join Date</th>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pelanggan as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['id_pelanggan']); ?></td>
                <td><?php echo htmlspecialchars($p['nama']); ?></td>
                <td><?php echo htmlspecialchars($p['no_hp']); ?></td>
                <td><?php echo htmlspecialchars($p['email']); ?></td>
                <td><?php echo htmlspecialchars($p['alamat']); ?></td>
                <td><?php echo $p['tanggal_daftar']; ?></td>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <td>
                        <a href="edit.php?id=<?php echo $p['id_pelanggan']; ?>">Edit</a>
                        <a href="#" onclick="confirmDelete(<?php echo $p['id_pelanggan']; ?>, 'customer', '../../controllers/CustomerController.php')">Delete</a>
                    </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
<?php
// views/spareparts/list.php
require_once '../../config/database.php';
require_once '../../models/Sparepart.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$sparepartModel = new Sparepart($pdo);
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$spareparts = $sparepartModel->getAll($limit, $offset, $search);
$totalSpareparts = $sparepartModel->countAll($search);
$totalPages = ceil($totalSpareparts / $limit);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Spare Parts</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <script src="../../public/js/main.js"></script>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Spare Parts</h1>
    <a href="create.php">Add Spare Part</a>
    <form method="GET" action="">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search spare parts...">
        <button type="submit">Search</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Stock</th>
                <th>Price</th>
                <th>Brand</th>
                <th>Last Update</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($spareparts as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['id_sparepart']); ?></td>
                <td><?php echo htmlspecialchars($s['nama_sparepart']); ?></td>
                <td><?php echo $s['stok']; ?></td>
                <td>$<?php echo number_format($s['harga'], 2); ?></td>
                <td><?php echo htmlspecialchars($s['merek']); ?></td>
                <td><?php echo $s['tanggal_update']; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $s['id_sparepart']; ?>">Edit</a>
                    <a href="#" onclick="confirmDelete(<?php echo $s['id_sparepart']; ?>, 'spare part', '../../controllers/SparepartController.php')">Delete</a>
                </td>
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
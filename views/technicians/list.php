<?php
// views/technicians/list.php
require_once '../../config/database.php';
require_once '../../models/Teknisi.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$teknisiModel = new Teknisi($pdo);
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$activeOnly = isset($_GET['active']) && $_GET['active'] === '1'; // Filter aktif

$teknisi = $teknisiModel->getAll($limit, $offset, $search, $activeOnly);
$totalTeknisi = $teknisiModel->countAll($search, $activeOnly);
$totalPages = ceil($totalTeknisi / $limit);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Technicians</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <script src="../../public/js/main.js"></script>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Technicians</h1>
    <a href="create.php">Add Technician</a>
    <form method="GET" action="">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search technicians...">
        <label><input type="checkbox" name="active" value="1" <?php if($activeOnly) echo 'checked'; ?>> Active Only</label>
        <button type="submit">Search</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Expertise</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teknisi as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['id_teknisi']); ?></td>
                <td><?php echo htmlspecialchars($t['nama_teknisi']); ?></td>
                <td><?php echo htmlspecialchars($t['keahlian']); ?></td>
                <td><?php echo htmlspecialchars($t['no_hp']); ?></td>
                <td><?php echo htmlspecialchars($t['email']); ?></td>
                <td><?php echo $t['status_aktif'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $t['id_teknisi']; ?>">Edit</a>
                    <a href="#" onclick="confirmDelete(<?php echo $t['id_teknisi']; ?>, 'technician', '../../controllers/TechnicianController.php')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&active=<?php echo $activeOnly ? 1 : 0; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
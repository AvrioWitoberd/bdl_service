<?php
// views/services/track.php
require_once '../../config/database.php';
require_once '../../models/Service.php';
require_once '../../models/Pembayaran.php'; // Jika ingin menampilkan status pembayaran
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$serviceModel = new Service($pdo);
$pembayaranModel = new Pembayaran($pdo);

$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    header("Location: list.php");
    exit;
}

$service = $serviceModel->getById($serviceId);
if (!$service) {
    die("Service not found.");
}

// Ambil status untuk dropdown update
$stmt = $pdo->prepare("SELECT id_status, nama_status FROM status_perbaikan ORDER BY nama_status");
$stmt->execute();
$statusOptions = $stmt->fetchAll();

// Ambil pembayaran terkait (jika ada)
$payment = $pembayaranModel->getByServiceId($serviceId);

$message = $message ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track Service #<?php echo $service['id_service']; ?></title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <script src="../../public/js/main.js"></script>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Track Service #<?php echo $service['id_service']; ?></h1>
    <div class="service-details">
        <h3>Service Details</h3>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($service['nama_pelanggan']); ?></p>
        <p><strong>Device:</strong> <?php echo htmlspecialchars($service['jenis_perangkat']) . ' ' . htmlspecialchars($service['merek']) . ' ' . htmlspecialchars($service['model']); ?></p>
        <p><strong>Complaint:</strong> <?php echo htmlspecialchars($service['keluhan']); ?></p>
        <p><strong>Estimated Cost:</strong> $<?php echo number_format($service['biaya_service'], 2); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($service['nama_status']); ?></p>
        <p><strong>Technician:</strong> <?php echo htmlspecialchars($service['nama_teknisi'] ?? 'Unassigned'); ?></p>
        <p><strong>Received:</strong> <?php echo $service['tanggal_masuk']; ?></p>
        <p><strong>Completed:</strong> <?php echo $service['tanggal_selesai']; ?></p>
        <p><strong>Notes:</strong> <?php echo htmlspecialchars($service['keterangan'] ?? 'N/A'); ?></p>
        <p><strong>Internal Notes:</strong> <?php echo htmlspecialchars($service['catatan_internal'] ?? 'N/A'); ?></p>
    </div>

    <?php if ($payment): ?>
        <div class="payment-details">
            <h3>Payment Details</h3>
            <p><strong>Amount:</strong> $<?php echo number_format($payment['total_bayar'], 2); ?></p>
            <p><strong>Method:</strong> <?php echo htmlspecialchars($payment['metode_bayar']); ?></p>
            <p><strong>Discount:</strong> <?php echo $payment['diskon']; ?>%</p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($payment['status_bayar']); ?></p>
            <p><strong>Date:</strong> <?php echo $payment['tanggal_bayar']; ?></p>
        </div>
    <?php endif; ?>

    <?php
    $canUpdateStatus = ($_SESSION['role'] === 'admin' || ($_SESSION['role'] === 'teknisi' && $_SESSION['user_id'] == $service['id_teknisi']));
    $canCompleteService = ($_SESSION['role'] === 'admin' && $service['nama_status'] !== 'completed');
    ?>

    <?php if ($canUpdateStatus): ?>
        <h3>Update Status</h3>
        <?php if ($message): ?>
            <p style="color: red;"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="POST" action="../../controllers/ServiceController.php">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id_service" value="<?php echo $service['id_service']; ?>">
            <label for="id_status">New Status:</label>
            <select name="id_status" id="id_status" required>
                <?php foreach ($statusOptions as $status): ?>
                    <option value="<?php echo $status['id_status']; ?>" <?php if($status['id_status'] == $service['id_status']) echo 'selected'; ?>><?php echo htmlspecialchars($status['nama_status']); ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="keterangan">Notes:</label><br>
            <textarea name="keterangan" id="keterangan"><?php echo htmlspecialchars($service['keterangan'] ?? ''); ?></textarea><br><br>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <label for="catatan_internal">Internal Notes:</label><br>
                <textarea name="catatan_internal" id="catatan_internal"><?php echo htmlspecialchars($service['catatan_internal'] ?? ''); ?></textarea><br><br>
            <?php endif; ?>

            <input type="submit" value="Update Status">
        </form>
    <?php endif; ?>

    <?php if ($canCompleteService): ?>
        <h3>Complete Service & Record Payment</h3>
        <form method="POST" action="../../controllers/ServiceController.php">
            <input type="hidden" name="action" value="complete_service">
            <input type="hidden" name="id_service" value="<?php echo $service['id_service']; ?>">
            <label for="final_cost">Final Cost:</label>
            <input type="number" name="final_cost" id="final_cost" value="<?php echo $service['biaya_service']; ?>" step="0.01" required><br><br>

            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="cash">Cash</option>
                <option value="credit_card">Credit Card</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select><br><br>

            <label for="discount">Discount (%):</label>
            <input type="number" name="discount" id="discount" value="0" min="0" max="100" step="0.01"><br><br>

            <input type="submit" value="Complete & Record Payment" style="background-color: #007bff;">
        </form>
    <?php endif; ?>

    <a href="list.php">Back to Services</a>
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>
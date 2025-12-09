<?php
// views/reports/index.php (ini adalah view yang ditampilkan oleh ReportController)
// $servicesByStatus, $revenueByTech, $startDate, $endDate diambil dari controller
?>
<!DOCTYPE html>
<html>

<head>
    <title>Reports</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <?php include '../partials/header.php'; ?>
    <h1>Reports</h1>
    <form method="GET" action="../../controllers/ReportController.php">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
        <button type="submit">Filter</button>
    </form>

    <h2>Services by Status</h2>
    <a
        href="../../controllers/ReportController.php?export=services_by_status&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">Export
        to CSV</a>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicesByStatus as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nama_status']); ?></td>
                    <td><?php echo $row['jumlah_servis']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Revenue by Technician</h2>
    <a
        href="../../controllers/ReportController.php?export=revenue_by_tech&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">Export
        to CSV</a>
    <table>
        <thead>
            <tr>
                <th>Technician</th>
                <th>Total Revenue</th>
                <th>Payments Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenueByTech as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nama_teknisi']); ?></td>
                    <td>$<?php echo number_format($row['total_pendapatan'], 2); ?></td>
                    <td><?php echo $row['jumlah_pembayaran']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php include '../partials/footer.php'; ?>
</body>

</html>
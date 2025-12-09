<?php
// views/database_features/performance.php (ini adalah view yang ditampilkan oleh DatabaseFeaturesController)
// $mvData, $viewData, $explainResults, $refreshMessage diambil dari controller
?>
<!DOCTYPE html>
<html>

<head>
    <title>Performance & DB Features</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
    <?php include '../partials/header.php'; ?>
    <h1>Performance & Database Features</h1>

    <h2>Materialized View: Monthly Revenue</h2>
    <p><a href="../../controllers/DatabaseFeaturesController.php?refresh_mv=1">Refresh Materialized View</a></p>
    <?php if ($refreshMessage): ?>
        <p><?php echo $refreshMessage; ?></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Year</th>
                <th>Month</th>
                <th>Total Revenue</th>
                <th>Total Services</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mvData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['year']); ?></td>
                    <td><?php echo htmlspecialchars($row['month']); ?></td>
                    <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['total_services']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Complex View: Service Summary</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Device</th>
                <th>Status</th>
                <th>Technician</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($viewData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_service']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td><?php echo htmlspecialchars($row['jenis_perangkat']) . ' ' . htmlspecialchars($row['merek']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['nama_status']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_teknisi']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Query Performance (EXPLAIN ANALYZE)</h2>
    <h3>Query on Service Status (Assuming index on id_status, tanggal_masuk):</h3>
    <pre><?php echo htmlspecialchars($explainResults['with_index']); ?></pre>
    <!-- Jika Anda ingin membandingkan tanpa index, Anda perlu membuat query tambahan -->

    <?php include '../partials/footer.php'; ?>
</body>

</html>
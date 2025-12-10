<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /bdl_service/views/auth/login.php");
    exit;
}
?>

<nav style="background-color: #007bff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="/bdl_service/index.php" style="color: white; text-decoration: none; font-weight: bold;">🏠 Beranda</a>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="/bdl_service/views/dashboard.php" style="color: white; text-decoration: none; margin-left: 1rem;">📊 Dashboard</a>
            <a href="/bdl_service/views/customers/list.php" style="color: white; text-decoration: none; margin-left: 1rem;">👥 Pelanggan</a>
            <a href="/bdl_service/views/technicians/list.php" style="color: white; text-decoration: none; margin-left: 1rem;">🔧 Teknisi</a>
            <a href="/bdl_service/views/spareparts/list.php" style="color: white; text-decoration: none; margin-left: 1rem;">⚙️ Spare Part</a>
            <a href="/bdl_service/views/services/list.php" style="color: white; text-decoration: none; margin-left: 1rem;">📋 Service</a>
            <a href="/bdl_service/views/reports/index.php" style="color: white; text-decoration: none; margin-left: 1rem;">📈 Laporan</a>
            <a href="/bdl_service/views/database_features/performance.php" style="color: white; text-decoration: none; margin-left: 1rem;">💾 Fitur DB</a>
        <?php elseif ($_SESSION['role'] === 'pelanggan'): ?>
            <a href="/bdl_service/views/services/list.php" style="color: white; text-decoration: none; margin-left: 1rem;">📋 Service Saya</a>
        <?php elseif ($_SESSION['role'] === 'teknisi'): ?>
            <a href="/bdl_service/views/services/list.php" style="color: white; text-decoration: none; margin-left: 1rem;">🔧 Service Saya</a>
        <?php endif; ?>
    </div>

    <div>
        <span style="color: white;">Halo, <?= htmlspecialchars($_SESSION['name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
        <a href="/bdl_service/controllers/AuthController.php?action=logout" style="color: white; text-decoration: none; margin-left: 1rem;">Logout</a>
    </div>
</nav>
<hr>

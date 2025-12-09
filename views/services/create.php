<!-- views/services/create.php -->
<?php
require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';
require_once '../../models/Teknisi.php';
require_once '../../models/Perangkat.php';
require_once '../../models/Service.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { // Hanya admin yang bisa menerima service
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$teknisiModel = new Teknisi($pdo);
$perangkatModel = new Perangkat($pdo);
$serviceModel = new Service($pdo);

$pelangganList = $pelangganModel->getAll(1000, 0);
$teknisiList = $teknisiModel->getAll(1000, 0, '', true); // Hanya teknisi aktif

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_perangkat = (int) $_POST['id_perangkat'];
    $id_teknisi = !empty($_POST['id_teknisi']) ? (int) $_POST['id_teknisi'] : null; // Bisa kosong saat diterima
    $id_admin = $_SESSION['user_id']; // Ambil dari session admin
    $keluhan = trim($_POST['keluhan']);
    $biaya_service = (float) $_POST['biaya_service'];

    // Validasi dasar
    if (empty($id_perangkat) || empty($keluhan)) {
        $message = 'Perangkat dan Keluhan wajib diisi.';
    } else {
        $newServiceId = $serviceModel->create($id_perangkat, $id_teknisi, $id_admin, $keluhan, $biaya_service);
        if ($newServiceId) {
            header("Location: list.php?msg=Service created successfully with ID: $newServiceId");
            exit;
        } else {
            $message = 'Error creating service.';
        }
    }
}

// Jika pelanggan dipilih via AJAX, ambil perangkatnya
if (isset($_GET['pelanggan_id']) && is_numeric($_GET['pelanggan_id'])) {
    $selectedPelangganId = (int) $_GET['pelanggan_id'];
    $devices = $perangkatModel->getByPelangganId($selectedPelangganId);
    header('Content-Type: application/json');
    echo json_encode($devices);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Receive Service</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pelangganSelect = document.getElementById('pelanggan_id');
            const deviceSelect = document.getElementById('id_perangkat');

            pelangganSelect.addEventListener('change', function () {
                const pelangganId = this.value;
                if (pelangganId) {
                    fetch(`create.php?pelanggan_id=${pelangganId}`)
                        .then(response => response.json())
                        .then(devices => {
                            deviceSelect.innerHTML = '<option value="">Pilih Perangkat</option>';
                            devices.forEach(device => {
                                const option = document.createElement('option');
                                option.value = device.id_perangkat;
                                option.textContent = `${device.jenis_perangkat} ${device.merek} ${device.model}`;
                                deviceSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error fetching devices:', error));
                } else {
                    deviceSelect.innerHTML = '<option value="">Pilih Perangkat</option>';
                }
            });
        });
    </script>
</head>

<body>
    <?php include '../../views/partials/header.php'; ?>
    <h1>Receive Service</h1>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="pelanggan_id">Customer:</label><br>
        <select id="pelanggan_id" name="pelanggan_id" required>
            <option value="">Select Customer</option>
            <?php foreach ($pelangganList as $pelanggan): ?>
                <option value="<?php echo $pelanggan['id_pelanggan']; ?>">
                    <?php echo htmlspecialchars($pelanggan['nama']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="id_perangkat">Device:</label><br>
        <select id="id_perangkat" name="id_perangkat" required>
            <option value="">Select Device</option>
            <!-- Options will be populated by JavaScript based on selected customer -->
        </select><br><br>

        <label for="id_teknisi">Technician (Optional):</label><br>
        <select id="id_teknisi" name="id_teknisi">
            <option value="">Assign Later</option>
            <?php foreach ($teknisiList as $teknisi): ?>
                <option value="<?php echo $teknisi['id_teknisi']; ?>">
                    <?php echo htmlspecialchars($teknisi['nama_teknisi']); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="keluhan">Complaint:</label><br>
        <textarea id="keluhan" name="keluhan" required></textarea><br><br>

        <label for="biaya_service">Estimated Cost:</label><br>
        <input type="number" id="biaya_service" name="biaya_service" step="0.01" value="0.00"><br><br>

        <input type="submit" value="Create Service">
    </form>
    <a href="list.php">Back to Services</a>
</body>

</html>
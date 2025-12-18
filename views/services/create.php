<?php
// views/services/create.php

// === 1. LOGIKA PHP (ASLI DENGAN PERBAIKAN SINTAKS) ===
$pdo = require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';
require_once '../../models/Teknisi.php';
require_once '../../models/Perangkat.php';
require_once '../../models/Service.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'pelanggan'])) {
    header("Location: list.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$teknisiModel = new Teknisi($pdo);
$perangkatModel = new Perangkat($pdo);
$serviceModel = new Service($pdo);

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$message = $_GET['msg'] ?? '';
$error = '';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_perangkat = trim($_POST['nama_perangkat']);
    $jenis_perangkat = trim($_POST['jenis_perangkat']);
    $merek = trim($_POST['merek']);
    $nomor_seri = trim($_POST['nomor_seri']);
    $keluhan = trim($_POST['keluhan']);

    if ($role === 'admin') {
        $id_pelanggan = (int) $_POST['pelanggan_id'];
        $id_teknisi = !empty($_POST['id_teknisi']) ? (int) $_POST['id_teknisi'] : null;
        $biaya_service = (float) $_POST['biaya_service'];
        
        $mapAdmin = [
            1 => 8001,  
            2 => 8002,  
        ];

        if (isset($mapAdmin[$userId])) {
            $id_admin = $mapAdmin[$userId]; 
        } else {
            // Jaga-jaga kalau user tidak terdaftar, ambil admin pertama di DB
            $stmtDefault = $pdo->query("SELECT id_admin FROM admin ORDER BY id_admin ASC LIMIT 1");
            $id_admin = $stmtDefault->fetchColumn();
        }
    } else {
        // Logika untuk user non-admin (Pelanggan)
        $id_pelanggan = $userId;
        $id_teknisi = null;
        $biaya_service = 0;
        $id_admin = null;
    }

    if (empty($nama_perangkat) || empty($jenis_perangkat) || empty($keluhan)) {
        $error = 'Nama Perangkat, Jenis, dan Keluhan wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();
            $newDeviceId = $perangkatModel->create($id_pelanggan, $nama_perangkat, $jenis_perangkat, $merek, $nomor_seri);
            $newServiceId = $serviceModel->create($newDeviceId, $id_teknisi, $id_admin, $id_pelanggan, $keluhan, $biaya_service);
            $pdo->commit();
            header("Location: list.php?msg=Service created successfully");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

// Ambil data dropdown untuk Admin
$pelangganList = [];
$teknisiList = [];
if ($role === 'admin') {
    $pelangganList = $pelangganModel->getAll(1000, 0);
    $teknisiList = $teknisiModel->getAll(1000, 0, '', true);
}

// === 2. LOAD HEADER ===
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    .card-custom {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        background: #fff;
        overflow: hidden;
    }

    .form-section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #adb5bd;
        margin-bottom: 1rem;
        border-bottom: 1px solid #f1f1f1;
        padding-bottom: 0.5rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
        border-color: #dee2e6;
    }

    .form-control, .form-select {
        border-left: none;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        padding: 0.7rem 1rem;
    }

    .form-control:focus, .form-select:focus {
        background-color: #fff;
        box-shadow: none;
        border-color: #5D87FF; /* Fokus Biru */
    }

    .input-group:focus-within .input-group-text {
        background-color: #fff;
        border-color: #5D87FF;
        color: #5D87FF;
    }

    /* Area Admin Khusus */
    .admin-area {
        background: #f0f7ff; /* Biru sangat muda */
        border: 1px dashed #5D87FF;
        border-radius: 0.75rem;
    }

    /* Tombol Simpan Hijau */
    .btn-save {
        background-color: #13deb9;
        border-color: #13deb9;
        color: white;
        padding: 10px 25px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-save:hover {
        background-color: #0bb89a;
        border-color: #0bb89a;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(19, 222, 185, 0.3);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Service Baru</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small text-muted mt-1">
                <li class="breadcrumb-item"><a href="list.php" class="text-decoration-none">Data Service</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
            </ol>
        </nav>
    </div>
    <a href="list.php" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card-custom animate__animated animate__fadeInUp">
    <div class="card-body p-4 p-md-5">
        <form method="POST">
            
            <div class="row g-5">
                <div class="col-lg-6">
                    
                    <?php if ($role === 'admin'): ?>
                        <div class="form-section-title"><i class="fas fa-user-tag me-2"></i>Identitas Pelanggan</div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Pilih Pemilik Perangkat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <select name="pelanggan_id" class="form-select" required>
                                    <option value="">-- Cari Pelanggan --</option>
                                    <?php foreach ($pelangganList as $p): ?>
                                        <option value="<?= $p['id_pelanggan'] ?>">
                                            <?= htmlspecialchars($p['nama']) ?> — <?= $p['no_hp'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-section-title"><i class="fas fa-laptop me-2"></i>Informasi Perangkat</div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nama / Model</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                            <input type="text" name="nama_perangkat" class="form-control" placeholder="Contoh: Samsung A50" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Kategori</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                            <input type="text" name="jenis_perangkat" class="form-control" placeholder="Contoh: Smartphone" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-secondary">Merek</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-copyright"></i></span>
                                <input type="text" name="merek" class="form-control" placeholder="Samsung">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-secondary">Nomor Seri (SN)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" name="nomor_seri" class="form-control" placeholder="SN123...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-section-title"><i class="fas fa-comment-medical me-2"></i>Detail Masalah</div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Deskripsi Keluhan</label>
                        <div class="input-group h-100">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea name="keluhan" class="form-control" style="min-height: 120px;" placeholder="Ceritakan kronologi kerusakan atau keluhan yang dialami..." required></textarea>
                        </div>
                    </div>

                    <?php if ($role === 'admin'): ?>
                        <div class="admin-area p-3 mt-4">
                            <div class="form-section-title mb-3 text-primary" style="border-bottom-color: rgba(93, 135, 255, 0.3);">
                                <i class="fas fa-user-shield me-2"></i>Admin Area
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">Tunjuk Teknisi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-user-gear"></i></span>
                                    <select name="id_teknisi" class="form-select bg-white">
                                        <option value="">-- Belum Ada --</option>
                                        <?php foreach ($teknisiList as $t): ?>
                                            <option value="<?= $t['id_teknisi'] ?>"><?= htmlspecialchars($t['nama_teknisi']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small text-secondary">Estimasi Biaya / DP</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white fw-bold text-success">Rp</span>
                                    <input type="number" name="biaya_service" class="form-control bg-white" value="0">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="d-flex justify-content-end gap-2">
                <a href="list.php" class="btn btn-light border px-4 rounded-pill">Batal</a>
                <button type="submit" class="btn btn-save rounded-pill px-4 shadow-sm">
                    <i class="fas fa-save me-2"></i> Simpan Service
                </button>
            </div>

        </form>
    </div>
</div>

<?php
// === 5. LOAD FOOTER ===
require_once __DIR__ . '/../partials/footer.php';
?>
<?php
// models/Service.php
class Service
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($id_perangkat, $id_teknisi, $id_admin, $id_pelanggan, $keluhan, $biaya_service = 0.00)
{
    try {
        $this->pdo->beginTransaction();

        // Default status = "Menunggu Konfirmasi"
        $stmtStatus = $this->pdo->prepare("
            SELECT id_status 
            FROM status_perbaikan 
            WHERE nama_status = 'Menunggu Konfirmasi'
            LIMIT 1
        ");
        $stmtStatus->execute();
        $status = $stmtStatus->fetchColumn();

        $stmt = $this->pdo->prepare("
            INSERT INTO service 
            (id_perangkat, id_teknisi, id_admin, id_pelanggan, id_status, tanggal_masuk, keluhan, biaya_service)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)
        ");

        $stmt->execute([
            $id_perangkat,
            $id_teknisi,
            $id_admin,
            $id_pelanggan,
            $status,
            $keluhan,
            $biaya_service
        ]);

        $stmt2 = $this->pdo->query("SELECT lastval()");
        $newServiceId = $stmt2->fetchColumn();


        $this->pdo->commit();
        return $newServiceId;

    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Create service failed: " . $e->getMessage());
        return false;
    }
}

    public function getServices($limit = 10, $offset = 0, $search = '', $filterStatus = '', $filterTeknisi = '')
    {
        $whereClause = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $whereClause .= " AND (s.keluhan ILIKE :search OR p.nama ILIKE :search OR t.nama_teknisi ILIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($filterStatus) {
            $whereClause .= " AND s.id_status = :status_id";
            $params[':status_id'] = $filterStatus;
        }
        if ($filterTeknisi) {
            $whereClause .= " AND s.id_teknisi = :teknisi_id";
            $params[':teknisi_id'] = $filterTeknisi;
        }

        $sql = "SELECT s.id_service, s.tanggal_masuk, s.tanggal_selesai, s.keluhan, s.biaya_service, s.keterangan, s.catatan_internal, sp.nama_status, d.jenis_perangkat, d.merek, d.model, p.nama as nama_pelanggan, t.nama_teknisi
                 FROM service s
                 JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                 JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
                 LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                 JOIN status_perbaikan sp ON s.id_status = sp.id_status
                 $whereClause
                 ORDER BY s.tanggal_masuk DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countServices($search = '', $filterStatus = '', $filterTeknisi = '')
    {
        $whereClause = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $whereClause .= " AND (s.keluhan ILIKE :search OR p.nama ILIKE :search OR t.nama_teknisi ILIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($filterStatus) {
            $whereClause .= " AND s.id_status = :status_id";
            $params[':status_id'] = $filterStatus;
        }
        if ($filterTeknisi) {
            $whereClause .= " AND s.id_teknisi = :teknisi_id";
            $params[':teknisi_id'] = $filterTeknisi;
        }

        $sql = "SELECT COUNT(*) FROM service s JOIN perangkat d ON s.id_perangkat = d.id_perangkat JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi JOIN status_perbaikan sp ON s.id_status = sp.id_status $whereClause";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $sql = "SELECT s.*, d.jenis_perangkat, d.merek, d.model, p.nama as nama_pelanggan, t.nama_teknisi, sp.nama_status FROM service s JOIN perangkat d ON s.id_perangkat = d.id_perangkat JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi JOIN status_perbaikan sp ON s.id_status = sp.id_status WHERE s.id_service = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($serviceId, $statusId, $keterangan = '', $catatan_internal = '')
    {
        $stmt = $this->pdo->prepare("UPDATE service SET id_status = ?, keterangan = ?, catatan_internal = ? WHERE id_service = ?");
        return $stmt->execute([$statusId, $keterangan, $catatan_internal, $serviceId]);
    }

    // Transaction: Complete service and record payment
    public function completeService($serviceId, $finalCost, $paymentMethod, $discount = 0.00)
{
    try {
        $this->pdo->beginTransaction();

        // Update status ke "Selesai Diperbaiki"
        $stmt = $this->pdo->prepare("
            UPDATE service 
            SET 
                id_status = (
                    SELECT id_status 
                    FROM status_perbaikan 
                    WHERE nama_status = 'Selesai Diperbaiki'
                    LIMIT 1
                ),
                tanggal_selesai = NOW(),
                biaya_service = ?
            WHERE id_service = ?
        ");
        $stmt->execute([$finalCost, $serviceId]);

        $finalPaymentAmount = $finalCost - ($discount);

        $stmt = $this->pdo->prepare("
            INSERT INTO pembayaran 
            (id_service, metode_bayar, total_bayar, diskon, status_bayar)
            VALUES (?, ?, ?, ?, 'Lunas')
        ");
        $stmt->execute([$serviceId, $paymentMethod, $finalPaymentAmount, $discount]);

        $this->pdo->commit();
        return true;

    } catch (Exception $e) {
        $this->pdo->rollBack();
        error_log("Transaction failed completing service: " . $e->getMessage());
        return false;
    }
}

}
?>
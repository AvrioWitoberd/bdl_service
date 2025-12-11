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
        // 1. Cari Status Default
        $stmtStatus = $this->pdo->prepare("SELECT id_status FROM status_perbaikan WHERE nama_status = 'Menunggu Konfirmasi' LIMIT 1");
        $stmtStatus->execute();
        $status = $stmtStatus->fetchColumn();

        // Fallback jika status tidak ada
        if (!$status) {
             $stmtStatus = $this->pdo->query("SELECT id_status FROM status_perbaikan LIMIT 1");
             $status = $stmtStatus->fetchColumn();
        }

        // 2. Insert Data
        // Kita biarkan Exception melempar ke Controller jika gagal (JANGAN pakai try-catch disini)
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

        // 3. Kembalikan ID
        return $this->pdo->lastInsertId();
    }

    public function delete($id) {
        // Hapus pembayaran terkait dulu jika ada constraint (opsional)
        // $stmt = $this->pdo->prepare("DELETE FROM pembayaran WHERE id_service = ?");
        // $stmt->execute([$id]);

        $stmt = $this->pdo->prepare("DELETE FROM service WHERE id_service = ?");
        return $stmt->execute([$id]);
    }

    // Tambahkan parameter $id_admin di sini
    public function update($id, $id_teknisi, $keluhan, $biaya_service, $id_status, $id_admin) 
    {
        // Kita tambahkan "id_admin = ?" di query update
        $stmt = $this->pdo->prepare("
            UPDATE service 
            SET id_teknisi = ?, 
                keluhan = ?, 
                biaya_service = ?, 
                id_status = ?,
                id_admin = ?       -- Update kolom admin
            WHERE id_service = ?
        ");

        // Masukkan data sesuai urutan tanda tanya (?)
        return $stmt->execute([
            $id_teknisi,    
            $keluhan,       
            $biaya_service, 
            $id_status,     
            $id_admin,      // Masukkan ID Admin penanggung jawab
            $id             
        ]);
    }

    // Tambahkan parameter $filterPelanggan di urutan terakhir
    public function getServices($limit = 10, $offset = 0, $search = '', $filterStatus = '', $filterTeknisi = '', $filterPelanggan = '')
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
        // === TAMBAHAN LOGIC FILTER PELANGGAN ===
        if ($filterPelanggan) {
            $whereClause .= " AND s.id_pelanggan = :pelanggan_id";
            $params[':pelanggan_id'] = $filterPelanggan;
        }

        $sql = "SELECT s.id_service, s.tanggal_masuk, s.tanggal_selesai, s.keluhan, s.biaya_service, s.keterangan, s.catatan_internal, 
                       sp.nama_status, 
                       d.nama_perangkat, d.jenis_perangkat, d.merek, 
                       p.nama as nama_pelanggan, 
                       t.nama_teknisi
                 FROM service s
                 JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                 JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
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

    public function countServices($search = '', $filterStatus = '', $filterTeknisi = '', $filterPelanggan = '')
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
        // === TAMBAHAN LOGIC FILTER PELANGGAN ===
        if ($filterPelanggan) {
            $whereClause .= " AND s.id_pelanggan = :pelanggan_id";
            $params[':pelanggan_id'] = $filterPelanggan;
        }

        $sql = "SELECT COUNT(*) 
                FROM service s 
                JOIN perangkat d ON s.id_perangkat = d.id_perangkat 
                JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan 
                LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi 
                JOIN status_perbaikan sp ON s.id_status = sp.id_status 
                $whereClause";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        // PERBAIKAN: Hapus d.model
        $sql = "SELECT s.*, 
                       d.nama_perangkat, d.jenis_perangkat, d.merek, 
                       p.nama as nama_pelanggan, 
                       t.nama_teknisi, 
                       sp.nama_status 
                FROM service s 
                JOIN perangkat d ON s.id_perangkat = d.id_perangkat 
                JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan 
                LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi 
                JOIN status_perbaikan sp ON s.id_status = sp.id_status 
                WHERE s.id_service = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Method lainnya (updateStatus, completeService) biarkan tetap sama...
    // ...
    public function updateStatus($serviceId, $statusId, $keterangan = '', $catatan_internal = '')
    {
        $stmt = $this->pdo->prepare("UPDATE service SET id_status = ?, keterangan = ?, catatan_internal = ? WHERE id_service = ?");
        return $stmt->execute([$statusId, $keterangan, $catatan_internal, $serviceId]);
    }
}
?>
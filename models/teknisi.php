<?php
// models/Teknisi.php
class Teknisi
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll($limit = 10, $offset = 0, $search = '', $activeOnly = false) {
        $whereClause = ' WHERE 1=1 ';
        $params = [];
        
        // Filter Search
        if ($search) {
            $whereClause .= " AND (nama_teknisi ILIKE :search OR no_hp ILIKE :search OR email ILIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        // Filter Status Aktif
        if ($activeOnly) {
            $whereClause .= " AND status_aktif = TRUE";
        }

        // Query dengan Function Database
        $sql = "SELECT id_teknisi, nama_teknisi, keahlian, no_hp, email, status_aktif,
                       fn_hitung_job_teknisi(id_teknisi) as total_job 
                FROM teknisi 
                $whereClause 
                ORDER BY nama_teknisi 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        
        // === BAGIAN INI YANG HILANG SEBELUMNYA ===
        // Binding Parameter Limit & Offset
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        
        // Binding Parameter Search
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        // ==========================================

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll($search = '', $activeOnly = false)
    {
        $whereClause = ' WHERE 1=1 ';
        $params = [];
        if ($search) {
            $whereClause .= " AND (nama_teknisi ILIKE :search OR no_hp ILIKE :search OR email ILIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($activeOnly) {
            $whereClause .= " AND status_aktif = TRUE";
        }
        $sql = "SELECT COUNT(*) FROM teknisi $whereClause";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT id_teknisi, nama_teknisi, keahlian, no_hp, email, status_aktif FROM teknisi WHERE id_teknisi = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($nama_teknisi, $keahlian, $no_hp, $email, $password, $status_aktif = true)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO teknisi (nama_teknisi, keahlian, no_hp, email, password, status_aktif) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nama_teknisi, $keahlian, $no_hp, $email, $hashedPassword, $status_aktif]);
    }

    public function update($id, $nama_teknisi, $keahlian, $no_hp, $email, $status_aktif)
    {
        $stmt = $this->pdo->prepare("UPDATE teknisi SET nama_teknisi=?, keahlian=?, no_hp=?, email=?, status_aktif=? WHERE id_teknisi=?");
        return $stmt->execute([$nama_teknisi, $keahlian, $no_hp, $email, $status_aktif, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM teknisi WHERE id_teknisi = ?");
        return $stmt->execute([$id]);
    }
}
?>
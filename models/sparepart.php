<?php
// models/Sparepart.php
class Sparepart
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll($limit = 10, $offset = 0, $search = '')
    {
        $whereClause = '';
        $params = [];
        if ($search) {
            $whereClause = " WHERE nama_sparepart ILIKE :search OR merek ILIKE :search";
            $params = [':search' => "%$search%"];
        }
        $sql = "SELECT id_sparepart, nama_sparepart, stok, harga, merek, tanggal_update FROM sparepart $whereClause ORDER BY nama_sparepart LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll($search = '')
    {
        $whereClause = '';
        $params = [];
        if ($search) {
            $whereClause .= " WHERE nama_sparepart ILIKE :search OR merek ILIKE :search";
            $params = [':search' => "%$search%"];
        }
        $sql = "SELECT COUNT(*) FROM sparepart $whereClause";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT id_sparepart, nama_sparepart, stok, harga, merek, tanggal_update FROM sparepart WHERE id_sparepart = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($nama_sparepart, $stok, $harga, $merek)
    {
        $stmt = $this->pdo->prepare("INSERT INTO sparepart (nama_sparepart, stok, harga, merek) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nama_sparepart, $stok, $harga, $merek]);
    }

    public function update($id, $nama_sparepart, $stok, $harga, $merek)
    {
        $stmt = $this->pdo->prepare("UPDATE sparepart SET nama_sparepart=?, stok=?, harga=?, merek=?, tanggal_update=NOW() WHERE id_sparepart=?");
        return $stmt->execute([$nama_sparepart, $stok, $harga, $merek, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM sparepart WHERE id_sparepart = ?");
        return $stmt->execute([$id]);
    }
}
?>
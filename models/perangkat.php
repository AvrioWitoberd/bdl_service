<?php
// models/Perangkat.php
class Perangkat
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByPelangganId($pelangganId)
    {
        $stmt = $this->pdo->prepare("SELECT id_perangkat, jenis_perangkat, merek, model, kondisi_masuk, tanggal_masuk FROM perangkat WHERE id_pelanggan = ? ORDER BY tanggal_masuk DESC");
        $stmt->execute([$pelangganId]);
        return $stmt->fetchAll();
    }

    public function create($pelangganId, $jenis, $merek, $model, $kondisi)
    {
        $stmt = $this->pdo->prepare("INSERT INTO perangkat (id_pelanggan, jenis_perangkat, merek, model, kondisi_masuk) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$pelangganId, $jenis, $merek, $model, $kondisi]);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM perangkat WHERE id_perangkat = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    // Add other methods as needed
}
?>
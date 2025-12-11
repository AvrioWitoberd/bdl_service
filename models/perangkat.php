<?php
// models/Perangkat.php

class Perangkat {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. FUNGSI CREATE (Untuk Service Baru)
    public function create($id_pelanggan, $nama_perangkat, $jenis_perangkat, $merek, $nomor_seri)
    {
        $sql = "INSERT INTO perangkat 
                (id_pelanggan, nama_perangkat, jenis_perangkat, merek, nomor_seri, tanggal_input) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $id_pelanggan, 
            $nama_perangkat, 
            $jenis_perangkat, 
            $merek, 
            $nomor_seri
        ]);
        
        return $this->pdo->lastInsertId();
    }

    // 2. FUNGSI GET BY ID (Yang kamu tanyakan) -> TETAP DISIMPAN
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM perangkat WHERE id_perangkat = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 3. FUNGSI GET BY PELANGGAN (Untuk List dropdown jika perlu)
    public function getByPelangganId($idPelanggan)
    {
        $stmt = $this->pdo->prepare("
            SELECT id_perangkat, nama_perangkat, jenis_perangkat, merek, nomor_seri, tanggal_input
            FROM perangkat 
            WHERE id_pelanggan = ? 
            ORDER BY id_perangkat DESC
        ");
        
        $stmt->execute([$idPelanggan]);
        return $stmt->fetchAll();
    }
    
    // 4. FUNGSI GET ALL (Opsional, buat Admin lihat semua)
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM perangkat ORDER BY id_perangkat DESC");
        return $stmt->fetchAll();
    }
}
?>
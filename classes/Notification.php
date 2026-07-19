<?php
require_once __DIR__ . '/Model.php';

class Notification extends Model {

    // ----- Kirim notifikasi -----
    public function kirim($user_id, $judul, $pesan, $tipe = 'info', $tautan = null) {
        $query = "INSERT INTO notifications (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $judul, $pesan, $tipe, $tautan);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Ambil notifikasi pengguna (terbaru dulu) -----
    public function ambil($user_id, $batas = 20) {
        $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $batas);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Hitung notifikasi belum dibaca -----
    public function hitungBelumDibaca($user_id) {
        $query = "SELECT COUNT(id) as total FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    // ----- Tandai sudah dibaca -----
    public function tandaiDibaca($id_notifikasi, $user_id) {
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "ii", $id_notifikasi, $user_id);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Tandai semua sudah dibaca -----
    public function tandaiSemuaDibaca($user_id) {
        $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Cek apakah notifikasi dengan judul + tautan tertentu sudah ada hari ini (mencegah duplikat) -----
    public function sudahAdaHariIni($user_id, $judul, $tautan = null) {
        if ($tautan !== null) {
            $query = "SELECT COUNT(id) as total FROM notifications WHERE user_id = ? AND judul = ? AND link = ? AND DATE(created_at) = CURDATE()";
            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $judul, $tautan);
        } else {
            $query = "SELECT COUNT(id) as total FROM notifications WHERE user_id = ? AND judul = ? AND link IS NULL AND DATE(created_at) = CURDATE()";
            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param($stmt, "is", $user_id, $judul);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return ($row['total'] ?? 0) > 0;
    }

    // ----- Hapus notifikasi lama (lebih dari 30 hari) -----
    public function bersihkanLama() {
        $query = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        return mysqli_query($this->db, $query);
    }
}

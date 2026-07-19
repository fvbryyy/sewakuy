<?php
require_once __DIR__ . '/Model.php';

class Setting extends Model {

    // ----- Ambil semua pengaturan -----
    public function ambilSemua() {
        $result = mysqli_query($this->db, "SELECT setting_key, setting_value FROM settings");
        $pengaturan = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pengaturan[$row['setting_key']] = $row['setting_value'];
        }
        return $pengaturan;
    }

    // ----- Ambil satu pengaturan berdasarkan kunci -----
    public function ambil($kunci) {
        $stmt = mysqli_prepare($this->db, "SELECT setting_value FROM settings WHERE setting_key = ?");
        mysqli_stmt_bind_param($stmt, "s", $kunci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'] ?? null;
    }

    // ----- Simpan pengaturan (insert atau update) -----
    public function simpan($kunci, $nilai) {
        $stmt = mysqli_prepare($this->db, "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        mysqli_stmt_bind_param($stmt, 'ss', $kunci, $nilai);
        return mysqli_stmt_execute($stmt);
    }
}

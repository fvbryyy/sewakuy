<?php
require_once __DIR__ . '/Model.php';

class User extends Model {

    // ----- Daftar pengguna baru -----
    public function daftar($nama, $email, $kata_sandi, $no_hp, $alamat) {
        $kata_sandi_terhash = password_hash($kata_sandi, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($this->db, "INSERT INTO users (nama, email, password, role, no_hp, alamat) VALUES (?, ?, ?, 'customer', ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $kata_sandi_terhash, $no_hp, $alamat);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Masuk / login -----
    public function masuk($email, $kata_sandi) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($kata_sandi, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    // ----- Tampilkan semua pelanggan -----
    public function tampilSemuaPelanggan() {
        $query = "SELECT id, nama, email, no_hp, alamat FROM users WHERE role = 'customer' ORDER BY id DESC";
        return mysqli_query($this->db, $query);
    }

    // ----- Hitung jumlah pelanggan -----
    public function hitungPelanggan() {
        $query = "SELECT COUNT(id) as total FROM users WHERE role = 'customer'";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }

    // ----- Ambil profil pengguna berdasarkan ID -----
    public function ambilProfil($id) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // ----- Perbarui profil -----
    public function perbaruiProfil($id, $nama, $no_hp, $alamat, $kata_sandi_baru = "") {
        if (!empty($kata_sandi_baru)) {
            $kata_sandi_terhash = password_hash($kata_sandi_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($this->db, "UPDATE users SET nama=?, no_hp=?, alamat=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $nama, $no_hp, $alamat, $kata_sandi_terhash, $id);
        } else {
            $stmt = mysqli_prepare($this->db, "UPDATE users SET nama=?, no_hp=?, alamat=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $nama, $no_hp, $alamat, $id);
        }
        return mysqli_stmt_execute($stmt);
    }
}
?>

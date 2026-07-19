<?php
require_once __DIR__ . '/Model.php';

class Product extends Model {

    // ----- Tampilkan semua produk beserta kategori -----
    public function tampilSemua() {
        $query = "SELECT produk.*, kategori.nama_kategori
                  FROM produk
                  LEFT JOIN kategori ON produk.kategori_id = kategori.id
                  ORDER BY produk.id DESC";
        return mysqli_query($this->db, $query);
    }

    // ----- Tampilkan daftar kategori -----
    public function tampilKategori() {
        $query = "SELECT * FROM kategori";
        return mysqli_query($this->db, $query);
    }

    // ----- Tambah produk baru -----
    public function tambah($kategori_id, $nama, $deskripsi, $harga, $stok, $gambar) {
        $stmt = mysqli_prepare($this->db, "INSERT INTO produk (kategori_id, nama_produk, deskripsi, harga_perhari, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issdis", $kategori_id, $nama, $deskripsi, $harga, $stok, $gambar);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Ambil detail produk -----
    public function ambilDetail($id) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM produk WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // ----- Ubah produk -----
    public function ubah($id, $kategori_id, $nama, $deskripsi, $harga, $stok, $gambar) {
        if ($gambar != "") {
            $stmt = mysqli_prepare($this->db, "UPDATE produk SET kategori_id=?, nama_produk=?, deskripsi=?, harga_perhari=?, stok=?, gambar=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "issdisi", $kategori_id, $nama, $deskripsi, $harga, $stok, $gambar, $id);
        } else {
            $stmt = mysqli_prepare($this->db, "UPDATE produk SET kategori_id=?, nama_produk=?, deskripsi=?, harga_perhari=?, stok=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "issdii", $kategori_id, $nama, $deskripsi, $harga, $stok, $id);
        }
        return mysqli_stmt_execute($stmt);
    }

    // ----- Hapus produk -----
    public function hapus($id) {
        $stmt = mysqli_prepare($this->db, "DELETE FROM produk WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Filter produk berdasarkan kategori -----
    public function tampilPerKategori($kategori_id) {
        $query = "SELECT produk.*, kategori.nama_kategori
                  FROM produk
                  LEFT JOIN kategori ON produk.kategori_id = kategori.id
                  WHERE produk.kategori_id = ?
                  ORDER BY produk.id DESC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $kategori_id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Cari produk berdasarkan kata kunci -----
    public function cari($kata_kunci) {
        $kata_kunci = "%$kata_kunci%";
        $stmt = mysqli_prepare($this->db, "SELECT produk.*, kategori.nama_kategori
                  FROM produk
                  LEFT JOIN kategori ON produk.kategori_id = kategori.id
                  WHERE produk.nama_produk LIKE ?
                  ORDER BY produk.id DESC");
        mysqli_stmt_bind_param($stmt, "s", $kata_kunci);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Hitung jumlah produk -----
    public function hitungProduk() {
        $query = "SELECT COUNT(id) as total FROM produk";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
}
?>

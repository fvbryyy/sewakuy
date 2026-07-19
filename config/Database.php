<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db_name = "dbsewakuy";
    public $conn;

    // ----- membuka koneksi -----
    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db_name);
        if (!$this->conn) {
            die("Koneksi basis data gagal: " . mysqli_connect_error());
        }
        mysqli_set_charset($this->conn, "utf8mb4");
    }

    // ----- menutup koneksi -----
    public function __destruct() {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}
?>
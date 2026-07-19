<?php
require_once __DIR__ . '/../config/Database.php';

class Model {
    protected $db;
    protected $dbContainer;

    public function __construct() {
        $this->dbContainer = new Database();
        $this->db = $this->dbContainer->conn;
    }

    public function __destruct() {
        // nutup koneksi MySQL otomatis
    }
}

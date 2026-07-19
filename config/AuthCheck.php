<?php

function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function cekAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: ../auth/login.php");
        exit();
    }
}

function cekPelanggan() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
        header("Location: ../auth/login.php");
        exit();
    }
}

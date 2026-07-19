<?php
session_start();
require_once '../classes/User.php';

$user = new User();

// ----- Register -----
if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    if ($user->daftar($nama, $email, $password, $no_hp, $alamat)) {
        $_SESSION['flash'] = ['pesan' => 'Pendaftaran berhasil! Silakan masuk.', 'tipe' => 'success'];
        header("Location: ../auth/login.php");
        exit();
    } else {
        $_SESSION['flash'] = ['pesan' => 'Email sudah terdaftar, gunakan email lain!', 'tipe' => 'danger'];
        header("Location: ../auth/register.php");
        exit();
    }
}

// ----- Login -----
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $dataUser = $user->masuk($email, $password);

    if ($dataUser) {
        $_SESSION['user_id'] = $dataUser['id'];
        $_SESSION['nama']    = $dataUser['nama'];
        $_SESSION['role']    = $dataUser['role'];

        if ($dataUser['role'] == 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../customer/index.php");
        }
        exit();
    } else {
        $_SESSION['flash'] = ['pesan' => 'Email atau kata sandi salah!', 'tipe' => 'danger'];
        header("Location: ../auth/login.php");
        exit();
    }
}
?>
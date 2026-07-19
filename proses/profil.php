<?php
session_start();
require_once '../config/AuthCheck.php';
cekLogin();

require_once '../classes/User.php';
$userObj = new User();

// ----- Update profil -----
if (isset($_POST['update_profil'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $kata_sandi_baru = trim($_POST['password_baru']);

    if ($id == $_SESSION['user_id']) {
        if ($userObj->perbaruiProfil($id, $nama, $no_hp, $alamat, $kata_sandi_baru)) {
            $_SESSION['nama'] = $nama;
            $_SESSION['flash'] = ['pesan' => 'Profil berhasil diperbarui!', 'tipe' => 'success'];
        } else {
            $_SESSION['flash'] = ['pesan' => 'Gagal memperbarui profil!', 'tipe' => 'danger'];
        }
    } else {
        $_SESSION['flash'] = ['pesan' => 'Akses tidak sah!', 'tipe' => 'danger'];
    }
    header("Location: ../customer/profil.php");
    exit();
}
?>
<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

if (!isset($_POST['simpan_pengaturan'])) {
    header("Location: ../admin/pengaturan.php");
    exit();
}

require_once '../classes/Setting.php';
$modelPengaturan = new Setting();

$fields = ['nama_toko', 'alamat_toko', 'no_hp_toko', 'jam_operasional', 'denda_per_hari', 'link_maps'];

foreach ($fields as $key) {
    if (!isset($_POST[$key]) || trim($_POST[$key]) === '') {
        $_SESSION['flash'] = ['pesan' => 'Semua kolom harus diisi!', 'tipe' => 'danger'];
        header("Location: ../admin/pengaturan.php");
        exit();
    }
}

foreach ($fields as $key) {
    $modelPengaturan->simpan($key, trim($_POST[$key]));
}

$_SESSION['flash'] = ['pesan' => 'Pengaturan toko berhasil disimpan!', 'tipe' => 'success'];
header("Location: ../admin/pengaturan.php");
exit();

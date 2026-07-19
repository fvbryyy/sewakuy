<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();
require_once '../classes/Product.php';

$product = new Product();

$ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
$maks_ukuran = 5 * 1024 * 1024;

// ----- Tambah produk -----
if (isset($_POST['tambah_produk'])) {
    $nama        = $_POST['nama_produk'];
    $kategori_id = $_POST['kategori_id'];
    $harga       = $_POST['harga'];
    $stok        = $_POST['stok'];
    $deskripsi   = $_POST['deskripsi'];

    $gambar    = $_FILES['gambar']['name'];
    $tmp_name  = $_FILES['gambar']['tmp_name'];
    $ukuran    = $_FILES['gambar']['size'];
    $ekstensi  = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        $_SESSION['flash'] = ['pesan' => 'Format gambar tidak diizinkan! Gunakan JPG, JPEG, PNG, atau WEBP.', 'tipe' => 'danger'];
        header("Location: ../admin/produk.php");
        exit();
    }
    if ($ukuran > $maks_ukuran) {
        $_SESSION['flash'] = ['pesan' => 'Ukuran gambar maksimal 5MB!', 'tipe' => 'danger'];
        header("Location: ../admin/produk.php");
        exit();
    }

    $nama_baru = time() . "_" . rand(100, 999) . "." . $ekstensi;

    if (move_uploaded_file($tmp_name, "../public/produk/" . $nama_baru)) {
        if ($product->tambah($kategori_id, $nama, $deskripsi, $harga, $stok, $nama_baru)) {
            $_SESSION['flash'] = ['pesan' => 'Produk berhasil ditambahkan!', 'tipe' => 'success'];
        } else {
            $_SESSION['flash'] = ['pesan' => 'Gagal menyimpan data!', 'tipe' => 'danger'];
        }
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal mengunggah gambar!', 'tipe' => 'danger'];
    }
    header("Location: ../admin/produk.php");
    exit();
}

// ----- Edit produk -----
if (isset($_POST['edit_produk'])) {
    $id          = $_POST['id'];
    $nama        = $_POST['nama_produk'];
    $kategori_id = $_POST['kategori_id'];
    $harga       = $_POST['harga'];
    $stok        = $_POST['stok'];
    $deskripsi   = $_POST['deskripsi'];

    $gambar   = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];
    $ukuran   = $_FILES['gambar']['size'];

    if ($gambar != "") {
        $ekstensi  = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));
        if (!in_array($ekstensi, $ekstensi_diizinkan)) {
            $_SESSION['flash'] = ['pesan' => 'Format gambar tidak diizinkan! Gunakan JPG, JPEG, PNG, atau WEBP.', 'tipe' => 'danger'];
            header("Location: ../admin/produk.php");
            exit();
        }
        if ($ukuran > $maks_ukuran) {
            $_SESSION['flash'] = ['pesan' => 'Ukuran gambar maksimal 5MB!', 'tipe' => 'danger'];
            header("Location: ../admin/produk.php");
            exit();
        }
        $nama_baru = time() . "_" . rand(100, 999) . "." . $ekstensi;
        move_uploaded_file($tmp_name, "../public/produk/" . $nama_baru);
    } else {
        $nama_baru = "";
    }

    if ($product->ubah($id, $kategori_id, $nama, $deskripsi, $harga, $stok, $nama_baru)) {
        $_SESSION['flash'] = ['pesan' => 'Produk berhasil diubah!', 'tipe' => 'success'];
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal mengubah produk!', 'tipe' => 'danger'];
    }
    header("Location: ../admin/produk.php");
    exit();
}

// ----- Hapus produk -----
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    if ($product->hapus($id)) {
        $_SESSION['flash'] = ['pesan' => 'Produk berhasil dihapus!', 'tipe' => 'success'];
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal menghapus produk!', 'tipe' => 'danger'];
    }
    header("Location: ../admin/produk.php");
    exit();
}
?>
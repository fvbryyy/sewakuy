<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

// ----- Tambah keranjang dari katalog -----
if (isset($_POST['tambah_keranjang'])) {
    require_once '../classes/Product.php';
    $product = new Product();

    $produk_id = $_POST['produk_id'];
    $jumlah = 1;
    $detail = $product->ambilDetail($produk_id);
    $stok_tersedia = $detail ? (int)$detail['stok'] : 0;

    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }

    $qty_di_keranjang = isset($_SESSION['keranjang'][$produk_id]) ? (int)$_SESSION['keranjang'][$produk_id] : 0;

    if (($qty_di_keranjang + $jumlah) > $stok_tersedia) {
        $_SESSION['flash'] = [
            'pesan' => 'Stok tidak mencukupi! Tersedia hanya ' . $stok_tersedia . ' unit.',
            'tipe' => 'danger'
        ];
        header("Location: ../customer/katalog.php");
        exit();
    }

    $_SESSION['keranjang'][$produk_id] = $qty_di_keranjang + $jumlah;

    $_SESSION['flash'] = [
        'pesan' => 'Berhasil dimasukkan ke keranjang!',
        'tipe' => 'success'
    ];
    header("Location: ../customer/katalog.php");
    exit();
}

// ----- Ubah qty di keranjang (+ / -) -----
if (isset($_GET['aksi']) && $_GET['aksi'] == 'ubah_qty') {
    $id = $_GET['id'];
    $operasi = $_GET['operasi'];

    if (isset($_SESSION['keranjang'][$id])) {
        if ($operasi == 'tambah') {
            require_once '../classes/Product.php';
            $product = new Product();
            $detail = $product->ambilDetail($id);
            $stok_tersedia = $detail ? (int)$detail['stok'] : 0;

            if ((int)$_SESSION['keranjang'][$id] >= $stok_tersedia) {
                $_SESSION['flash'] = [
                    'pesan' => 'Stok tidak mencukupi! Tersedia hanya ' . $stok_tersedia . ' unit.',
                    'tipe' => 'danger'
                ];
                header("Location: ../customer/keranjang.php");
                exit();
            }

            $_SESSION['keranjang'][$id] += 1;
        } else if ($operasi == 'kurang') {
            $_SESSION['keranjang'][$id] -= 1;

            if ($_SESSION['keranjang'][$id] <= 0) {
                unset($_SESSION['keranjang'][$id]);
            }
        }
    }
    header("Location: ../customer/keranjang.php");
    exit();
}

// ----- Hapus item dari keranjang -----
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_item') {
    $id = $_GET['id'];
    unset($_SESSION['keranjang'][$id]);
    $_SESSION['flash'] = ['pesan' => 'Barang berhasil dihapus dari keranjang.', 'tipe' => 'info'];
    header("Location: ../customer/keranjang.php");
    exit();
}

// ----- Proses checkout -----
if (isset($_POST['proses_checkout'])) {
    require_once '../classes/Transaction.php';
    require_once '../classes/Notification.php';
    $transaction = new Transaction();
    $notif_checkout = new Notification();

    $user_id      = $_SESSION['user_id'];
    $tgl_mulai    = $_POST['tgl_mulai'];
    $tgl_selesai  = $_POST['tgl_selesai'];
    $total_hari   = $_POST['total_hari'];
    $total_biaya  = $_POST['total_biaya'];
    $metode_bayar = $_POST['metode_bayar'] ?? 'transfer';

    $items = json_decode($_POST['serialized_items'], true);
    if (!is_array($items)) {
        $_SESSION['flash'] = ['pesan' => 'Data item tidak valid!', 'tipe' => 'danger'];
        header("Location: ../customer/checkout.php");
        exit();
    }

    if ($metode_bayar == 'tunai') {
        // ----- Tunai: langsung simpan tanpa upload bukti -----
        $tx_id = $transaction->buatTransaksi($user_id, $tgl_mulai, $tgl_selesai, $total_hari, $total_biaya, null, $items, 'tunai');
        if ($tx_id) {
            // Notifikasi ke admin: pesanan baru tunai
            $admin_ids = $transaction->ambilIdAdmin();
            foreach ($admin_ids as $admin_id) {
                $notif_checkout->kirim($admin_id, 'Pesanan Baru', "Pesanan #SK-{$tx_id} (Tunai) dari {$_SESSION['nama']} sudah masuk. Siap diambil.", 'info', "../admin/transaksi.php?status=siap_diambil");
            }
            unset($_SESSION['keranjang']);
            $_SESSION['flash'] = ['pesan' => 'Pesanan berhasil dikirim! Silakan datang ke toko untuk membayar dan mengambil alat.', 'tipe' => 'success'];
            header("Location: ../customer/riwayat.php");
        } else {
            $_SESSION['flash'] = ['pesan' => 'Gagal memproses transaksi!', 'tipe' => 'danger'];
            header("Location: ../customer/checkout.php");
        }
    } else {
        // ----- Transfer QRIS: upload bukti bayar -----
        $bukti     = $_FILES['bukti_bayar']['name'];
        $tmp_name  = $_FILES['bukti_bayar']['tmp_name'];
        $ukuran    = $_FILES['bukti_bayar']['size'];
        $ekstensi  = strtolower(pathinfo($bukti, PATHINFO_EXTENSION));

        $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
        if (!in_array($ekstensi, $ekstensi_diizinkan)) {
            $_SESSION['flash'] = ['pesan' => 'Format bukti bayar tidak diizinkan! Gunakan JPG, JPEG, PNG, atau PDF.', 'tipe' => 'danger'];
            header("Location: ../customer/checkout.php");
            exit();
        }
        if ($ukuran > 5 * 1024 * 1024) {
            $_SESSION['flash'] = ['pesan' => 'Ukuran bukti bayar maksimal 5MB!', 'tipe' => 'danger'];
            header("Location: ../customer/checkout.php");
            exit();
        }

        $nama_bukti_baru = "BUKTI_" . time() . "_" . rand(100, 999) . "." . $ekstensi;

        if (move_uploaded_file($tmp_name, "../public/uploads/" . $nama_bukti_baru)) {

            $tx_id = $transaction->buatTransaksi($user_id, $tgl_mulai, $tgl_selesai, $total_hari, $total_biaya, $nama_bukti_baru, $items, 'transfer');

                if ($tx_id) {
                    // Notifikasi ke admin: pesanan baru QRIS
                $admin_ids = $transaction->ambilIdAdmin();
                foreach ($admin_ids as $admin_id) {
                    $notif_checkout->kirim($admin_id, 'Pesanan Baru', "Pesanan #SK-{$tx_id} (QRIS) dari {$_SESSION['nama']} menunggu persetujuan.", 'info', "../admin/transaksi.php?status=menunggu_acc");
                }

                unset($_SESSION['keranjang']);

                $_SESSION['flash'] = ['pesan' => 'Pesanan berhasil dikirim! Menunggu persetujuan oleh Admin.', 'tipe' => 'success'];
                header("Location: ../customer/riwayat.php");
            } else {
                $_SESSION['flash'] = ['pesan' => 'Gagal memproses transaksi!', 'tipe' => 'danger'];
                header("Location: ../customer/checkout.php");
            }
        } else {
            $_SESSION['flash'] = ['pesan' => 'Gagal mengunggah bukti pembayaran!', 'tipe' => 'danger'];
            header("Location: ../customer/checkout.php");
        }
    }
    exit();
}
?>
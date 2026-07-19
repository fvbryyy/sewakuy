<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/Transaction.php';
require_once '../classes/Notification.php';
$tx = new Transaction();
$notif = new Notification();

if ((!isset($_GET['id']) && !isset($_POST['id'])) || (!isset($_GET['aksi']) && !isset($_POST['aksi']))) {
    header("Location: ../admin/transaksi.php");
    exit();
}

$id = $_GET['id'] ?? $_POST['id'];
$aksi = $_GET['aksi'] ?? $_POST['aksi'];

// ----- ACC pembayaran -----
if ($aksi == 'acc') {
    if ($tx->ubahStatus($id, 'siap_diambil')) {
        $nota = $tx->ambilNotaInduk($id);
        if ($nota) $notif->kirim($nota['user_id'], 'Pesanan Disetujui', "Pesanan #SK-{$id} telah disetujui. Silakan ambil alat di toko.", 'success', "../customer/riwayat.php");
        $_SESSION['flash'] = ['pesan' => 'Pembayaran berhasil disetujui! Status nota berubah menjadi Siap Diambil.', 'tipe' => 'success'];
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal memproses persetujuan.', 'tipe' => 'danger'];
    }
}

// ----- Tolak pesanan -----
elseif ($aksi == 'tolak') {
    $catatan = isset($_POST['catatan_tolak']) ? trim($_POST['catatan_tolak']) : '';
    if ($tx->tolakTransaksi($id, $catatan)) {
        $nota = $tx->ambilNotaInduk($id);
        $pesan_notif = "Pesanan #SK-{$id} ditolak.";
        if ($catatan !== '') {
            $pesan_notif .= " Alasan: {$catatan}";
        }
        if ($nota) $notif->kirim($nota['user_id'], 'Pesanan Ditolak', $pesan_notif, 'danger', "../customer/riwayat.php");
        $_SESSION['flash'] = ['pesan' => 'Pesanan berhasil ditolak.', 'tipe' => 'warning'];
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal menolak pesanan.', 'tipe' => 'danger'];
    }
}

// ----- Serahkan alat -----
elseif ($aksi == 'serahkan') {
    if ($tx->ubahStatus($id, 'disewa')) {
        $tx->kelolaStokAlat($id, 'kurangi');
        $nota = $tx->ambilNotaInduk($id);
        if ($nota) $notif->kirim($nota['user_id'], 'Alat Diserahkan', "Alat untuk pesanan #SK-{$id} telah diserahkan. Selamat menggunakan!", 'info', "../customer/invoice.php?id={$id}");
        $_SESSION['flash'] = ['pesan' => 'Alat resmi diserahkan! Stok inventaris otomatis berkurang.', 'tipe' => 'success'];
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal menyerahkan alat.', 'tipe' => 'danger'];
    }
}

// ----- Selesai / kembalikan alat (otomatis hitung denda) -----
elseif ($aksi == 'selesai') {
    if ($tx->prosesKembali($id)) {
        $tx->kelolaStokAlat($id, 'kembalikan');
        $nota = $tx->ambilNotaInduk($id);
        $msg = 'Transaksi Selesai! Alat telah kembali dan stok dikembalikan utuh.';
        $pesan_notif = "Alat untuk pesanan #SK-{$id} telah dikembalikan.";
        if ($nota && ($nota['hari_telat'] ?? 0) > 0) {
            $denda_text = ' (Telat ' . $nota['hari_telat'] . ' hari — Denda Rp ' . number_format($nota['total_denda'], 0, ',', '.') . ')';
            $msg .= $denda_text;
            $pesan_notif .= $denda_text;
        }
        if ($nota) $notif->kirim($nota['user_id'], 'Pesanan Selesai', $pesan_notif, 'success', "../customer/invoice.php?id={$id}");
        $_SESSION['flash'] = ['pesan' => $msg, 'tipe' => 'success'];
    } else {
        $_SESSION['flash'] = ['pesan' => 'Gagal menyelesaikan transaksi.', 'tipe' => 'danger'];
    }
}

header("Location: ../admin/transaksi.php");
exit();
?>
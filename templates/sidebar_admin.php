<?php
$current_page = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/../classes/Transaction.php';
require_once __DIR__ . '/../classes/Notification.php';
$tx_sidebar = new Transaction();
$pending_count = $tx_sidebar->hitungTransaksiPerStatus('menunggu_acc');

$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $notif_sidebar = new Notification();
    $notif_count = $notif_sidebar->hitungBelumDibaca($_SESSION['user_id']);
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="sidebar">
    <div class="sidebar-brand">Admin Sewa<span>kuy </span></div>
    
    <ul class="sidebar-menu">
        <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i> Beranda
            <?php if ($notif_count > 0): ?>
                <span class="notif-badge"><?= $notif_count ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="pengguna.php" class="<?= ($current_page == 'pengguna.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> Data Pelanggan</a></li>
        <li><a href="produk.php" class="<?= ($current_page == 'produk.php') ? 'active' : ''; ?>"><i class="fas fa-box"></i> Kelola Produk</a></li>
        <li>
            <a href="transaksi.php" class="<?= ($current_page == 'transaksi.php') ? 'active' : ''; ?>">
                <i class="fas fa-receipt"></i> Kelola Transaksi
                <?php if ($pending_count > 0): ?>
                    <span class="notif-badge"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="laporan.php" class="<?= ($current_page == 'laporan.php') ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Laporan</a></li>
        <li><a href="pengaturan.php" class="<?= ($current_page == 'pengaturan.php') ? 'active' : ''; ?>"><i class="fas fa-gear"></i> Pengaturan</a></li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="../auth/logout.php" class="btn-logout-admin"><i class="fas fa-right-from-bracket"></i> Keluar</a>
    </div>
</div>

<div class="main-content">

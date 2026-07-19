<?php
$current_page = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/../classes/Transaction.php';
require_once __DIR__ . '/../classes/Notification.php';

$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $tx_nav = new Transaction();
    $notif_nav = new Notification();
    // ----- Auto-check notifikasi customer -----
    $tx_nav->cekNotifikasiH1($_SESSION['user_id'], $notif_nav);
    $tx_nav->cekNotifikasiTerlambat($_SESSION['user_id'], $notif_nav);
    $notif_count = $notif_nav->hitungBelumDibaca($_SESSION['user_id']);
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<nav class="navbar">
    <div class="logo">sewa<span>kuy</span></div> 
    
    <div class="nav-links">
        <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">Beranda</a> 
        <a href="katalog.php" class="<?= ($current_page == 'katalog.php') ? 'active' : ''; ?>">Katalog</a> 
        
        <a href="riwayat.php" class="<?= ($current_page == 'riwayat.php') ? 'active' : ''; ?>">Riwayat Sewa</a> 
        
        <a href="keranjang.php" class="nav-cart-link <?= ($current_page == 'keranjang.php') ? 'active' : ''; ?>">
            <i class="fas fa-cart-shopping"></i><?php if (isset($_SESSION['keranjang']) && count($_SESSION['keranjang']) > 0) { $total_qty = array_sum($_SESSION['keranjang']); ?><span class="nav-cart-badge"><?= $total_qty; ?></span><?php } ?>
        </a> 

        <a href="notifikasi.php" class="nav-cart-link <?= ($current_page == 'notifikasi.php') ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i>
            <?php if ($notif_count > 0): ?>
                <span class="nav-cart-badge"><?= $notif_count; ?></span>
            <?php endif; ?>
        </a>
        
        <div class="nav-dropdown">
            <span class="user-greet">Halo, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?></strong> <i class="fas fa-chevron-down"></i></span>
            <div class="nav-dropdown-menu">
                <a href="profil.php"><i class="fas fa-user"></i> Edit Profil</a>
                <a href="../auth/logout.php"><i class="fas fa-right-from-bracket"></i> Keluar</a>
            </div>
        </div>
    </div>
</nav>

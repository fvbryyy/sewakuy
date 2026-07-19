<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/User.php';
require_once '../classes/Product.php';
require_once '../classes/Transaction.php';
require_once '../classes/Notification.php';
require_once '../helpers/status_badge.php';

$user = new User();
$product = new Product();
$tx = new Transaction();
$notif = new Notification();

$admin_id = $_SESSION['user_id'];
$tx->cekNotifikasiPesananBaru($admin_id, $notif);
$tx->cekNotifikasiAmbilHariIni($admin_id, $notif);
$tx->cekNotifikasiKembaliHariIni($admin_id, $notif);
$tx->cekNotifikasiTerlambatAdmin($admin_id, $notif);

$total_customer = $user->hitungPelanggan();
$total_produk   = $product->hitungProduk();
$pending_acc    = $tx->hitungTransaksiPerStatus('menunggu_acc');
$sedang_disewa  = $tx->hitungTransaksiPerStatus('disewa');
$ambil_hari_ini = $tx->hitungAmbilHariIni();
$kembali_hari_ini = $tx->hitungKembaliHariIni();
$terlambat      = $tx->hitungTerlambat();
$transaksi_terbaru = $tx->tampilTerbaru(5);

$daftar_notif = $notif->ambil($admin_id, 8);
$belum_dibaca = $notif->hitungBelumDibaca($admin_id);

if (isset($_GET['aksi_notif']) && $_GET['aksi_notif'] == 'baca_semua') {
    $notif->tandaiSemuaDibaca($admin_id);
    header("Location: index.php");
    exit();
}
if (isset($_GET['baca_notif'])) {
    $notif->tandaiDibaca((int)$_GET['baca_notif'], $admin_id);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda Admin - Sewakuy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../public/css/admin.css?v=<?= date('YmdHis'); ?>">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/sidebar_admin.php'; ?>

<div class="admin-page-header">
    <h2 class="admin-page-title">Beranda</h2>
    <p class="admin-page-subtitle">Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></strong>.</p>
</div>

    <!-- Row 1: Main Stats -->
<div class="stat-row">
    <div class="stat-card-v2">
        <div class="scv2-icon icon-purple"><i class="fas fa-users"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Total Pelanggan</span>
            <span class="scv2-value"><?= $total_customer; ?></span>
        </div>
    </div>
    <div class="stat-card-v2">
        <div class="scv2-icon icon-blue"><i class="fas fa-box"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Total Alat</span>
            <span class="scv2-value"><?= $total_produk; ?></span>
        </div>
    </div>
    <div class="stat-card-v2">
        <div class="scv2-icon icon-amber"><i class="fas fa-clock"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Menunggu Persetujuan</span>
            <span class="scv2-value"><?= $pending_acc; ?></span>
        </div>
    </div>
    <div class="stat-card-v2">
        <div class="scv2-icon icon-green"><i class="fas fa-check-circle"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Sedang Disewa</span>
            <span class="scv2-value"><?= $sedang_disewa; ?></span>
        </div>
    </div>
</div>

<!-- Row 2: Today's Activity -->
<div class="section-label">Aktivitas Hari Ini</div>
<div class="stat-row cols-3">
    <div class="stat-card-v2">
        <div class="scv2-icon icon-cyan"><i class="fas fa-arrow-right-from-bracket"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Ambil Hari Ini</span>
            <span class="scv2-value"><?= $ambil_hari_ini; ?></span>
        </div>
    </div>
    <div class="stat-card-v2">
        <div class="scv2-icon icon-amber"><i class="fas fa-arrow-right-to-bracket"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Kembali Hari Ini</span>
            <span class="scv2-value"><?= $kembali_hari_ini; ?></span>
        </div>
    </div>
    <div class="stat-card-v2">
        <div class="scv2-icon icon-red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="scv2-info">
            <span class="scv2-label">Terlambat</span>
            <span class="scv2-value"><?= $terlambat; ?></span>
        </div>
    </div>
</div>

<!-- Panels -->
<div class="panels-row">

    <!-- Notifikasi -->
    <div class="panel-card">
        <div class="panel-head">
            <h3><i class="fas fa-bell"></i> Notifikasi</h3>
            <?php if ($belum_dibaca > 0): ?>
                <a href="index.php?aksi_notif=baca_semua">Tandai semua dibaca</a>
            <?php endif; ?>
        </div>
        <?php if (mysqli_num_rows($daftar_notif) == 0): ?>
            <div class="panel-empty"><i class="fas fa-bell-slash"></i><span>Belum ada notifikasi</span></div>
        <?php else: ?>
            <div class="notif-scroll">
                <?php while ($n = mysqli_fetch_assoc($daftar_notif)): ?>
                    <a href="<?= $n['link'] ? 'index.php?baca_notif=' . $n['id'] : '#'; ?>" class="notif-row <?= $n['is_read'] ? '' : 'unread'; ?>">
                        <span class="notif-dot-t" data-t="<?= $n['tipe']; ?>"></span>
                        <div class="notif-txt">
                            <strong><?= htmlspecialchars($n['judul']); ?></strong>
                            <p><?= htmlspecialchars($n['pesan']); ?></p>
                            <small><?= date('d M Y H:i', strtotime($n['created_at'])); ?></small>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="panel-card">
        <div class="panel-head">
            <h3><i class="fas fa-receipt"></i> Transaksi Terbaru</h3>
            <a href="transaksi.php">Lihat Semua</a>
        </div>
        <?php if (mysqli_num_rows($transaksi_terbaru) == 0): ?>
            <div class="panel-empty"><i class="fas fa-inbox"></i><span>Belum ada transaksi</span></div>
        <?php else: ?>
            <div class="notif-scroll">
                <?php while($row = mysqli_fetch_assoc($transaksi_terbaru)): ?>
                    <div class="tx-row">
                        <div class="tx-left">
                            <span class="tx-id">#SK-<?= $row['id']; ?></span>
                            <span class="tx-name"><?= htmlspecialchars($row['nama']); ?></span>
                        </div>
                        <div class="tx-right">
                            <span class="tx-price">Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?></span>
                            <?= badgeStatus($row['status']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

</div>

</body>
</html>

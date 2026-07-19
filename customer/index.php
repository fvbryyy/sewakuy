<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

require_once '../classes/Transaction.php';
require_once '../classes/Notification.php';
require_once '../helpers/status_badge.php';
$tx = new Transaction();
$notif = new Notification();

$tx->cekNotifikasiH1($_SESSION['user_id'], $notif);
$tx->cekNotifikasiTerlambat($_SESSION['user_id'], $notif);
$unread = $notif->hitungBelumDibaca($_SESSION['user_id']);

$riwayat = $tx->tampilRiwayatPelanggan($_SESSION['user_id']);

$total_sewa = 0;
$menunggu_acc = 0;
$sedang_disewa = 0;
$selesai = 0;
$ditolak = 0;
$transaksi_terbaru = [];

while ($row = mysqli_fetch_assoc($riwayat)) {
    $total_sewa++;
    if ($row['status'] == 'menunggu_acc') $menunggu_acc++;
    elseif ($row['status'] == 'disewa' || $row['status'] == 'siap_diambil') $sedang_disewa++;
    elseif ($row['status'] == 'selesai') $selesai++;
    elseif ($row['status'] == 'ditolak') $ditolak++;
    if (count($transaksi_terbaru) < 5) {
        $transaksi_terbaru[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Sewakuy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../public/css/customer.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="container">

    <div class="katalog-header-landing">
        <h2 class="page-title">Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?>!</h2>
        <p class="page-subtitle">Kelola penyewaan alat multimedia kamu di sini.</p>
    </div>

    <div class="stat-row-cust">
        <div class="stat-card-v2-cust">
            <div class="scv2-icon-cust icon-blue"><i class="fas fa-receipt"></i></div>
            <div class="scv2-info-cust">
                <span class="scv2-label-cust">Total Transaksi</span>
                <span class="scv2-value-cust"><?= $total_sewa; ?></span>
            </div>
        </div>
        <div class="stat-card-v2-cust">
            <div class="scv2-icon-cust icon-amber"><i class="fas fa-clock"></i></div>
            <div class="scv2-info-cust">
                <span class="scv2-label-cust">Menunggu Persetujuan</span>
                <span class="scv2-value-cust"><?= $menunggu_acc; ?></span>
            </div>
        </div>
        <div class="stat-card-v2-cust">
            <div class="scv2-icon-cust icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="scv2-info-cust">
                <span class="scv2-label-cust">Sedang Disewa</span>
                <span class="scv2-value-cust"><?= $sedang_disewa; ?></span>
            </div>
        </div>
        <div class="stat-card-v2-cust">
            <div class="scv2-icon-cust icon-purple"><i class="fas fa-flag-checkered"></i></div>
            <div class="scv2-info-cust">
                <span class="scv2-label-cust">Selesai</span>
                <span class="scv2-value-cust"><?= $selesai; ?></span>
            </div>
        </div>
    </div>

    <?php if ($unread > 0): ?>
        <a href="notifikasi.php" class="notif-banner">
            <i class="fas fa-bell"></i>
            <span class="notif-banner-text">Ada <?= $unread; ?> notifikasi baru</span>
            <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>

    <?php if (!empty($transaksi_terbaru)): ?>
        <h3 class="section-title-md">Transaksi Terbaru</h3>
        <table class="table-responsive">
            <thead>
                <tr>
                    <th>Nota</th>
                    <th>Tanggal</th>
                    <th>Durasi</th>
                    <th>Total</th>
                    <th>Denda</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi_terbaru as $row): ?>
                    <tr>
                        <td><strong>#SK-<?= $row['id']; ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($row['tgl_transaksi'])); ?></td>
                        <td><?= $row['total_hari']; ?> hari</td>
                        <td class="text-primary fw-700">Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if (($row['total_denda'] ?? 0) > 0): ?>
                                <span class="denda-badge">Rp <?= number_format($row['total_denda'], 0, ',', '.'); ?></span>
                                <small class="denda-info">Telat <?= $row['hari_telat']; ?> hari</small>
                            <?php else: ?>
                                <span class="text-muted fs-13">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($row['metode_bayar'] ?? 'transfer') == 'tunai'): ?>
                                <span class="metode-badge tunai">Tunai</span>
                            <?php else: ?>
                                <span class="metode-badge qris">QRIS</span>
                            <?php endif; ?>
                        </td>
                        <td><?= badgeStatus($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'menunggu_acc'): ?>
                                <span class="btn-invoice-locked">Menunggu Persetujuan</span>
                            <?php elseif ($row['status'] == 'ditolak'): ?>
                                <span class="btn-invoice-locked btn-invoice-ditolak">Ditolak</span>
                            <?php else: ?>
                                <a href="invoice.php?id=<?= $row['id']; ?>" class="btn-invoice-active"><i class="fas fa-file-invoice"></i> Nota</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state mt-30">
            <p>Kamu belum punya transaksi. Yuk sewa alat sekarang!</p>
            <a href="katalog.php" class="btn-cta btn-cta-sm">Lihat Katalog</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../templates/footer.php'; ?>

</body>
</html>

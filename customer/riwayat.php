<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

require_once '../classes/Transaction.php';
require_once '../helpers/status_badge.php';
$tx = new Transaction();
$riwayat = $tx->tampilRiwayatPelanggan($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sewa - Sewakuy</title>
    <link rel="stylesheet" href="../public/css/customer.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="container">

    <div class="katalog-header-landing">
        <h2 class="page-title">Daftar Pesanan</h2>
        <p class="page-subtitle">
            Pantau status penyewaan, pembayaran, dan pengambilan alat multimedia.
        </p>
    </div>

    <?php if (mysqli_num_rows($riwayat) > 0): ?>
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
                    <th class="riwayat-nota-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                    <tr>
                        <td>
                            <strong>#SK-<?= $row['id']; ?></strong>
                        </td>
                        <td>
                            <?= date('d/m/Y H:i', strtotime($row['tgl_transaksi'])); ?>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($row['tgl_mulai'])); ?> s/d <?= date('d M Y', strtotime($row['tgl_selesai'])); ?>
                            <br>
                            <small>(<?= $row['total_hari']; ?> Hari)</small>
                        </td>
                        <td class="text-primary fw-700">
                            Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?>
                        </td>
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
                        <td>
                            <?= badgeStatus($row['status']); ?>
                        </td>
                        <td class="riwayat-nota-center">
                            <?php if ($row['status'] == 'menunggu_acc'): ?>
                                <span class="btn-invoice-locked">
                                    Menunggu Persetujuan
                                </span>
                            <?php elseif ($row['status'] == 'ditolak'): ?>
                                <?php if (!empty($row['catatan_tolak'])): ?>
                                    <span class="btn-invoice-locked btn-invoice-ditolak" title="<?= htmlspecialchars($row['catatan_tolak']); ?>">
                                        Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="btn-invoice-locked btn-invoice-ditolak">
                                        Ditolak
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="invoice.php?id=<?= $row['id']; ?>" class="btn-invoice-active">
                                    <i class="fas fa-file-invoice"></i> Lihat Nota
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <p>Kamu belum pernah melakukan transaksi sewa.</p>
            <a href="katalog.php" class="mt-15 inline-block">
                Lihat Katalog
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../templates/footer.php'; ?>

</body>
</html>
<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/Transaction.php';
require_once '../helpers/status_badge.php';
$tx = new Transaction();

$periode = $_GET['periode'] ?? 'bulanan';

if ($periode == 'harian') {
    $tgl = $_GET['tanggal'] ?? date('Y-m-d');
    $tgl_awal = $tgl;
    $tgl_akhir = $tgl;
} elseif ($periode == 'bulanan') {
    $bulan = $_GET['bulan'] ?? date('Y-m');
    $tgl_awal = $bulan . '-01';
    $tgl_akhir = date('Y-m-t', strtotime($tgl_awal));
} elseif ($periode == 'rentang') {
    $tgl_awal = $_GET['dari'] ?? date('Y-m') . '-01';
    $tgl_akhir = $_GET['sampai'] ?? date('Y-m-d');
} else {
    $tgl_awal = date('Y-m') . '-01';
    $tgl_akhir = date('Y-m-t');
    $periode = 'bulanan';
}

$transaksi = $tx->laporanTransaksi($tgl_awal, $tgl_akhir);
$ringkasan = $tx->laporanRingkasan($tgl_awal, $tgl_akhir);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - Sewakuy Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css">
</head>
<body>
<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/sidebar_admin.php'; ?>

<div class="admin-page-header">
    <h2 class="admin-page-title">Laporan Transaksi</h2>
    <p class="admin-page-subtitle">Rekap pendapatan dan transaksi berdasarkan periode.</p>
</div>

<div class="filter-card">
    <form method="GET" class="filter-form">
        <label class="filter-label">
            <input type="radio" name="periode" value="harian" <?= $periode == 'harian' ? 'checked' : '' ?> onchange="this.form.submit()"> Harian
        </label>
        <label class="filter-label">
            <input type="radio" name="periode" value="bulanan" <?= $periode == 'bulanan' ? 'checked' : '' ?> onchange="this.form.submit()"> Bulanan
        </label>
        <label class="filter-label">
            <input type="radio" name="periode" value="rentang" <?= $periode == 'rentang' ? 'checked' : '' ?> onchange="this.form.submit()"> Rentang
        </label>

        <?php if ($periode == 'harian'): ?>
            <input type="date" name="tanggal" value="<?= $tgl_awal ?>" onchange="this.form.submit()" class="form-control filter-input-sm">
        <?php elseif ($periode == 'bulanan'): ?>
            <input type="month" name="bulan" value="<?= $_GET['bulan'] ?? date('Y-m') ?>" onchange="this.form.submit()" class="form-control filter-input-sm">
        <?php elseif ($periode == 'rentang'): ?>
            <input type="date" name="dari" value="<?= $tgl_awal ?>" class="form-control filter-input-sm">
            <span class="filter-sep">s/d</span>
            <input type="date" name="sampai" value="<?= $tgl_akhir ?>" class="form-control filter-input-sm">
            <button type="submit" class="btn-admin-acc filter-btn-blue">Tampilkan</button>
        <?php endif; ?>
    </form>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-card-label">Total Transaksi</div>
        <div class="summary-card-value"><?= $ringkasan['total_transaksi'] ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">Total Sewa</div>
        <div class="summary-card-value md blue">Rp <?= number_format($ringkasan['total_sewa'], 0, ',', '.'); ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">Total Denda</div>
        <div class="summary-card-value md red">Rp <?= number_format($ringkasan['total_denda'], 0, ',', '.'); ?></div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">Total Pendapatan</div>
        <div class="summary-card-value md green">Rp <?= number_format($ringkasan['total_pendapatan'], 0, ',', '.'); ?></div>
    </div>
</div>

<?php if (mysqli_num_rows($transaksi) == 0): ?>
    <div class="admin-empty">Tidak ada transaksi di periode ini.</div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nota</th>
                <th>Pelanggan</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Total</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($transaksi)): ?>
                <tr>
                    <td>#SK-<?= $row['id']; ?></td>
                    <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                    <td><?= date('d/m/Y', strtotime($row['tgl_mulai'])); ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tgl_selesai'])); ?></td>
                    <td>Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?></td>
                    <td>
                        <?php if (($row['total_denda'] ?? 0) > 0): ?>
                            <span class="text-danger fw-600">Rp <?= number_format($row['total_denda'], 0, ',', '.'); ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= badgeStatus($row['status']); ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

</div>
</body>
</html>

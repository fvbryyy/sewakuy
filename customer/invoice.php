<?php
session_start();
require_once '../config/AuthCheck.php';
cekLogin();

require_once '../classes/Transaction.php';
require_once '../classes/Setting.php';
require_once '../helpers/status_badge.php';
$tx = new Transaction();
$modelPengaturan = new Setting();
$settings = $modelPengaturan->ambilSemua();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: riwayat.php");
    exit();
}

$transaksi_id = (int)$_GET['id'];
$nota = $tx->ambilNotaInduk($transaksi_id);
$barang = $tx->ambilDetailBarangNota($transaksi_id);

if (!$nota) {
    $_SESSION['flash'] = ['pesan' => 'Nota tidak ditemukan!', 'tipe' => 'danger'];
    header("Location: riwayat.php");
    exit();
}

// ----- Proteksi akses -----
if ($_SESSION['role'] == 'customer' && $nota['user_id'] != $_SESSION['user_id']) {
    echo "Akses dilarang!";
    exit();
}

if ($nota['status'] == 'menunggu_acc') {
    $_SESSION['flash'] = ['pesan' => 'Nota belum diterbitkan. Silakan tunggu pembayaran Anda disetujui oleh Admin!', 'tipe' => 'warning'];
    header("Location: riwayat.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Resmi #SK-<?= $nota['id']; ?></title>
    <link rel="stylesheet" href="../public/css/invoice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <div class="invoice-box">
        <h2 class="invoice-title">SEWAKUY</h2>

        <table width="100%" border="0" cellpadding="5" cellspacing="0">
            <tr>
                <td colspan="2">
                    Nota Referensi: <strong>#SK-<?= $nota['id']; ?></strong><br>
                    Tanggal: <?= date('d M Y H:i', strtotime($nota['tgl_transaksi'])); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><hr></td>
            </tr>
            <tr>
                <td valign="top">
                    <strong>Detail Penyewa:</strong><br>
                    Nama: <?= htmlspecialchars($nota['nama']); ?><br>
                    No. HP: <?= $nota['no_hp']; ?><br>
                    Alamat: <?= htmlspecialchars($nota['alamat']); ?>
                </td>
                <td valign="top" align="right">
                    <strong>Waktu Sewa:</strong><br>
                    Ambil: <?= date('d M Y', strtotime($nota['tgl_mulai'])); ?><br>
                    Kembali: <?= date('d M Y', strtotime($nota['tgl_selesai'])); ?><br>
                    Durasi: <strong><?= $nota['total_hari']; ?> Hari</strong><br>
                    <br>
                    <strong>Metode Bayar:</strong><br>
                    <?= ($nota['metode_bayar'] ?? 'transfer') == 'tunai' ? 'Tunai (Bayar di Tempat)' : 'QRIS'; ?>
                </td>
            </tr>
        </table>

        <br>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Nama Kamera / Lensa</th>
                    <th>Harga / Hari</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($barang)): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama_produk']); ?></td>
                        <td>Rp <?= number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                        <td align="center"><?= $item['jumlah']; ?> unit</td>
                        <td align="right">Rp <?= number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td align="center"><strong>Status:</strong></td>
                    <td align="right">
                        <?= badgeStatus($nota['status']); ?>
                    </td>
                </tr>
                <?php if (($nota['total_denda'] ?? 0) > 0): ?>
                <tr>
                    <td colspan="2"></td>
                    <td align="center" class="text-danger">Denda Telat (<?= $nota['hari_telat']; ?> hari × Rp <?= number_format($nota['denda_per_hari'], 0, ',', '.'); ?>)</td>
                    <td align="right" class="text-danger fw-700">Rp <?= number_format($nota['total_denda'], 0, ',', '.'); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="2"></td>
                    <td align="center"><strong>TOTAL BAYAR:</strong></td>
                    <td align="right"><strong>Rp <?= number_format($nota['total_biaya'] + ($nota['total_denda'] ?? 0), 0, ',', '.'); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <br>

        <div class="info-box">
            <div class="info-box-header">
                Informasi Pengambilan Alat
            </div>
            <div class="info-box-body">
                <div class="info-box-text">
                    <div class="info-box-notice">
                        <strong>Perhatian:</strong>
                        <ol>
                            <li>Penyewa WAJIB membawa KTP / SIM fisik asli untuk jaminan saat mengambil alat di kantor.</li>
                            <li>Waktu maksimal pengembalian alat adalah pukul 22.00 WIB pada tanggal selesai sewa.</li>
                            <li>Tunjukan nota ini saat pengambilan alat.</li>
                        </ol>
                    </div>
                    <?php if (($nota['total_denda'] ?? 0) > 0): ?>
                    <div class="info-box-denda">
                        <strong>Denda Keterlambatan:</strong><br>
                        <span class="fw-700">Telat <?= $nota['hari_telat']; ?> hari — Rp <?= number_format($nota['total_denda'], 0, ',', '.'); ?></span><br>
                        <span class="fs-13 text-warning">Denda dibayar tunai saat pengembalian alat. KTP/SIM dikembalikan setelah denda lunas.</span>
                    </div>
                    <?php endif; ?>
                    <div class="info-box-address">
                        <strong>Alamat Toko:</strong><br>
                        <?= nl2br(htmlspecialchars($settings['alamat_toko'] ?? '-')); ?><br>
                        <strong>Jam Operasional:</strong> <?= htmlspecialchars($settings['jam_operasional'] ?? '-'); ?><br>
                        <strong>Kontak:</strong> <?= htmlspecialchars($settings['no_hp_toko'] ?? '-'); ?>
                    </div>
                </div>
                <?php if (!empty($settings['link_maps'])): ?>
                    <div class="info-box-qr">
                        <p>Pindai Google Maps</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode($settings['link_maps']); ?>" alt="QR Lokasi Toko">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="no-print invoice-actions">
        <a href="riwayat.php" class="inv-btn inv-btn-secondary">Kembali ke Riwayat</a>
        <button onclick="window.print()" class="inv-btn inv-btn-primary"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
    </div>

</body>
</html>
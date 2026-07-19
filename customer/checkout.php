<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

// ----- Redirect jika keranjang kosong atau bukan POST -----
if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tgl_mulai'], $_POST['total_hari'])) {
    header("Location: keranjang.php");
    exit();
}

require_once '../classes/Product.php';
require_once '../classes/Setting.php';
$product = new Product();
$modelPengaturan = new Setting();
$settings = $modelPengaturan->ambilSemua();

$tgl_mulai = $_POST['tgl_mulai'];
$total_hari = $_POST['total_hari'];

$tgl_selesai = date('Y-m-d', strtotime($tgl_mulai . " + $total_hari days"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selesaikan Pembayaran - Sewakuy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../public/css/customer.css?v=<?= date('YmdHis'); ?>">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="container">
    <div class="katalog-header-landing">
        <h2 class="page-title">Selesaikan Pembayaran</h2>
        <p class="page-subtitle">
            Periksa kembali rincian sewa dan selesaikan pembayaran QRIS untuk melanjutkan pemesanan.
        </p>
    </div>

    <div class="checkout-grid">
        <div class="checkout-box">
            <div class="sidebar-title">
                Ringkasan Pesanan
            </div>

            <table class="table-responsive">
                <thead>
                    <tr>
                        <th>Nama Alat</th>
                        <th>Harga/Hari</th>
                        <th>Jml</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $total_perhari = 0;
                $items_detail = [];

                foreach ($_SESSION['keranjang'] as $produk_id => $qty):
                    $detail = $product->ambilDetail($produk_id);
                    if (!$detail) continue;
                    $subtotal = $detail['harga_perhari'] * $qty;
                    $total_perhari += $subtotal;

                    $items_detail[$produk_id] = [
                        'qty' => $qty,
                        'harga_perhari' => $detail['harga_perhari']
                    ];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($detail['nama_produk']); ?></td>
                        <td>Rp <?= number_format($detail['harga_perhari'], 0, ',', '.'); ?></td>
                        <td><?= $qty; ?></td>
                        <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <br>

            <div class="checkout-box info-box-inner">
                <div class="form-group">
                    <label>Tanggal Pengambilan</label>
                    <input type="text" class="form-control" value="<?= date('d M Y', strtotime($tgl_mulai)); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Tanggal Pengembalian</label>
                    <input type="text" class="form-control" value="<?= date('d M Y', strtotime($tgl_selesai)); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Durasi Sewa</label>
                    <input type="text" class="form-control" value="<?= $total_hari; ?> Hari" readonly>
                </div>

                <div class="info-box">
                    <div class="info-box-header">
                        Informasi Pengambilan Alat
                    </div>
                    <div class="info-box-body">
                        <div class="info-box-text">
                            <div class="info-box-notice">
                                <strong>Perhatian:</strong><br>
                                Saat pengambilan alat wajib membawa <strong>KTP/SIM asli</strong> sebagai jaminan fisik.
                                Pengembalian maksimal pukul <strong>22.00 WIB</strong>.
                            </div>
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
        </div>

        <div class="checkout-sidebar">
            <div class="sidebar-title">
                Total Pembayaran
            </div>

            <div class="sidebar-row">
                <span>Total Alat / Hari</span>
                <strong>Rp <?= number_format($total_perhari, 0, ',', '.'); ?></strong>
            </div>

            <div class="sidebar-row">
                <span>Durasi Sewa</span>
                <strong><?= $total_hari; ?> Hari</strong>
            </div>

            <hr>

            <div class="sidebar-row">
                <strong>Total Tagihan</strong>
                <strong class="text-primary fs-18">
                    Rp <?= number_format($total_perhari * $total_hari, 0, ',', '.'); ?>
                </strong>
            </div>

            <?php
            $denda_per_hari = (int)($settings['denda_per_hari'] ?? 15000);
            ?>
            <div class="denda-highlight">
                <strong>Kebijakan Denda:</strong> Jika terlambat mengembalikan alat, denda
                <strong class="denda-rate-badge">Rp <?= number_format($denda_per_hari, 0, ',', '.'); ?>/hari</strong>
                akan dikenakan. Pengembalian maksimal pukul <strong>22.00 WIB</strong> pada tanggal jatuh tempo.
                Denda dibayar tunai saat pengembalian alat.
            </div>

            <div class="payment-method-wrap">
                <strong class="block fs-15">Pilih Metode Pembayaran</strong>
                <div class="payment-options">
                    <label class="payment-label selected" id="label-transfer" onclick="pilihMetode('transfer')">
                        <input type="radio" name="metode_bayar" value="transfer" checked onchange="pilihMetode('transfer')">
                        <i class="fas fa-qrcode qris-icon"></i>
                        <div>
                            <strong>QRIS</strong>
                            <span>Bayar baru ambil alat</span>
                        </div>
                    </label>
                    <label class="payment-label" id="label-tunai" onclick="pilihMetode('tunai')">
                        <input type="radio" name="metode_bayar" value="tunai" onchange="pilihMetode('tunai')">
                        <i class="fas fa-money-bill-wave tunai-icon"></i>
                        <div>
                            <strong>Tunai</strong>
                            <span>Bayar saat ambil alat</span>
                        </div>
                    </label>
                </div>
            </div>

            <div id="section-transfer" class="qris-box">
                <strong>Pindai QRIS Sewakuy</strong>
                <img src="../public/img/qris.jpeg" alt="QRIS" class="qris-image">
                <span class="form-help">
                    Transfer sesuai nominal tagihan di atas.
                </span>
            </div>

            <div id="section-tunai" class="qris-box tunai-box" style="display:none;">
                <strong class="text-success">Bayar di Tempat</strong>
                <p class="tunai-desc">
                    Lakukan pembayaran tunai saat mengambil alat di toko.
                    Status pesanan langsung <strong>Siap Diambil</strong>.
                </p>
            </div>

            <form action="../proses/transaksi.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tgl_mulai" value="<?= $tgl_mulai; ?>">
                <input type="hidden" name="tgl_selesai" value="<?= $tgl_selesai; ?>">
                <input type="hidden" name="total_hari" value="<?= $total_hari; ?>">
                <input type="hidden" name="total_biaya" value="<?= $total_perhari * $total_hari; ?>">
                <input type="hidden" name="serialized_items" value='<?= htmlspecialchars(json_encode($items_detail), ENT_QUOTES); ?>'>
                <input type="hidden" name="metode_bayar" id="hidden-metode" value="transfer">

                <div class="form-group" id="upload-section">
                    <label>Unggah Bukti Pembayaran</label>
                    <input type="file" name="bukti_bayar" class="form-control" id="bukti_bayar" required>
                </div>

                <button type="submit" name="proses_checkout" class="btn-checkout btn-full">
                    Selesaikan & Kirim Pesanan
                </button>
            </form>
        </div>

        <script>
        function pilihMetode(metode) {
            document.getElementById('hidden-metode').value = metode;
            document.getElementById('label-transfer').style.borderColor = metode === 'transfer' ? '#2563eb' : '#e2e8f0';
            document.getElementById('label-tunai').style.borderColor = metode === 'tunai' ? '#059669' : '#e2e8f0';
            document.getElementById('section-transfer').style.display = metode === 'transfer' ? 'block' : 'none';
            document.getElementById('section-tunai').style.display = metode === 'tunai' ? 'block' : 'none';
            document.getElementById('upload-section').style.display = metode === 'transfer' ? 'block' : 'none';

            var fileInput = document.getElementById('bukti_bayar');
            fileInput.required = (metode === 'transfer');
            if (metode === 'tunai') fileInput.value = '';
        }
        </script>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>

</body>
</html>
<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

require_once '../classes/Product.php';
$product = new Product();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Sewakuy</title>
    <link rel="stylesheet" href="../public/css/customer.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="cart-container">

    <div class="katalog-header-landing">
        <h2 class="page-title">Keranjang Sewa</h2>
        <p class="page-subtitle">
            Periksa kembali daftar alat multimedia dan jumlah unit sebelum melakukan pemesanan.
        </p>
    </div>

    <?php if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])): ?>
        <div class="empty-state">
            Keranjangmu masih kosong. Silakan ke <a href="katalog.php">Katalog</a> untuk memilih unit.
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama Alat</th>
                    <th>Harga / Hari</th>
                    <th>Jumlah</th>
                    <th>Subtotal / Hari</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_harga_perhari = 0;

                foreach ($_SESSION['keranjang'] as $produk_id => $qty):
                    $detail = $product->ambilDetail($produk_id);
                    if (!$detail) continue;
                    $subtotal = $detail['harga_perhari'] * $qty;
                    $total_harga_perhari += $subtotal;
                    $stok_tersedia = (int)$detail['stok'];
                ?>
                    <tr>
                        <td>
                            <?php if (!empty($detail['gambar'])): ?>
                                <img src="../public/produk/<?= htmlspecialchars($detail['gambar']); ?>" class="cart-img" alt="<?= htmlspecialchars($detail['nama_produk']); ?>">
                            <?php else: ?>
                                <img src="../public/produk/no-image.png" class="cart-img" alt="Tidak Ada Gambar">
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($detail['nama_produk']); ?></strong>
                            <br><small class="stok-muted">Stok: <?= $stok_tersedia; ?> unit</small>
                        </td>
                        <td>
                            Rp <?= number_format($detail['harga_perhari'], 0, ',', '.'); ?>
                        </td>
                        <td>
                            <div class="qty-control">
                                <a href="../proses/transaksi.php?aksi=ubah_qty&operasi=kurang&id=<?= $produk_id; ?>" class="qty-btn">−</a>
                                <span class="qty-number"><?= $qty; ?></span>
                                <?php if ($qty < $stok_tersedia): ?>
                                    <a href="../proses/transaksi.php?aksi=ubah_qty&operasi=tambah&id=<?= $produk_id; ?>" class="qty-btn">+</a>
                                <?php else: ?>
                                    <span class="qty-btn qty-disabled">+</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong>Rp <?= number_format($subtotal, 0, ',', '.'); ?></strong>
                        </td>
                        <td>
                            <a href="../proses/transaksi.php?aksi=hapus_item&id=<?= $produk_id; ?>" class="btn-delete" onclick="return confirm('Hapus barang?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div>
                <span class="summary-desc">Estimasi Biaya Dasar Seluruh Barang:</span>
                <div class="total-price">
                    Rp <?= number_format($total_harga_perhari, 0, ',', '.'); ?>
                    <span class="total-unit-label">/hari</span>
                </div>
            </div>
        </div>

        <br>

        <div class="cart-summary">
            <form action="checkout.php" method="POST">
                <div>
                    <label for="tgl_mulai">Tanggal Pengambilan Alat</label><br>
                    <input type="date" id="tgl_mulai" name="tgl_mulai" min="<?= date('Y-m-d'); ?>" required>
                </div>

                <br>

                <div>
                    <label for="total_hari">Lama Sewa (Hari)</label><br>
                    <input type="number" id="total_hari" name="total_hari" min="1" value="1" required>
                </div>

                <br>

                <div class="summary-desc">
                    Pengembalian alat maksimal pukul <strong>22.00 WIB</strong> pada tanggal selesai sewa.
                </div>

                <br>

                <button type="submit" class="btn-checkout">
                    Lanjut ke Pembayaran
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../templates/footer.php'; ?>

</body>
</html>
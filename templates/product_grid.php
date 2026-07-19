<div class="grid-produk">
    <?php if (mysqli_num_rows($semua_produk) == 0): ?>
        <div class="empty-katalog">
            Maaf, produk di kategori ini sedang kosong.
        </div>
    <?php else: ?>
        <?php while ($row = mysqli_fetch_assoc($semua_produk)): ?>
            <div class="card-produk">
                <div class="foto-wrapper">
                    <?php if (!empty($row['gambar'])): ?>
                        <img src="../public/produk/<?= htmlspecialchars($row['gambar'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($row['nama_produk']); ?>">
                    <?php else: ?>
                        <img src="../public/produk/no-image.png" alt="Tidak Ada Gambar">
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <h4><?= htmlspecialchars($row['nama_produk']); ?></h4>

                    <div class="card-meta-row">
                        <span class="badge-merk">
                            <?= htmlspecialchars($row['nama_kategori']); ?>
                        </span>
                        <small class="stok-info">
                            Tersedia: <?= $row['stok']; ?> unit
                        </small>
                    </div>

                    <p class="prod-desc">
                        <?= htmlspecialchars($row['deskripsi']); ?>
                    </p>

                    <div class="card-action-area">
                        <div class="price-info">
                            <span class="label-harga">Harga Sewa:</span>
                            <div>
                                <span class="amount">Rp <?= number_format($row['harga_perhari'], 0, ',', '.'); ?></span>
                                <span class="unit">/hari</span>
                            </div>
                        </div>

                        <?php if ($row['stok'] > 0): ?>
                            <form action="../proses/transaksi.php" method="POST">
                                <input type="hidden" name="produk_id" value="<?= htmlspecialchars($row['id'], ENT_QUOTES); ?>">
                                <button type="submit" name="tambah_keranjang" class="btn-sewa">
                                    Tambah Keranjang
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn-sewa" disabled>Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
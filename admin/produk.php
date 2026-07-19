<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/Product.php';
$product = new Product();
$semua_produk = $product->tampilSemua();
$daftar_kategori = $product->tampilKategori();

// ----- Cek mode edit -----
$data_edit = null;
if (isset($_GET['aksi']) && $_GET['aksi'] == 'view_edit' && isset($_GET['id'])) {
    $data_edit = $product->ambilDetail((int)$_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Sewakuy Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/sidebar_admin.php'; ?>

<div class="admin-header-flex">
    <div>
        <h2 class="admin-page-title">Kelola Produk Sewakuy</h2>
        <p class="admin-page-subtitle">Manajemen stok unit, kategori, serta penyesuaian harga sewa alat (Kamera, Lensa, Aksesoris).</p>
    </div>
    <button onclick="toggleForm()" class="btn-admin-acc btn-tambah-produk">
        Tambah Produk
    </button>
</div>

<?php if ($data_edit): ?>
    <div class="checkout-box">
        <h3 class="sidebar-title">Edit Produk</h3>
        <form action="../proses/produk.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $data_edit['id']; ?>">

            <div class="form-group">
                <label>Nama Alat:</label>
                <input type="text" class="form-control" name="nama_produk" value="<?= $data_edit['nama_produk']; ?>" required>
            </div>

            <div class="form-group">
                <label>Kategori:</label>
                <select class="form-control" name="kategori_id" required>
                    <?php while($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                        <option value="<?= $kat['id']; ?>" <?= ($kat['id'] == $data_edit['kategori_id']) ? 'selected' : ''; ?>>
                            <?= $kat['nama_kategori']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga Sewa per Hari (Rp):</label>
                <input type="number" class="form-control" name="harga" value="<?= $data_edit['harga_perhari']; ?>" required>
            </div>

            <div class="form-group">
                <label>Stok Tersedia:</label>
                <input type="number" class="form-control" name="stok" value="<?= $data_edit['stok']; ?>" required>
            </div>

            <div class="form-group">
                <label>Deskripsi:</label>
                <textarea class="form-control" name="deskripsi" rows="4" required><?= $data_edit['deskripsi']; ?></textarea>
            </div>

            <div class="form-group">
                <label>Ganti Foto (Biarkan kosong jika tidak diganti):</label>
                <input type="file" class="form-control" name="gambar">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-admin-acc" name="edit_produk">Simpan Perubahan</button>
                <a href="produk.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div id="formTambah" class="checkout-box" style="display:none;">
    <h3 class="sidebar-title">Tambah Produk Baru</h3>
    <form action="../proses/produk.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Alat:</label>
            <input type="text" class="form-control" name="nama_produk" required>
        </div>

        <div class="form-group">
            <label>Kategori:</label>
            <select class="form-control" name="kategori_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php mysqli_data_seek($daftar_kategori, 0); ?>
                <?php while($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                    <option value="<?= $kat['id']; ?>"><?= $kat['nama_kategori']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Harga Sewa per Hari (Rp):</label>
            <input type="number" class="form-control" name="harga" required>
        </div>

        <div class="form-group">
            <label>Stok Awal:</label>
            <input type="number" class="form-control" name="stok" required>
        </div>

        <div class="form-group">
            <label>Deskripsi:</label>
            <textarea class="form-control" name="deskripsi" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label>Foto Alat:</label>
            <input type="file" class="form-control" name="gambar" required>
        </div>

        <button type="submit" class="btn-admin-acc" name="tambah_produk">Tambah Alat</button>
        <button type="button" onclick="toggleForm()" class="btn-cancel">Batal</button>
    </form>
</div>

<script>
function toggleForm() {
    var el = document.getElementById('formTambah');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<div class="section-margin">
    <h3 class="sidebar-title">Daftar Inventaris Alat</h3>

    <?php if (mysqli_num_rows($semua_produk) == 0): ?>
        <div class="admin-empty">
            Belum ada alat yang ditambahkan ke sistem.
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="col-foto">Foto</th>
                    <th>Nama Alat</th>
                    <th>Kategori</th>
                    <th>Harga/Hari</th>
                    <th>Stok</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($semua_produk)): ?>
                    <tr>
                        <td>
                            <img src="../public/produk/<?= $row['gambar']; ?>" width="70" class="produk-foto" alt="foto">
                        </td>
                        <td><strong><?= htmlspecialchars($row['nama_produk']); ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                        <td>Rp <?= number_format($row['harga_perhari'], 0, ',', '.'); ?></td>
                        <td><?= $row['stok']; ?> unit</td>
                        <td>
                            <a href="produk.php?aksi=view_edit&id=<?= $row['id']; ?>" class="badge-action mr-5">Ubah</a>
                            <a href="../proses/produk.php?aksi=hapus&id=<?= $row['id']; ?>" class="badge-action btn-hapus" onclick="return confirm('Yakin ingin menghapus alat ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</div> 

</body>
</html>
<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/Setting.php';
$modelPengaturan = new Setting();
$settings = $modelPengaturan->ambilSemua();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Toko - Sewakuy Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css">
</head>
<body>
<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/sidebar_admin.php'; ?>

<div class="admin-page-header">
    <h2 class="admin-page-title">Pengaturan Toko</h2>
    <p class="admin-page-subtitle">Kelola informasi toko yang akan ditampilkan kepada pelanggan.</p>
</div>

<div class="checkout-box max-w-700">
    <form action="../proses/pengaturan.php" method="POST">
        <div class="form-group">
            <label>Nama Toko</label>
            <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($settings['nama_toko'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Alamat Toko (Lengkap)</label>
            <textarea name="alamat_toko" class="form-control" rows="4" required><?= htmlspecialchars($settings['alamat_toko'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Nomor HP / WhatsApp Toko</label>
            <input type="text" name="no_hp_toko" class="form-control" value="<?= htmlspecialchars($settings['no_hp_toko'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Jam Operasional</label>
            <input type="text" name="jam_operasional" class="form-control" value="<?= htmlspecialchars($settings['jam_operasional'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Denda Keterlambatan (Rp/hari)</label>
            <input type="number" name="denda_per_hari" class="form-control" value="<?= htmlspecialchars($settings['denda_per_hari'] ?? '15000') ?>" required min="0">
            <span class="form-help form-help-text">Nominal denda per hari jika penyewa telat mengembalikan alat.</span>
        </div>
        <div class="form-group">
            <label>Tautan Google Maps</label>
            <input type="text" name="link_maps" class="form-control" value="<?= htmlspecialchars($settings['link_maps'] ?? '') ?>" placeholder="https://maps.app.goo.gl/...">
            <span class="form-help form-help-text">Salin tautan Google Maps, lalu tempel di sini.</span>
        </div>
        <button type="submit" name="simpan_pengaturan" class="btn-admin-acc btn-simpan">Simpan Pengaturan</button>
    </form>
</div>

</div>
</body>
</html>

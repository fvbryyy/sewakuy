<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

require_once '../classes/User.php';
$userObj = new User();
$profil = $userObj->ambilProfil($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Sewakuy</title>
    <link rel="stylesheet" href="../public/css/customer.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="cart-container">

    <h2 class="page-title">Profil Saya</h2>
    <p class="page-subtitle">
        Kelola dan perbarui data diri akunmu untuk mempermudah proses verifikasi sewa.
    </p>

    <form action="../proses/profil.php" method="POST" class="form-profil">
        <input type="hidden" name="id" value="<?= $profil['id']; ?>">

        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($profil['nama']); ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email (Nama Pengguna)</label>
            <input type="email" id="email" value="<?= htmlspecialchars($profil['email']); ?>" class="form-control disabled-input" disabled>
            <small class="form-help">Email tidak dapat diubah demi keamanan akun.</small>
        </div>

        <div class="form-group">
            <label for="no_hp">No. WhatsApp / HP</label>
            <input type="text" id="no_hp" name="no_hp" value="<?= htmlspecialchars($profil['no_hp']); ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="alamat">Alamat Lengkap</label>
            <textarea id="alamat" name="alamat" rows="4" class="form-control text-area" required><?= htmlspecialchars($profil['alamat']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="password_baru">Kata Sandi Baru (Opsional)</label>
            <input type="password" id="password_baru" name="password_baru" placeholder="Kosongkan jika tidak ingin mengganti kata sandi" class="form-control">
        </div>

        <div class="form-actions">
            <button type="submit" name="update_profil" class="btn-checkout">Simpan Perubahan</button>
            <a href="index.php" class="btn-cancel">Batal</a>
        </div>
    </form>

</div>

<?php require_once '../templates/footer.php'; ?>

</body>
</html>
<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/User.php';
$user = new User();
$daftar_customer = $user->tampilSemuaPelanggan();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengguna - Sewakuy Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/sidebar_admin.php'; ?>

<div class="admin-page-header">
    <h2 class="admin-page-title">Data Pelanggan</h2>
    <p class="admin-page-subtitle">Berikut adalah daftar data diri pelanggan resmi aplikasi Sewakuy yang berstatus aktif.</p>
</div>

<?php if (mysqli_num_rows($daftar_customer) == 0): ?>
    <div class="admin-empty">
        Belum ada pelanggan yang mendaftar akun.
    </div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID Pelanggan</th>
                <th>Nama Lengkap</th>
                <th>Email Aktif</th>
                <th>No. HP (WhatsApp)</th>
                <th>Alamat Rumah Lengkap</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($daftar_customer)): ?>
                <tr>
                    <td>#USR-<?= $row['id']; ?></td>
                    <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td>
                        <a href="https://wa.me/<?= $row['no_hp']; ?>" target="_blank" class="whatsapp-link">
                            <?= $row['no_hp']; ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

</div> 

</body>
</html>
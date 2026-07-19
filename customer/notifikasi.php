<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

require_once '../classes/Notification.php';
$notif = new Notification();
$user_id = $_SESSION['user_id'];

if (isset($_GET['aksi']) && $_GET['aksi'] == 'baca_semua') {
    $notif->tandaiSemuaDibaca($user_id);
    header("Location: notifikasi.php");
    exit();
}

if (isset($_GET['baca'])) {
    $notif->tandaiDibaca((int)$_GET['baca'], $user_id);
    if (isset($_GET['go'])) {
        $tujuan = $_GET['go'];
        if (strpos($tujuan, '://') === false && substr($tujuan, 0, 1) !== '/') {
            header("Location: " . $tujuan);
            exit();
        }
    }
    header("Location: notifikasi.php");
    exit();
}

$daftar_notif = $notif->ambil($user_id, 50);
$belum_dibaca = $notif->hitungBelumDibaca($user_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Sewakuy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../public/css/customer.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="container">
    <div class="notif-header">
        <div>
            <h2 class="page-title">Notifikasi</h2>
            <p class="page-subtitle">Pantau status pesanan dan pengingat pengembalian alat.</p>
        </div>
        <?php if ($belum_dibaca > 0): ?>
            <a href="notifikasi.php?aksi=baca_semua" class="notif-mark-all">Tandai semua dibaca</a>
        <?php endif; ?>
    </div>

    <?php if (mysqli_num_rows($daftar_notif) == 0): ?>
        <div class="notif-empty">
            <i class="fas fa-bell-slash"></i>
            <span>Belum ada notifikasi</span>
        </div>
    <?php else: ?>
        <div class="notif-list">
            <?php while ($n = mysqli_fetch_assoc($daftar_notif)): ?>
                <?php
                $href = $n['link'] ? "notifikasi.php?baca={$n['id']}&go=" . urlencode($n['link']) : "notifikasi.php?baca={$n['id']}";
                ?>
                <a href="<?= $href; ?>" class="notif-item <?= $n['is_read'] ? '' : 'unread'; ?>">
                    <span class="notif-dot" data-t="<?= $n['tipe']; ?>"></span>
                    <div class="notif-body">
                        <strong class="notif-judul"><?= htmlspecialchars($n['judul']); ?></strong>
                        <p class="notif-pesan"><?= htmlspecialchars($n['pesan']); ?></p>
                        <small class="notif-waktu"><?= date('d M Y H:i', strtotime($n['created_at'])); ?></small>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../templates/footer.php'; ?>

</body>
</html>

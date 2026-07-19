<?php
session_start();
require_once '../config/AuthCheck.php';
cekAdmin();

require_once '../classes/Transaction.php';
require_once '../classes/Notification.php';
require_once '../helpers/status_badge.php';
$tx = new Transaction();
$notif = new Notification();

// ----- Auto-check notifikasi -----
$admin_id = $_SESSION['user_id'];
$tx->cekNotifikasiPesananBaru($admin_id, $notif);
$tx->cekNotifikasiAmbilHariIni($admin_id, $notif);
$tx->cekNotifikasiKembaliHariIni($admin_id, $notif);
$tx->cekNotifikasiTerlambatAdmin($admin_id, $notif);

$status_filter = $_GET['status'] ?? '';
if ($status_filter !== '') {
    $semua_transaksi = $tx->tampilSemuaAdminPerStatus($status_filter);
} else {
    $semua_transaksi = $tx->tampilSemuaAdmin();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Sewakuy Admin</title>
    <link rel="stylesheet" href="../public/css/admin.css">
</head>
<body>
<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/sidebar_admin.php'; ?>

<div class="admin-page-header">
    <h2 class="admin-page-title">Manajemen Transaksi Penyewaan Alat</h2>
    <p class="admin-page-subtitle">Berikut adalah daftar pesanan masuk dari pelanggan. Periksa bukti transfer dengan teliti sebelum melakukan persetujuan.</p>
</div>

<div class="filter-btn-wrap">
    <a href="transaksi.php" class="filter-btn <?= $status_filter === '' ? 'active' : '' ?>">Semua</a>
    <a href="transaksi.php?status=menunggu_acc" class="filter-btn <?= $status_filter === 'menunggu_acc' ? 'active' : '' ?>">Menunggu Persetujuan</a>
    <a href="transaksi.php?status=siap_diambil" class="filter-btn <?= $status_filter === 'siap_diambil' ? 'active' : '' ?>">Siap Diambil</a>
    <a href="transaksi.php?status=disewa" class="filter-btn <?= $status_filter === 'disewa' ? 'active' : '' ?>">Disewa</a>
    <a href="transaksi.php?status=selesai" class="filter-btn <?= $status_filter === 'selesai' ? 'active' : '' ?>">Selesai</a>
    <a href="transaksi.php?status=ditolak" class="filter-btn filter-btn-ditolak <?= $status_filter === 'ditolak' ? 'active' : '' ?>">Ditolak</a>
</div>

<?php if (mysqli_num_rows($semua_transaksi) == 0): ?>
    <div class="admin-empty">
        <?php if ($status_filter !== ''): ?>
            Tidak ada transaksi dengan status tersebut.
        <?php else: ?>
            Belum ada transaksi sewa masuk ke sistem.
        <?php endif; ?>
    </div>
<?php else: ?>
    <table class="admin-table">
            <thead>
                <tr>
                    <th>Nota</th>
                    <th>Pelanggan</th>
                    <th>Durasi</th>
                    <th>Total</th>
                    <th>Denda</th>
                    <th>Metode</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($semua_transaksi)): ?>
                <tr>
                    <td>#SK-<?= $row['id']; ?></td>
                    <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                    <td>
                        <?= date('d/m/Y', strtotime($row['tgl_mulai'])); ?> s/d <?= date('d/m/Y', strtotime($row['tgl_selesai'])); ?><br>
                        <p class="durasi-info">(<?= $row['total_hari']; ?> Hari)</p>
                    </td>
                    <td><strong>Rp <?= number_format($row['total_biaya'], 0, ',', '.'); ?></strong></td>
                    <td>
                        <?php if (($row['total_denda'] ?? 0) > 0): ?>
                            <span class="denda-badge">Rp <?= number_format($row['total_denda'], 0, ',', '.'); ?></span>
                            <small class="denda-info">Telat <?= $row['hari_telat']; ?> hari</small>
                        <?php elseif ($row['status'] == 'disewa' && $row['tgl_selesai'] < date('Y-m-d')): ?>
                            <?php
                            $hari_telat = floor((strtotime('today') - strtotime($row['tgl_selesai'])) / 86400);
                            $denda_preview = $hari_telat * ($row['denda_per_hari'] ?? 15000);
                            ?>
                            <span class="denda-badge denda-preview">Rp <?= number_format($denda_preview, 0, ',', '.'); ?></span>
                            <small class="denda-info denda-info-warning">Telat <?= $hari_telat; ?> hari (belum dikembalikan)</small>
                        <?php else: ?>
                            <span class="text-muted fs-13">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($row['metode_bayar'] ?? 'transfer') == 'tunai'): ?>
                            <span class="badge-status status-success">Tunai</span>
                        <?php else: ?>
                            <span class="badge-status status-active">QRIS</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['bukti_bayar']): ?>
                            <a href="../public/uploads/<?= $row['bukti_bayar']; ?>" target="_blank" class="badge-action bukti-link">
                                Lihat Bukti
                            </a>
                        <?php else: ?>
                            <span class="text-muted fs-13">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= badgeStatus($row['status']); ?>
                    </td>
                    <td>
                        <?php
                        // ----- Bersihkan nomor HP untuk wa.me -----
                        $wa_number = $row['no_hp'] ?? '';
                        $wa_number = preg_replace('/[^0-9]/', '', $wa_number);
                        if (substr($wa_number, 0, 1) == '0') {
                            $wa_number = '62' . substr($wa_number, 1);
                        } elseif (substr($wa_number, 0, 2) == '62') {
                            $wa_number = $wa_number;
                        } elseif (substr($wa_number, 0, 1) == '6') {
                            $wa_number = $wa_number;
                        }
                        ?>
                        <?php if($row['status'] == 'menunggu_acc'): ?>
                            <div class="aksi-wrap">
                                <a href="../proses/admin_transaksi.php?aksi=acc&id=<?= $row['id']; ?>" class="btn-admin-acc" onclick="return confirm('Apakah Anda yakin bukti transfer sudah valid dan ingin menyetujui transaksi ini?')">
                                    Setujui
                                </a>
                                <button type="button" class="btn-admin-deny btn-padding-sm" onclick="bukaTolak(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama'], ENT_QUOTES); ?>', '<?= $row['id']; ?>')">
                                    Tolak
                                </button>
                                <?php if ($wa_number): ?>
                                    <a href="https://wa.me/<?= $wa_number; ?>" target="_blank" class="whatsapp-link">WhatsApp</a>
                                <?php endif; ?>
                            </div>
                        <?php elseif($row['status'] == 'siap_diambil'): ?>
                            <div class="aksi-wrap">
                                <a href="../proses/admin_transaksi.php?aksi=serahkan&id=<?= $row['id']; ?>" class="btn-admin-acc btn-serahkan" onclick="return confirm('Pastikan pelanggan sudah membawa KTP/SIM asli! Klik OK jika alat sudah diserahkan.')">
                                    Serahkan Alat
                                </a>
                                <?php if ($wa_number): ?>
                                    <?php
                                    if (strtolower($row['metode_bayar']) == 'tunai') {
                                        $status_bayar = "*BAYAR DI TOKO (TUNAI)*";
                                        $catatan_bayar = "Mohon siapkan uang pas saat melakukan pengambilan alat di toko.";
                                    } else {
                                        $status_bayar = "*DISETUJUI / LUNAS (QRIS)*";
                                        $catatan_bayar = "Pembayaran telah kami verifikasi.";
                                    }

                                    $wa_pesan = urlencode(
                                        "*SEWAKUY - PEMBERITAHUAN*\n" .
                                        "-----------------------------------------\n\n" .
                                        "Halo *" . $row['nama'] . "*,\n" .
                                        "Pesanan Anda untuk nota *#SK-" . $row['id'] . "* sekarang berstatus: *SIAP DIAMBIL*.\n\n" .
                                        "*DETAIL NOTA:*\n" .
                                        "• Status Pembayaran : " . $status_bayar . "\n" .
                                        "• Keterangan : " . $catatan_bayar . "\n\n" .
                                        "*NB:*\n" .
                                        "1. Wajib menunjukkan *nota asli* kepada kru toko.\n" .
                                        "2. Membawa *KTP/SIM asli* (sesuai nama penyewa).\n\n" .
                                        "-----------------------------------------\n" .
                                        "Sampai jumpa di toko! Jika ada pertanyaan, silakan balas pesan ini."
                                    );
                                    ?>
                                    <a href="https://wa.me/<?= $wa_number; ?>?text=<?= $wa_pesan; ?>" target="_blank" class="whatsapp-link">WhatsApp</a>
                                <?php endif; ?>
                            </div>
                        <?php elseif($row['status'] == 'disewa'): ?>
                            <a href="../proses/admin_transaksi.php?aksi=selesai&id=<?= $row['id']; ?>" class="btn-admin-deny btn-padding-sm" onclick="return confirm('Konfirmasi pengembalian alat? Sistem otomatis hitung denda jika melewati tanggal jatuh tempo.')">
                                Kembalikan Alat
                            </a>
                        <?php elseif($row['status'] == 'ditolak'): ?>
                            <?php if (!empty($row['catatan_tolak'])): ?>
                                <span class="aksi-info aksi-info-danger" title="<?= htmlspecialchars($row['catatan_tolak']); ?>">
                                    <i class="fas fa-info-circle"></i> <?= htmlspecialchars(mb_strimwidth($row['catatan_tolak'], 0, 30, '...')); ?>
                                </span>
                            <?php else: ?>
                                <span class="aksi-info">Ditolak tanpa catatan</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="aksi-info">Tidak Perlu Tindakan</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Modal Tolak Pesanan -->
<div id="modalTolak" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3 class="modal-title">Tolak Pesanan</h3>
        <p class="modal-subtitle">Tolak pesanan <strong id="modalNamaPelanggan"></strong> (Nota #SK-<span id="modalNotaId"></span>)?</p>
        <form id="formTolak" method="POST" action="../proses/admin_transaksi.php">
            <input type="hidden" name="aksi" value="tolak">
            <input type="hidden" name="id" id="modalTransaksiId">
            <div class="form-group">
                <label>Catatan Penolakan (opsional)</label>
                <textarea name="catatan_tolak" class="form-control" rows="3" placeholder="Contoh: Stok tidak tersedia, bukti bayar tidak valid..."></textarea>
            </div>
            <div class="aksi-wrap">
                <button type="submit" class="btn-admin-deny" onclick="return confirm('Yakin ingin menolak pesanan ini?')">Ya, Tolak</button>
                <button type="button" class="btn-cancel" onclick="tutupTolak()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaTolak(id, nama, notaId) {
    document.getElementById('modalTransaksiId').value = id;
    document.getElementById('modalNamaPelanggan').textContent = nama;
    document.getElementById('modalNotaId').textContent = notaId;
    document.getElementById('modalTolak').style.display = 'flex';
}
function tutupTolak() {
    document.getElementById('modalTolak').style.display = 'none';
}
document.getElementById('modalTolak').addEventListener('click', function(e) {
    if (e.target === this) tutupTolak();
});
</script>

</div> 

</body>
</html>

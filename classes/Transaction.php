<?php
require_once __DIR__ . '/Model.php';

class Transaction extends Model {

    // ----- Buat transaksi baru -----
    public function buatTransaksi($user_id, $tgl_mulai, $tgl_selesai, $total_hari, $total_biaya, $bukti_bayar, $items, $metode_bayar = 'transfer') {
        $status = ($metode_bayar == 'tunai') ? 'siap_diambil' : 'menunggu_acc';
        $query_tx = "INSERT INTO transaksi (user_id, tgl_mulai, tgl_selesai, total_hari, total_biaya, metode_bayar, bukti_bayar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_tx = mysqli_prepare($this->db, $query_tx);
        mysqli_stmt_bind_param($stmt_tx, "issidsss", $user_id, $tgl_mulai, $tgl_selesai, $total_hari, $total_biaya, $metode_bayar, $bukti_bayar, $status);

        if (mysqli_stmt_execute($stmt_tx)) {
            $id_transaksi = mysqli_insert_id($this->db);

            foreach ($items as $produk_id => $detail) {
                $qty = $detail['qty'];
                $harga_satuan = $detail['harga_perhari'];

                $query_detail = "INSERT INTO detail_transaksi (transaksi_id, produk_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)";
                $stmt_detail = mysqli_prepare($this->db, $query_detail);
                mysqli_stmt_bind_param($stmt_detail, "iiid", $id_transaksi, $produk_id, $qty, $harga_satuan);
                mysqli_stmt_execute($stmt_detail);
            }
            return $id_transaksi;
        }
        return false;
    }

    // ----- Riwayat transaksi pelanggan -----
    public function tampilRiwayatPelanggan($user_id) {
        $query = "SELECT * FROM transaksi WHERE user_id = ? ORDER BY id DESC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Ambil semua ID admin -----
    public function ambilIdAdmin() {
        $query = "SELECT id FROM users WHERE role = 'admin'";
        $result = mysqli_query($this->db, $query);
        $daftar_id = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $daftar_id[] = $row['id'];
        }
        return $daftar_id;
    }

    // ----- Ambil data transaksi untuk nota -----
    public function ambilNotaInduk($id_transaksi) {
        $query = "SELECT transaksi.*, users.nama, users.no_hp, users.alamat
                  FROM transaksi
                  JOIN users ON transaksi.user_id = users.id
                  WHERE transaksi.id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // ----- Ambil detail barang dalam nota -----
    public function ambilDetailBarangNota($id_transaksi) {
        $query = "SELECT detail_transaksi.*, produk.nama_produk
                  FROM detail_transaksi
                  JOIN produk ON detail_transaksi.produk_id = produk.id
                  WHERE detail_transaksi.transaksi_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Tampilkan semua transaksi (admin) -----
    public function tampilSemuaAdmin() {
        $query = "SELECT transaksi.*, users.nama, users.no_hp
                  FROM transaksi
                  JOIN users ON transaksi.user_id = users.id
                  ORDER BY transaksi.id DESC";
        return mysqli_query($this->db, $query);
    }

    // ----- Tampilkan semua transaksi admin berdasarkan status -----
    public function tampilSemuaAdminPerStatus($status) {
        $query = "SELECT transaksi.*, users.nama, users.no_hp
                  FROM transaksi
                  JOIN users ON transaksi.user_id = users.id
                  WHERE transaksi.status = ?
                  ORDER BY transaksi.id DESC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $status);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Ubah status transaksi -----
    public function ubahStatus($id_transaksi, $status) {
        $query = "UPDATE transaksi SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $id_transaksi);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Tolak transaksi -----
    public function tolakTransaksi($id_transaksi, $catatan = '') {
        $query = "UPDATE transaksi SET status = 'ditolak', catatan_tolak = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "si", $catatan, $id_transaksi);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Kelola stok alat (kurangi / kembalikan) -----
    public function kelolaStokAlat($id_transaksi, $metode) {
        if (!in_array($metode, ['kurangi', 'kembalikan'])) return;

        $query_detail = "SELECT produk_id, jumlah FROM detail_transaksi WHERE transaksi_id = ?";
        $stmt_detail = mysqli_prepare($this->db, $query_detail);
        mysqli_stmt_bind_param($stmt_detail, "i", $id_transaksi);
        mysqli_stmt_execute($stmt_detail);
        $result = mysqli_stmt_get_result($stmt_detail);

        while ($item = mysqli_fetch_assoc($result)) {
            $produk_id = $item['produk_id'];
            $jumlah = $item['jumlah'];

            if ($metode == 'kurangi') {
                $query_stok = "UPDATE produk SET stok = stok - ? WHERE id = ?";
            } else {
                $query_stok = "UPDATE produk SET stok = stok + ? WHERE id = ?";
            }

            $stmt_stok = mysqli_prepare($this->db, $query_stok);
            mysqli_stmt_bind_param($stmt_stok, "ii", $jumlah, $produk_id);
            mysqli_stmt_execute($stmt_stok);
        }
    }

    // ----- Proses pengembalian alat (hitung denda otomatis) -----
    public function prosesKembali($id_transaksi) {
        $query = "SELECT tgl_selesai FROM transaksi WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        if (!$row) return false;

        $tgl_kembali = date('Y-m-d');
        $tgl_selesai = $row['tgl_selesai'];

        $hari_telat = 0;
        $total_denda = 0;

        require_once __DIR__ . '/Setting.php';
        $modelPengaturan = new Setting();
        $denda_per_hari = (int)($modelPengaturan->ambil('denda_per_hari') ?? 15000);

        if ($tgl_kembali > $tgl_selesai) {
            $tanggal1 = new DateTime($tgl_selesai);
            $tanggal2 = new DateTime($tgl_kembali);
            $hari_telat = $tanggal1->diff($tanggal2)->days;
            $total_denda = $hari_telat * $denda_per_hari;
        }

        $query = "UPDATE transaksi SET status = 'selesai', tgl_kembali = ?, hari_telat = ?, total_denda = ?, denda_per_hari = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "siiii", $tgl_kembali, $hari_telat, $total_denda, $denda_per_hari, $id_transaksi);
        return mysqli_stmt_execute($stmt);
    }

    // ----- Tampilkan transaksi terbaru untuk dashboard -----
    public function tampilTerbaru($batas = 5) {
        $query = "SELECT transaksi.*, users.nama
                  FROM transaksi
                  JOIN users ON transaksi.user_id = users.id
                  ORDER BY transaksi.id DESC
                  LIMIT ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $batas);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Hitung total pendapatan -----
    public function hitungPendapatan() {
        $query = "SELECT COALESCE(SUM(total_biaya + total_denda), 0) as total FROM transaksi WHERE status = 'selesai'";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }

    // ----- Hitung pendapatan bulan ini -----
    public function hitungPendapatanBulanIni() {
        $query = "SELECT COALESCE(SUM(total_biaya + total_denda), 0) as total FROM transaksi WHERE status = 'selesai' AND MONTH(tgl_kembali) = MONTH(CURDATE()) AND YEAR(tgl_kembali) = YEAR(CURDATE())";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }

    // ----- Hitung transaksi berdasarkan status -----
    public function hitungTransaksiPerStatus($status) {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "s", $status);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }

    // ----- Laporan: transaksi dalam rentang tanggal -----
    public function laporanTransaksi($tgl_awal, $tgl_akhir) {
        $query = "SELECT transaksi.*, users.nama
                  FROM transaksi
                  JOIN users ON transaksi.user_id = users.id
                  WHERE transaksi.tgl_mulai BETWEEN ? AND ?
                  ORDER BY transaksi.tgl_mulai DESC";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "ss", $tgl_awal, $tgl_akhir);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    // ----- Laporan: ringkasan pendapatan dalam rentang tanggal -----
    public function laporanRingkasan($tgl_awal, $tgl_akhir) {
        $query = "SELECT
                    COUNT(id) as total_transaksi,
                    COALESCE(SUM(total_biaya), 0) as total_sewa,
                    COALESCE(SUM(total_denda), 0) as total_denda,
                    COALESCE(SUM(total_biaya + total_denda), 0) as total_pendapatan
                  FROM transaksi
                  WHERE tgl_mulai BETWEEN ? AND ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "ss", $tgl_awal, $tgl_akhir);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // ----- Transaksi yang harus diambil hari ini -----
    public function ambilHariIni() {
        $query = "SELECT t.*, u.nama, u.no_hp FROM transaksi t JOIN users u ON t.user_id = u.id
                  WHERE t.status = 'siap_diambil' AND t.tgl_mulai = CURDATE() ORDER BY t.id DESC";
        return mysqli_query($this->db, $query);
    }

    // ----- Transaksi yang harus dikembalikan hari ini -----
    public function kembaliHariIni() {
        $query = "SELECT t.*, u.nama, u.no_hp FROM transaksi t JOIN users u ON t.user_id = u.id
                  WHERE t.status = 'disewa' AND t.tgl_selesai = CURDATE() ORDER BY t.id DESC";
        return mysqli_query($this->db, $query);
    }

    // ----- Transaksi yang sudah terlambat -----
    public function terlambat() {
        $query = "SELECT t.*, u.nama, u.no_hp FROM transaksi t JOIN users u ON t.user_id = u.id
                  WHERE t.status = 'disewa' AND t.tgl_selesai < CURDATE() ORDER BY t.tgl_selesai ASC";
        return mysqli_query($this->db, $query);
    }

    // ----- Hitung transaksi untuk ringkasan admin -----
    public function hitungAmbilHariIni() {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = 'siap_diambil' AND tgl_mulai = CURDATE()";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    public function hitungKembaliHariIni() {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = 'disewa' AND tgl_selesai = CURDATE()";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    public function hitungTerlambat() {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = 'disewa' AND tgl_selesai < CURDATE()";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }

    // ----- Cek notifikasi H-1 pengembalian untuk pelanggan -----
    public function cekNotifikasiH1($user_id, $modelNotif) {
        $query = "SELECT * FROM transaksi WHERE user_id = ? AND status = 'disewa' AND tgl_selesai = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $judul = 'Pengembalian Besok';
            if (!$modelNotif->sudahAdaHariIni($user_id, $judul, "../customer/invoice.php?id={$row['id']}")) {
                $modelNotif->kirim($user_id, $judul,
                    "Pesanan #SK-{$row['id']} harus dikembalikan besok ({$row['tgl_selesai']}). Siapkan alat untuk pengembalian.",
                    'warning', "../customer/invoice.php?id={$row['id']}");
            }
        }
    }

    // ----- Cek notifikasi terlambat untuk pelanggan -----
    public function cekNotifikasiTerlambat($user_id, $modelNotif) {
        $query = "SELECT * FROM transaksi WHERE user_id = ? AND status = 'disewa' AND tgl_selesai < CURDATE()";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $tanggal1 = new DateTime($row['tgl_selesai']);
            $tanggal2 = new DateTime(date('Y-m-d'));
            $hari_telat = $tanggal1->diff($tanggal2)->days;

            $judul = 'Pengembalian Terlambat';
            if (!$modelNotif->sudahAdaHariIni($user_id, $judul, "../customer/invoice.php?id={$row['id']}")) {
                $modelNotif->kirim($user_id, $judul,
                    "Pesanan #SK-{$row['id']} sudah terlambat {$hari_telat} hari! Segera kembalikan alat untuk menghindari denda tambahan.",
                    'danger', "../customer/invoice.php?id={$row['id']}");
            }
        }
    }

    // ----- Cek notifikasi admin: pesanan baru hari ini -----
    public function cekNotifikasiPesananBaru($admin_id, $modelNotif) {
        $query = "SELECT * FROM transaksi WHERE DATE(tgl_transaksi) = CURDATE() AND status IN ('menunggu_acc', 'siap_diambil')";
        $result = mysqli_query($this->db, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $judul = 'Pesanan Baru';
            $tautan = "../admin/transaksi.php?status=menunggu_acc";
            if (!$modelNotif->sudahAdaHariIni($admin_id, $judul, $tautan)) {
                $modelNotif->kirim($admin_id, $judul,
                    "Ada pesanan baru #SK-{$row['id']} yang perlu ditinjau.",
                    'info', $tautan);
            }
        }
    }

    // ----- Cek notifikasi admin: ambil hari ini -----
    public function cekNotifikasiAmbilHariIni($admin_id, $modelNotif) {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = 'siap_diambil' AND tgl_mulai = CURDATE()";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        $total = (int)($row['total'] ?? 0);

        if ($total > 0) {
            $judul = 'Ambil Hari Ini';
            $tautan = "../admin/transaksi.php?status=siap_diambil";
            if (!$modelNotif->sudahAdaHariIni($admin_id, $judul, $tautan)) {
                $modelNotif->kirim($admin_id, $judul,
                    "{$total} pesanan siap diambil hari ini. Pastikan alat sudah disiapkan.",
                    'info', $tautan);
            }
        }
    }

    // ----- Cek notifikasi admin: kembali hari ini -----
    public function cekNotifikasiKembaliHariIni($admin_id, $modelNotif) {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = 'disewa' AND tgl_selesai = CURDATE()";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        $total = (int)($row['total'] ?? 0);

        if ($total > 0) {
            $judul = 'Pengembalian Hari Ini';
            $tautan = "../admin/transaksi.php?status=disewa";
            if (!$modelNotif->sudahAdaHariIni($admin_id, $judul, $tautan)) {
                $modelNotif->kirim($admin_id, $judul,
                    "{$total} pesanan harus dikembalikan hari ini. Pantau pengembalian alat.",
                    'warning', $tautan);
            }
        }
    }

    // ----- Cek notifikasi admin: terlambat -----
    public function cekNotifikasiTerlambatAdmin($admin_id, $modelNotif) {
        $query = "SELECT COUNT(id) as total FROM transaksi WHERE status = 'disewa' AND tgl_selesai < CURDATE()";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        $total = (int)($row['total'] ?? 0);

        if ($total > 0) {
            $judul = 'Pengembalian Terlambat';
            $tautan = "../admin/transaksi.php?status=disewa";
            if (!$modelNotif->sudahAdaHariIni($admin_id, $judul, $tautan)) {
                $modelNotif->kirim($admin_id, $judul,
                    "{$total} pesanan sudah melewati batas waktu pengembalian! Segera hubungi pelanggan.",
                    'danger', $tautan);
            }
        }
    }
}
?>

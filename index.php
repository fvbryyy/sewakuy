<?php
session_start();
require_once 'classes/Product.php';
$product = new Product();
$semua_produk = $product->tampilSemua();

$produk_unggulan = [];
$i = 0;
while ($row = mysqli_fetch_assoc($semua_produk)) {
    if ($i >= 4) break;
    $produk_unggulan[] = $row;
    $i++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewakuy - Sewa Kamera & Lensa Mudah & Terpercaya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="public/css/customer.css">
</head>
<body>

    <?php require_once 'templates/alert.php'; ?>

    <nav class="navbar lp-nav">
        <div class="logo">sewa<span>kuy</span></div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php">Panel Admin</a>
                <a href="auth/logout.php" class="btn-logout">Keluar</a>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <a href="customer/index.php">Beranda</a>
                <a href="auth/logout.php" class="btn-logout">Keluar</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn-login">Masuk</a>
                <a href="auth/register.php" class="btn-profile">Daftar Akun</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero -->
    <section class="lp-hero">
        <div class="lp-hero-bg" style="background-image:url('public/img/hero.jpg');"></div>
        <div class="lp-hero-overlay"></div>
        <div class="lp-hero-content">
            <span class="lp-badge"><i class="fas fa-camera-retro"></i> Sewa Alat Multimedia #1 di Yogyakarta</span>
            <h1 class="lp-hero-title">Abadikan Momen Berhargamu dengan <span>Peralatan Profesional</span></h1>
            <p class="lp-hero-desc">Sewa kamera, lensa, aksesoris, dan perlengkapan dokumentasi berkualitas tinggi. Proses cepat, harga transparan, alat terawat.</p>
            <div class="lp-hero-actions">
                <a href="auth/register.php" class="btn-cta"><i class="fas fa-play"></i> Mulai Sewa</a>
                <a href="#katalog" class="btn-cta-outline"><i class="fas fa-eye"></i> Lihat Katalog</a>
            </div>
            <div class="lp-hero-stats">
                <div class="lp-stat-item">
                    <span class="lp-stat-num">20+</span>
                    <span class="lp-stat-label">Unit Alat</span>
                </div>
                <div class="lp-stat-divider"></div>
                <div class="lp-stat-item">
                    <span class="lp-stat-num">100%</span>
                    <span class="lp-stat-label">Terawat</span>
                </div>
                <div class="lp-stat-divider"></div>
                <div class="lp-stat-item">
                    <span class="lp-stat-num">24h</span>
                    <span class="lp-stat-label">Layanan</span>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="lp-steps">
        <div class="lp-section-container">
            <span class="lp-section-badge">Cara Kerja</span>
            <h2 class="lp-section-title">Sewa Alat dalam 3 Langkah Mudah</h2>
            <div class="lp-steps-grid">
                <div class="lp-step-card">
                    <div class="lp-step-num">01</div>
                    <div class="lp-step-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>Daftar Akun</h3>
                    <p>Buat akun gratis dalam hitungan detik. Isi data diri dan siap mulai.</p>
                </div>
                <div class="lp-step-arrow"><i class="fas fa-arrow-right"></i></div>
                <div class="lp-step-card">
                    <div class="lp-step-num">02</div>
                    <div class="lp-step-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3>Pilih & Sewa</h3>
                    <p>Jelajahi katalog, pilih alat yang dibutuhkan, lalu selesaikan pembayaran.</p>
                </div>
                <div class="lp-step-arrow"><i class="fas fa-arrow-right"></i></div>
                <div class="lp-step-card">
                    <div class="lp-step-num">03</div>
                    <div class="lp-step-icon"><i class="fas fa-camera"></i></div>
                    <h3>Ambil & Gunakan</h3>
                    <p>Ambil alat di toko, gunakan untuk proyekmu, kembalikan saat selesai.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section class="lp-about">
        <div class="lp-section-container">
            <span class="lp-section-badge lp-badge-light">Mengapa Sewakuy?</span>
            <h2 class="lp-section-title lp-title-light">Solusi Sewa Peralatan Dokumentasi Terpercaya</h2>
            <p class="lp-section-desc lp-desc-light">Kami hadir untuk memudahkanmu mendapatkan peralatan dokumentasi berkualitas tanpa ribet.</p>
            <div class="lp-about-grid-v2">
                <div class="lp-about-card">
                    <div class="lp-about-card-icon icon-green"><i class="fas fa-shield-halved"></i></div>
                    <h4>Peralatan Terawat</h4>
                    <p>Seluruh unit diperiksa dan dirawat rutin sebelum disewakan.</p>
                </div>
                <div class="lp-about-card">
                    <div class="lp-about-card-icon icon-blue"><i class="fas fa-bolt"></i></div>
                    <h4>Proses Cepat</h4>
                    <p>Pemesanan online praktis, konfirmasi instan, alat siap diambil.</p>
                </div>
                <div class="lp-about-card">
                    <div class="lp-about-card-icon icon-amber"><i class="fas fa-tag"></i></div>
                    <h4>Harga Transparan</h4>
                    <p>Tidak ada biaya tersembunyi. Harga sewa per hari jelas dan terjangkau.</p>
                </div>
                <div class="lp-about-card">
                    <div class="lp-about-card-icon icon-purple"><i class="fas fa-headset"></i></div>
                    <h4>Dukungan Ramah</h4>
                    <p>Tim kami siap membantu konsultasi alat yang tepat untuk kebutuhanmu.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="lp-products" id="katalog">
        <div class="lp-section-container">
            <span class="lp-section-badge">Katalog</span>
            <h2 class="lp-section-title">Alat Populer yang Banyak Disewa</h2>
            <p class="lp-section-desc">Pilihan peralatan terbaik untuk fotografi, videografi, dan produksi konten.</p>
            <?php if (!empty($produk_unggulan)): ?>
            <div class="lp-products-grid">
                <?php foreach ($produk_unggulan as $row): ?>
                <div class="lp-product-card">
                    <div class="lp-product-img">
                        <?php if (!empty($row['gambar'])): ?>
                            <img src="public/produk/<?= htmlspecialchars($row['gambar'], ENT_QUOTES); ?>" alt="<?= htmlspecialchars($row['nama_produk']); ?>">
                        <?php else: ?>
                            <img src="public/produk/no-image.png" alt="Tidak Ada Gambar">
                        <?php endif; ?>
                    </div>
                    <div class="lp-product-body">
                        <span class="lp-product-category"><?= htmlspecialchars($row['nama_kategori']); ?></span>
                        <h3><?= htmlspecialchars($row['nama_produk']); ?></h3>
                        <p class="lp-product-desc"><?= htmlspecialchars($row['deskripsi']); ?></p>
                        <div class="lp-product-footer">
                            <div class="lp-product-price">
                                <span class="lp-price-amount">Rp <?= number_format($row['harga_perhari'], 0, ',', '.'); ?></span>
                                <span class="lp-price-unit">/hari</span>
                            </div>
                            <a href="auth/register.php" class="btn-sewa"><i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="lp-cta">
        <div class="lp-section-container">
            <div class="lp-cta-box">
                <div class="lp-cta-content">
                    <h2>Siap Memulai Proyekmu?</h2>
                    <p>Daftar sekarang dan dapatkan akses ke seluruh kamera, lensa, dan perlengkapan profesional kami.</p>
                </div>
                <a href="auth/register.php" class="btn-cta-outline-light"><i class="fas fa-rocket"></i> Daftar Gratis Sekarang</a>
            </div>
        </div>
    </section>

    <?php require_once 'templates/footer.php'; ?>

</body>
</html>

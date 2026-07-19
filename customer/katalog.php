<?php
session_start();
require_once '../config/AuthCheck.php';
cekPelanggan();

require_once '../classes/Product.php';
$product = new Product();

$daftar_kategori = $product->tampilKategori();

// ----- Filter kategori & search -----
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

if ($search !== '') {
    $semua_produk = $product->cari($search);
} elseif ($kategori !== '') {
    $semua_produk = $product->tampilPerKategori($kategori);
} else {
    $semua_produk = $product->tampilSemua();
}

// ----- Live search: AJAX request -> return only grid -----
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    require_once '../templates/product_grid.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Alat - Sewakuy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../public/css/customer.css">
</head>
<body>

<?php require_once '../templates/alert.php'; ?>
<?php require_once '../templates/navbar.php'; ?>

<div class="katalog-container">

    <div class="katalog-header-landing">
        <h2 class="page-title">Mulai Sewa Alat Multimedia</h2>
        <p class="page-subtitle">
            Pilih unit yang kamu butuhkan, masukkan keranjang, lalu selesaikan pembayaran sewa.
        </p>
    </div>

    <div class="search-filter-wrapper">
        <div class="search-bar">
            <form method="GET" action="katalog.php">
                <input type="text" name="search" class="search-input" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                <?php if ($search !== ''): ?>
                    <a href="katalog.php" class="search-clear"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="filter-container">
            <a href="katalog.php" class="filter-btn <?= (!isset($_GET['kategori']) || $_GET['kategori'] == '') ? 'active' : ''; ?>">
                Semua Alat
            </a>

            <?php mysqli_data_seek($daftar_kategori, 0); ?>
            <?php while ($kat = mysqli_fetch_assoc($daftar_kategori)): ?>
                <a href="katalog.php?kategori=<?= $kat['id']; ?>" class="filter-btn <?= (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id']) ? 'active' : ''; ?>" data-kategori="<?= $kat['id']; ?>">
                    <?= htmlspecialchars($kat['nama_kategori']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="grid-produk-wrap">
        <?php require_once '../templates/product_grid.php'; ?>
    </div>

</div>

<?php require_once '../templates/footer.php'; ?>

<script>
(function() {
    const searchInput = document.querySelector('.search-input');
    const gridWrap = document.getElementById('grid-produk-wrap');
    const filterBtns = document.querySelectorAll('.filter-btn');
    let debounceTimer;

    function loadGrid(params) {
        const url = 'katalog.php?' + new URLSearchParams(params).toString();
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            if (xhr.status === 200) {
                gridWrap.innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function getActiveKategori() {
        const active = document.querySelector('.filter-btn.active');
        if (active && active.dataset.kategori !== undefined) {
            return active.dataset.kategori;
        }
        return '';
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            const params = {};
            if (searchInput.value.trim() !== '') {
                params.search = searchInput.value.trim();
            } else {
                const kat = getActiveKategori();
                if (kat) params.kategori = kat;
            }
            loadGrid(params);
        }, 300);
    });

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            searchInput.value = '';
            const params = {};
            const href = btn.getAttribute('href');
            const urlParams = new URLSearchParams(href.split('?')[1] || '');
            if (urlParams.has('kategori')) {
                params.kategori = urlParams.get('kategori');
            }
            loadGrid(params);
        });
    });
})();
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar - Sewakuy</title>
    <link rel="stylesheet" href="../public/css/auth.css">
</head>
<body>

    <div class="container">
        <?php session_start(); require_once '../templates/alert.php'; ?>
        
        <h2>Daftar Akun</h2>

        <form action="../proses/auth.php" method="POST">
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Buat Kata Sandi" required>
            <input type="text" name="no_hp" placeholder="No HP (WhatsApp)" required>
            <input type="text" name="alamat" placeholder="Masukkan alamat" required>

            <button type="submit" name="register">Daftar</button>
        </form>
        
        <p>Sudah memiliki akun? <a href="login.php">Masuk</a></p>

    </div>

</body>
</html>
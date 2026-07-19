<!DOCTYPE html>
<html lang="id">
<head>
    <title>Masuk - Sewakuy</title>
    <link rel="stylesheet" href="../public/css/auth.css">
</head>
<body>
    <div class="container">
        <?php session_start(); require_once '../templates/alert.php'; ?>
        
        <h2>Masuk</h2>

        <form action="../proses/auth.php" method="POST">
            <input type="email" name="email" placeholder="Masukkan Email Anda" required>
            <input type="password" name="password" placeholder="Masukkan Kata Sandi Anda" required>
            <button type="submit" name="login">Masuk</button>
        </form>
        <p>Belum memiliki akun? <a href="register.php">Daftar</a></p>
    </div>

</body>
</html>
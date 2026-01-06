<?php
// Mulai Session
session_start();

// Debug: Tampilkan error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Hubungkan ke Database
require_once '../config.php';

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$error = "";
$username_input = "";

if (isset($_POST['login'])) {
    // Ambil data dari form
    $username_input = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = $_POST['password'];

    // Query cari user berdasarkan username
    $query = "SELECT * FROM users WHERE username = '$username_input'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query gagal: " . mysqli_error($conn));
    }
    
    $num_rows = mysqli_num_rows($result);

    if ($num_rows === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek Password dengan password_verify (karena password di database sudah di-hash)
        if (password_verify($password_input, $row['password'])) {
            // Login Berhasil
            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            
            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "Cannot find your username";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mathogram</title>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../img/Logo-white.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<style>
body {
    background-image: url('../img/Login.svg');
    background-size: cover;
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-color: var(--primary-blue); 
    display: flex;
    flex-direction: column;
    height: 100vh;
    font-family: 'Arial', sans-serif;
}
footer{
    background-color: #ffffff00;
    border-top: #ffffff00;
    text-shadow: 0 2px 5px #00000033;

}
footer>div{
    justify-content: center;
}
/* 2. Biarkan pembungkus konten mengambil semua sisa ruang */
.main-wrapper {
    flex: 1; /* Ini yang mendorong footer ke dasar layar */
}

@media (max-width: 768px){
    body{
        height: 105vh;
    }
}
</style>
<body>
<div class="main-wrapper">
        <div class="login">
            <div class="box-login">
                <div class="button-group">
                    <a href="../index.php" class="back-arrow">&larr;</a>
                </div>
                <img src="../img/Logo.svg">
                <h1>SIGN IN</h1>

                <?php if ($error) : ?>
                    <div class="error-msg" style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="login-input">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" 
                               value="<?php echo htmlspecialchars($username_input); ?>" 
                               placeholder="Account Username" required>
                    </div>
    
                    <div class="login-input">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" 
                               placeholder="Example123" required>
                    </div>
    
                    <div class="helper-text" onclick="window.location.href='register.php'">
                        Still haven't account yet? Sign up!
                    </div>
    
                    <div class="button-group">
                        <button type="submit" name="login" class="buttons">LOGIN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <div>
            <p>&copy; 2025 <strong>Mathogram</strong> - Multiple Educational Mathematics Operation</p>
            <div class="footer-links">
                <a href="#">Terms & service</a>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
// Mulai session
session_start();

// Hubungkan ke database
require_once '../config.php';

// Inisialisasi variabel
$error = "";
$success = "";
$username_input = "";
$email_input = "";

// Proses registrasi ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Ambil data dari form
    $username_input = mysqli_real_escape_string($conn, $_POST['username']);
    $email_input = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    $errors = [];
    
    // 1. Validasi Username
    if (strlen($username_input) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    // 2. Validasi Email - HARUS mengandung @ dan domain
    if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address (example@gmail.com)";
    } else {
        // Pastikan email memiliki format yang benar
        $email_parts = explode('@', $email_input);
        if (count($email_parts) !== 2 || empty($email_parts[0]) || empty($email_parts[1])) {
            $errors[] = "Email must contain '@' and domain (example@gmail.com)";
        }
    }
    
    // 3. Validasi Password
    if (strlen($password_input) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // 4. Validasi Konfirmasi Password
    if ($password_input !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Jika tidak ada error validasi, lanjutkan
    if (empty($errors)) {
        // Cek apakah username sudah digunakan
        $check_username = "SELECT id FROM users WHERE username = '$username_input'";
        $result_username = mysqli_query($conn, $check_username);
        
        if (mysqli_num_rows($result_username) > 0) {
            $errors[] = "Username already exists. Please choose another one.";
        }
        
        // Cek apakah email sudah digunakan
        $check_email = "SELECT id FROM users WHERE email = '$email_input'";
        $result_email = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result_email) > 0) {
            $errors[] = "Email already registered. Please use another email.";
        }
        
        // Jika username dan email tersedia
        if (empty($errors)) {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);
            
            // Query insert user baru - sesuai dengan struktur tabel
            $query = "INSERT INTO users (username, email, password, created_at) 
                     VALUES ('$username_input', '$email_input', '$hashed_password', NOW())";
            
            if (mysqli_query($conn, $query)) {
                // Registrasi sukses
                $success = "Registration successful! Redirecting to login...";
                
                // Reset input
                $username_input = "";
                $email_input = "";
                
                // Redirect ke login setelah 2 detik
                header("refresh:2;url=login.php");
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
        
        // Jika ada errors, gabungkan ke string error
        if (!empty($errors)) {
            $error = implode("<br>", $errors);
        }
    } else {
        // Jika ada validasi errors
        $error = implode("<br>", $errors);
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
    min-height: 100vh;
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

</style>
<body>
    <div class="main-wrapper">
        <div class="login">
            <div class="box-login">
                <div class="button-group">
                    <a href="login.php" class="back-arrow">&larr;</a>
                </div>
                <img src="../img/Logo.svg">
                <h1>SIGN IN</h1>
                
                <?php if ($error): ?>
                    <div class="error-message" style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message" style="background-color: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="login-input">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Account Username" 
                               value="<?php echo htmlspecialchars($username_input); ?>" required>
                    </div>
                    <div class="login-input">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com" 
                               value="<?php echo htmlspecialchars($email_input); ?>" required>
                    </div>
                    <div class="login-input">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Example123" required>
                    </div>
                    <div class="login-input">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                    </div>
    
                    <div class="helper-text" onclick="window.location.href='login.php'">Already had an account? Sign in</div>
    
                    <div class="button-group">
                        <button type="submit" name="register" class="buttons">REGISTER</button>
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
    
    <script>
    // Real-time validation
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Validasi email real-time
        emailInput.addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = '#c62828';
            } else {
                this.style.borderColor = '#ddd';
            }
        });
        
        // Validasi konfirmasi password real-time
        function validatePassword() {
            if (passwordInput.value && confirmPasswordInput.value) {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.style.borderColor = '#c62828';
                } else {
                    confirmPasswordInput.style.borderColor = '#4CAF50';
                }
            }
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
        
        // Validasi saat form submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const errors = [];
            
            // Validasi username
            const username = document.getElementById('username').value;
            if (username.length < 3) {
                errors.push("Username must be at least 3 characters");
                isValid = false;
            }
            
            // Validasi email
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errors.push("Please enter a valid email (example@gmail.com)");
                isValid = false;
            }
            
            // Validasi password
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                errors.push("Password must be at least 6 characters");
                isValid = false;
            }
            
            // Validasi confirm password
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                errors.push("Passwords do not match");
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errors.join('\n'));
            }
        });
    });
    </script>
</body>
</html>
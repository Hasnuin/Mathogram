<?php
// index.php - Dashboard untuk guest/non-login users
require_once 'config.php';
session_start();

// Cek jika user sudah login, redirect ke dashboard
if (isset($_SESSION['id'])) {
    header('Location: dashboard.php');
    exit();
}

// Ambil konten terpopuler/terbaru untuk ditampilkan
$query = "SELECT c.*, u.username, 
                 (SELECT COUNT(*) FROM bookmarks WHERE content_id = c.id) as bookmark_count,
                 (SELECT COUNT(*) FROM quiz_results WHERE content_id = c.id) as quiz_attempts
          FROM contents c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.status = 'published'
          ORDER BY c.created_at DESC 
          LIMIT 12";

$result = mysqli_query($conn, $query);
$content_count = mysqli_num_rows($result);
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
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="img/Logo-white.svg">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <!-- HEADER (Tanpa Sidebar) -->
    <header class="top-bar" style="margin: 0; padding: 15px 30px; border-radius: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="logo-area">
            <i><img src="img/Logo.svg" width="50"></i>
        </div>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search content..." disabled title="Please login to search">
        </div>

        <div class="profile-area">
            <button class="buttons" onclick="window.location.href='login/login.php'" style="background: var(--minor-green);">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            <button class="buttons" onclick="window.location.href='login/register.php'">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </div>
    </header>

    <!-- HERO BANNER -->
    <div class="hero-banner d-flex align-items-end" style="margin: 20px; border-radius: 15px;">
        <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
             alt="Mathematics Learning">
        <div class="hero-overlay w-100 d-flex justify-content-between align-items-center">
            <div class="banner-img">
                <h3>Welcome to Mathogram</h3>
                <h5 class="">Learn Mathematics Together with Interactive Content</h5>
                <small>Join our community of learners</small>
            </div>
            <div>
                <button class="buttons" onclick="window.location.href='login/register.php'" 
                        style="background: var(--minor-green);">
                    <i class="fas fa-rocket"></i> Get Started Free
                </button>
            </div>
        </div>
    </div>

    <!-- LATEST CONTENT -->
    <div style="padding: 20px;">
        
        <div class="content">
            <?php if ($content_count > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="post" onclick="showLoginPrompt()">
                        <div class="image">
                            <!-- Stats Overlay -->
                            
                            <img src="<?php 
                                $thumbnail = $row['thumbnail_path'] ?? '';
                                if (!empty($thumbnail) && strpos($thumbnail, 'uploads/thumbnails/') === false) {
                                    echo 'uploads/thumbnails/' . htmlspecialchars($thumbnail);
                                } else {
                                    echo htmlspecialchars($thumbnail ?: 'uploads/thumbnails/default.jpg');
                                }
                            ?>" alt="<?php echo htmlspecialchars($row['title']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x225/326090/ffffff?text=<?php echo urlencode(substr($row['title'], 0, 20)); ?>'">
                        </div>
                        <div class="inner">             
                            <h1><?php echo htmlspecialchars($row['title']); ?></h1>
                            <div class="footer-row">
                                <div class="user-info">
                                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                    <span class="username-text"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                                <button class="buttons"><?php echo htmlspecialchars($row['category']); ?></button>
                            </div>
                            <div style="margin-top: 10px; font-size: 12px; color: var(--text-color); opacity: 0.7;">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; grid-column: 1 / -1; color: var(--text-color);">
                    <i class="fas fa-inbox fa-3x" style="color: var(--nav-hover); margin-bottom: 20px;"></i>
                    <h3>No Content Available</h3>
                    <p>Be the first to upload content!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CALL TO ACTION -->
    <div style="text-align: center; padding: 60px 20px; background: linear-gradient(135deg, var(--primary-blue), #2b5079); border-radius: 15px; margin: 40px 20px; color: white;">
        <h2 style="margin-bottom: 20px;">Ready to Start Learning?</h2>
        <p style="margin-bottom: 30px; opacity: 0.9; max-width: 600px; margin-left: auto; margin-right: auto;">
            Join thousands of students who are already improving their math skills with LearnHub
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <button class="buttons" onclick="window.location.href='login/register.php'" 
                    style="background: white; color: var(--primary-blue); font-weight: bold; padding: 15px 30px;">
                <i class="fas fa-user-plus"></i> Sign Up Free
            </button>
            <button class="buttons" onclick="window.location.href='login/login.php'" 
                    style="background: transparent; border: 2px solid white; color: white; padding: 15px 30px;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include('Nav/footer.php'); ?>

    <!-- LOGIN MODAL PROMPT -->
    <div class="ovr" id="loginPrompt" style="display: none;">
        <div class="popup-content" style="max-width: 500px;">
            <div class="popup-head">
                <h2>Login Required</h2>
                <button class="circle" onclick="closeLoginPrompt()">&times;</button>
            </div>
            
            <div class="popup-main0">
                <div class="popup-colu1" style="text-align: center; padding: 40px 30px;">
                    <i class="fas fa-lock fa-4x" style="color: var(--primary-blue); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--text-color); margin-bottom: 15px;">Please Login to Continue</h3>
                    <p style="color: var(--text-color); opacity: 0.8; margin-bottom: 30px;">
                        You need to login to view content details, take quizzes, and access all features.
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <button class="buttons" onclick="window.location.href='login/login.php'" 
                                style="background: var(--primary-blue); padding: 15px;">
                            <i class="fas fa-sign-in-alt"></i> Login to Your Account
                        </button>
                        <button class="buttons" onclick="window.location.href='login/register.php'" 
                                style="background: var(--minor-green); padding: 15px;">
                            <i class="fas fa-user-plus"></i> Create New Account
                        </button>
                        <button class="buttons" onclick="closeLoginPrompt()" 
                                style="background: var(--nav-hover); color: var(--text-color); padding: 15px;">
                            Continue Browsing
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Fungsi untuk menampilkan login prompt
    function showLoginPrompt() {
        document.getElementById('loginPrompt').style.display = 'flex';
    }
    
    // Fungsi untuk menutup login prompt
    function closeLoginPrompt() {
        document.getElementById('loginPrompt').style.display = 'none';
    }
    
    // Close modal dengan ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLoginPrompt();
        }
    });
    
    // Close modal saat klik di luar
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('loginPrompt');
        const modalContent = document.querySelector('#loginPrompt .popup-content');
        
        if (modal.style.display === 'flex' && modalContent && !modalContent.contains(e.target)) {
            closeLoginPrompt();
        }
    });
    
    // Theme toggle (ambil dari localStorage)
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    });
    </script>
</body>
</html>
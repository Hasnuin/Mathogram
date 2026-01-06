<?php
// Set variabel untuk menentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="logo-area">
        <i><img src="img/Logo.svg" width="50"></i>
    </div>
    
<!-- Di sidebar.php, tambahkan menu bookmark -->
    <nav>
        <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="history.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>History</span>
        </a>
        <a href="bookmark.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bookmark.php' ? 'active' : ''; ?>">
            <i class="fas fa-bookmark"></i>
            <span>Bookmarks</span>
            <?php
            // Tampilkan jumlah bookmark
            if (isset($_SESSION['id'])) {
                $user_id = $_SESSION['id'];
                $bookmark_count_query = "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $bookmark_count_query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $bookmark_count = mysqli_fetch_assoc($result)['count'] ?? 0;
                
                if ($bookmark_count > 0) {
                    echo '<span class="badge" style="background: var(--minor-orange); color: white; border-radius: 10px; padding: 2px 8px; font-size: 12px; margin-left: auto;">' . $bookmark_count . '</span>';
                }
            }
            ?>
        </a>

    </nav>

    <div class="sidebar-footer">
        <nav>
            <a class="nav-link" href="javascript:void(0)" id="settings-btn">
                <i class="fas fa-cog"></i> <span>Settings</span>
            </a>
        </nav>
    
        <div id="settings-popup" class="settings-popup">
            <div class="popup-header">
                <span>Appearance</span>
                <i class="fas fa-times" id="close-popup"></i>
            </div>
            <div class="popup-body">
                <div class="setting-item">
                    <label for="theme-select">Theme</label>
                    <select id="theme-select" class="theme-dropdown">
                        <option value="light">Light Mode</option>
                        <option value="dark">Dark Mode</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// 1. Validasi login
require_once 'Nav/auth_check.php';

// 2. Koneksi ke database
require_once 'config.php';

// Ambil data user dari session
$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// Query untuk mendapatkan data user
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

// Format tanggal join
$join_date = date('d/m/Y', strtotime($user_data['created_at'] ?? 'now'));

// ============================================
// AMBIL KONTEN YANG DIUPLOAD OLEH USER
// ============================================
$content_query = "SELECT * FROM contents WHERE user_id = '$user_id' ORDER BY created_at DESC";
$content_result = mysqli_query($conn, $content_query);
$content_count = mysqli_num_rows($content_result);
?>

<?php include('Nav/header.php'); ?>
<?php include('Nav/sidebar.php'); ?>
<?php include('Nav/topbar.php'); ?>

<!-- KONTEN PROFIL -->

<?php include('popups/profile_card.php'); ?>

<div class="content">
    <?php if ($content_count > 0): ?>
        <?php 
        // Loop melalui setiap konten
        while($row = mysqli_fetch_assoc($content_result)): 
            // Tambahkan username ke array row untuk post_card.php
            $row['username'] = $username;
        ?>
            <?php include('popups/post_card.php'); ?>
        <?php endwhile; ?>
    <?php else: ?>
        <!-- Tampilkan tombol upload jika tidak ada konten -->
        <div class="history" style="grid-column: 2;">
            <p>You haven't uploaded any content yet.</p>
            <button class="buttons" onclick="openModal()">Upload Content</button>
        </div>
    <?php endif; ?>
</div>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
<script src="js/script.js"></script>
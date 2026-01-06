<?php 
// dashboard.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$current_user_id = $_SESSION['id'];

// Query untuk mendapatkan konten acak
$query = "SELECT c.*, u.username 
          FROM contents c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.status = 'published' 
          ORDER BY RAND() 
          LIMIT 12";

$content_result = mysqli_query($conn, $query);
$content_count = mysqli_num_rows($content_result);

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<!-- KONTEN KHUSUS DASHBOARD -->
<div class="hero-banner d-flex align-items-end" style="margin: 20px; border-radius: 15px;">
        <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
             alt="Mathematics Learning">
        <div class="hero-overlay w-100 d-flex justify-content-between align-items-center">
            <div class="banner-img">
                <h3>Welcome to Mathogram</h3>
                <h5 class="">Learn Mathematics Together with Interactive Content</h5>
            </div>
        </div>
    </div>

<div class="content">
    <?php if ($content_count > 0): ?>
        <?php while($row = mysqli_fetch_assoc($content_result)): ?>
            <?php 
            // Kirim data ke post_card.php
            include('popups/post_card.php'); 
            ?>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <p>No content available yet.</p>
            <button class="buttons" onclick="openModal()">Upload Content</button>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL UNTUK VIEW CONTENT -->
<?php include('upload.php'); ?>
<?php include('popups/viewpost.php'); ?>

<?php include('Nav/footer.php'); ?>
<script src="js/script.js"></script>

<script>
// Di dashboard.php, tambahkan fungsi untuk check bookmark status
function checkBookmarkStatus(contentId) {
    fetch('bookmark_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check',
            content_id: contentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.is_bookmarked) {
            // Update UI jika perlu
            console.log('Content ' + contentId + ' is bookmarked');
        }
    });
}

// Panggil untuk setiap konten saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Ambil semua content ID dari post cards
    const postCards = document.querySelectorAll('.post');
    postCards.forEach(card => {
        const contentId = card.getAttribute('onclick')?.match(/id:\s*(\d+)/)?.[1];
        if (contentId) {
            checkBookmarkStatus(contentId);
        }
    });
});
</script>
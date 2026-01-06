<?php 
// history.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// ============================================
// AMBIL HISTORY QUIZ YANG TELAH DIKERJAKAN
// ============================================
$history_query = "SELECT qr.*, c.title, c.thumbnail_path, c.category, 
                         u.username as author_name
                  FROM quiz_results qr
                  JOIN contents c ON qr.content_id = c.id
                  JOIN users u ON c.user_id = u.id
                  WHERE qr.user_id = '$user_id'
                  ORDER BY qr.finished_at DESC";

$history_result = mysqli_query($conn, $history_query);
$history_count = mysqli_num_rows($history_result);

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<!-- KONTEN KHUSUS HISTORY -->
<div class="history">
    <h2 style="padding: 20px; color: var(--text-color);">Quiz History</h2>
    
    <?php if ($history_count > 0): ?>
        <?php while($quiz_result = mysqli_fetch_assoc($history_result)): ?>
            <?php 
            // Kirim data ke history_card.php
            $quiz_data = [
                'result_id' => $quiz_result['id'],
                'title' => $quiz_result['title'],
                'thumbnail_path' => $quiz_result['thumbnail_path'],
                'category' => $quiz_result['category'],
                'author_name' => $quiz_result['author_name'],
                'score' => $quiz_result['score'],
                'correct_answers' => $quiz_result['correct_answers'],
                'total_questions' => $quiz_result['total_questions'],
                'finished_at' => $quiz_result['finished_at']
            ];
            ?>
            <?php include('popups/history_card.php'); ?>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <h3>No Quiz History Yet</h3>
            <p>You haven't taken any quizzes yet. Start exploring content and take quizzes!</p>
            <button class="buttons" onclick="window.location.href='dashboard.php'">Browse Content</button>
        </div>
    <?php endif; ?>
</div>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
<script src="js/script.js"></script>
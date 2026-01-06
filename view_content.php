<?php
// view_content.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$content_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'view';

// Validasi content_id
if ($content_id <= 0) {
    die("Invalid content ID");
}

// ============================================
// AMBIL DATA KONTEN
// ============================================
$query = "SELECT c.*, u.username, u.profile_image 
          FROM contents c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Content not found");
}

$content = mysqli_fetch_assoc($result);

// Cek apakah user adalah pemilik konten
$current_user_id = $_SESSION['id'];
$is_owner = ($content['user_id'] == $current_user_id);

// ============================================
// AMBIL QUIZ (JIKA ADA)
// ============================================
$quiz_query = "SELECT COUNT(*) as quiz_count 
               FROM quiz_questions 
               WHERE content_id = ?";
$stmt = mysqli_prepare($conn, $quiz_query);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);
$quiz_result = mysqli_stmt_get_result($stmt);
$quiz_data = mysqli_fetch_assoc($quiz_result);
$has_quiz = ($quiz_data['quiz_count'] > 0);

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<style>
.content-detail-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary-blue);
}

.content-title {
    color: var(--text-color);
    margin: 0;
}

.content-media-container {
    background: var(--nav-color);
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.content-media {
    width: 100%;
    height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
}

.content-media img,
.content-media video {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.content-info {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
}

.author-info {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--nav-hover);
}

.author-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
}

.author-details h4 {
    margin: 0;
    color: var(--text-color);
}

.author-details small {
    color: var(--text-color);
    opacity: 0.7;
}

.content-description h3 {
    color: var(--text-color);
    margin-top: 0;
}

.content-description p {
    color: var(--text-color);
    line-height: 1.6;
    white-space: pre-line;
}

.content-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid var(--nav-hover);
}

.category-badge {
    background: var(--primary-blue);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 14px;
}

.type-badge {
    background: var(--minor-orange);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 14px;
}

.date-badge {
    background: var(--nav-hover);
    color: var(--text-color);
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 14px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-edit {
    background: var(--primary-blue);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.btn-delete {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.btn-quiz {
    background: var(--minor-green);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.quiz-info {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 20px;
    margin-top: 20px;
    border-left: 5px solid var(--minor-green);
    color: var(--text-color)
}

.related-content {
    margin-top: 40px;
}

.related-title {
    color: var(--text-color);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-blue);
}
</style>

<div class="content-detail-container">
    <!-- Header -->
    <div class="content-header">
        <h1 class="content-title"><?php echo htmlspecialchars($content['title']); ?></h1>
        <div>
            <?php if ($is_owner && $mode == 'edit'): ?>
                <button class="btn-edit" onclick="window.location.href='edit_content.php?id=<?php echo $content_id; ?>'">
                    <i class="fas fa-edit"></i> Edit Content
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Media (Image/Video) -->
    <div class="content-media-container">
        <div class="content-media">
            <?php if ($content['file_type'] == 'video'): ?>
                <?php 
                $video_path = strpos($content['file_path'], 'uploads/content/') === 0 ? 
                    $content['file_path'] : 'uploads/content/' . $content['file_path'];
                ?>
                <video controls style="width:100%; height:100%;">
                    <source src="<?php echo htmlspecialchars($video_path); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <?php 
                $file_path = strpos($content['file_path'], 'uploads/content/') === 0 ? 
                    $content['file_path'] : 'uploads/content/' . $content['file_path'];
                ?>
                <img src="<?php echo htmlspecialchars($file_path); ?>" 
                     alt="<?php echo htmlspecialchars($content['title']); ?>"
                     onerror="this.src='https://via.placeholder.com/800x450/326090/ffffff?text=Image+Not+Found'">
            <?php endif; ?>
        </div>
    </div>

    <!-- Content Info -->
    <div class="content-info">
        <!-- Author Info -->
        <div class="author-info">
            <?php 
            $avatar_path = !empty($content['profile_image']) ? 
                (strpos($content['profile_image'], 'uploads/profiles/') === 0 ? 
                    $content['profile_image'] : 'uploads/profiles/' . $content['profile_image']) : 
                '';
            ?>
            <?php if (!empty($avatar_path)): ?>
                <img src="<?php echo htmlspecialchars($avatar_path); ?>" 
                     alt="<?php echo htmlspecialchars($content['username']); ?>"
                     class="author-avatar"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <?php endif; ?>
            <i class="fas fa-user-circle author-avatar" 
               style="font-size: 60px; color: var(--primary-blue); <?php echo !empty($avatar_path) ? 'display:none;' : ''; ?>"></i>
            
            <div class="author-details">
                <h4><?php echo htmlspecialchars($content['username']); ?></h4>
                <small>Uploaded on <?php echo date('F j, Y', strtotime($content['created_at'])); ?></small>
            </div>
        </div>

        <!-- Description -->
        <div class="content-description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($content['description'])); ?></p>
        </div>

        <!-- Meta Info -->
        <div class="content-meta">
            <span class="category-badge">
                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($content['category']); ?>
            </span>
            <span class="type-badge">
                <i class="fas fa-file"></i> <?php echo ucfirst($content['file_type']); ?>
            </span>
            <span class="date-badge">
                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($content['created_at'])); ?>
            </span>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ($has_quiz): ?>
                <button class="btn-quiz" onclick="window.location.href='quiz.php?content_id=<?php echo $content_id; ?>'">
                    <i class="fas fa-play-circle"></i> Start Quiz (<?php echo $quiz_data['quiz_count']; ?> questions)
                </button>
            <?php endif; ?>
            
            <?php if ($is_owner): ?>
                <button class="btn-edit" onclick="window.location.href='edit_content.php?id=<?php echo $content_id; ?>'">
                    <i class="fas fa-edit"></i> Edit Content
                </button>
                <button class="btn-delete" onclick="deleteContent(<?php echo $content_id; ?>)">
                    <i class="fas fa-trash"></i> Delete
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quiz Info (if has quiz) -->
    <?php if ($has_quiz): ?>
        <div class="quiz-info">
            <h3><i class="fas fa-question-circle"></i> Quiz Available</h3>
            <p>This content has <?php echo $quiz_data['quiz_count']; ?> quiz question(s). Test your understanding!</p>
            <button class="btn-quiz" onclick="window.location.href='quiz.php?content_id=<?php echo $content_id; ?>'">
                <i class="fas fa-play"></i> Take Quiz Now
            </button>
        </div>
    <?php endif; ?>

    <!-- Related Content (Same Category) -->
    <?php
    $related_query = "SELECT c.*, u.username 
                      FROM contents c 
                      JOIN users u ON c.user_id = u.id 
                      WHERE c.category = ? 
                        AND c.id != ? 
                        AND c.status = 'published'
                      ORDER BY RAND() 
                      LIMIT 4";
    $stmt = mysqli_prepare($conn, $related_query);
    mysqli_stmt_bind_param($stmt, "si", $content['category'], $content_id);
    mysqli_stmt_execute($stmt);
    $related_result = mysqli_stmt_get_result($stmt);
    $related_count = mysqli_num_rows($related_result);
    ?>
    
    <?php if ($related_count > 0): ?>
        <div class="related-content">
            <h3 class="related-title">More in <?php echo htmlspecialchars($content['category']); ?></h3>
            <div class="content">
                <?php while($related = mysqli_fetch_assoc($related_result)): ?>
                    <?php 
                    // Kirim data ke post_card.php
                    $row = $related;
                    include('popups/post_card.php'); 
                    ?>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteContent(contentId) {
    if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
        fetch('delete_content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: contentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Content deleted successfully');
                window.location.href = 'profile.php';
            } else {
                alert('Failed to delete content: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting content');
        });
    }
}
</script>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
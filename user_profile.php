<?php
// user_profile.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$viewed_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = $_SESSION['id'];

// Validasi user_id
if ($viewed_user_id <= 0) {
    die("Invalid user ID");
}

// ============================================
// AMBIL DATA USER YANG DILIHAT
// ============================================
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $viewed_user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    die("User not found");
}

$viewed_user = mysqli_fetch_assoc($user_result);

// Cek apakah ini profil sendiri
$is_own_profile = ($viewed_user_id == $current_user_id);

// ============================================
// STATISTIK USER
// ============================================
// Total konten yang diupload
$content_query = "SELECT COUNT(*) as total_content 
                  FROM contents 
                  WHERE user_id = ? AND status = 'published'";
$stmt = mysqli_prepare($conn, $content_query);
mysqli_stmt_bind_param($stmt, "i", $viewed_user_id);
mysqli_stmt_execute($stmt);
$content_result = mysqli_stmt_get_result($stmt);
$content_data = mysqli_fetch_assoc($content_result);
$total_content = $content_data['total_content'] ?? 0;

// Total quiz yang dikerjakan
$quiz_query = "SELECT COUNT(*) as total_quizzes,
                      AVG(score) as avg_score,
                      SUM(correct_answers) as total_correct,
                      SUM(total_questions) as total_questions
               FROM quiz_results 
               WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $quiz_query);
mysqli_stmt_bind_param($stmt, "i", $viewed_user_id);
mysqli_stmt_execute($stmt);
$quiz_result = mysqli_stmt_get_result($stmt);
$quiz_data = mysqli_fetch_assoc($quiz_result);
$total_quizzes = $quiz_data['total_quizzes'] ?? 0;
$avg_score = round($quiz_data['avg_score'] ?? 0);
$correct_percentage = $quiz_data['total_questions'] > 0 ? 
    round(($quiz_data['total_correct'] / $quiz_data['total_questions']) * 100) : 0;

// ============================================
// KONTEN USER
// ============================================
$user_contents_query = "SELECT c.*, u.username 
                        FROM contents c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.user_id = ? AND c.status = 'published'
                        ORDER BY c.created_at DESC";
$stmt = mysqli_prepare($conn, $user_contents_query);
mysqli_stmt_bind_param($stmt, "i", $viewed_user_id);
mysqli_stmt_execute($stmt);
$user_contents_result = mysqli_stmt_get_result($stmt);
$user_contents_count = mysqli_num_rows($user_contents_result);

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<style>
.user-profile-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-header {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--primary-blue);
}

.profile-info h1 {
    color: var(--text-color);
    margin: 0 0 10px 0;
}

.profile-meta {
    color: var(--text-color);
    opacity: 0.8;
    margin-bottom: 15px;
}

.profile-stats {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: var(--primary-blue);
    display: block;
}

.stat-label {
    font-size: 14px;
    color: var(--text-color);
    opacity: 0.7;
}

.profile-bio {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.profile-bio h3 {
    color: var(--text-color);
    margin-top: 0;
}

.profile-bio p {
    color: var(--text-color);
    line-height: 1.6;
    white-space: pre-line;
}

.section-title {
    color: var(--text-color);
    margin: 30px 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-blue);
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--text-color);
    opacity: 0.7;
    grid-column: 1 / -1;
}

.follow-btn {
    background: var(--primary-blue);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
}

.follow-btn:hover {
    background: #2b5079;
}

.follow-btn.following {
    background: var(--minor-green);
}
</style>

<div class="user-profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <?php 
        $avatar_path = !empty($viewed_user['profile_image']) ? 
            (strpos($viewed_user['profile_image'], 'uploads/profiles/') === 0 ? 
                $viewed_user['profile_image'] : 'uploads/profiles/' . $viewed_user['profile_image']) : 
            '';
        ?>
        
        <?php if (!empty($avatar_path)): ?>
            <img src="<?php echo htmlspecialchars($avatar_path); ?>" 
                 alt="<?php echo htmlspecialchars($viewed_user['username']); ?>"
                 class="profile-avatar"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <?php endif; ?>
        
        <i class="fas fa-user-circle profile-avatar" 
           style="font-size: 120px; color: var(--primary-blue); <?php echo !empty($avatar_path) ? 'display:none;' : ''; ?>"></i>
        
        <div class="profile-info" style="flex: 1;">
            <h1><?php echo htmlspecialchars($viewed_user['username']); ?></h1>
            
            <div class="profile-meta">
                <p>
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($viewed_user['email']); ?><br>
                    <i class="fas fa-calendar-alt"></i> Member since: <?php echo date('F Y', strtotime($viewed_user['created_at'])); ?>
                </p>
            </div>
            
            <?php if (!empty($viewed_user['website'])): ?>
                <p>
                    <i class="fas fa-globe"></i> 
                    <a href="<?php echo htmlspecialchars($viewed_user['website']); ?>" target="_blank" style="color: var(--primary-blue);">
                        <?php echo htmlspecialchars($viewed_user['website']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <!-- Follow Button (jika bukan profile sendiri) -->
            <?php if (!$is_own_profile): ?>
                <button class="follow-btn" id="followBtn" onclick="toggleFollow(<?php echo $viewed_user_id; ?>)">
                    <i class="fas fa-user-plus"></i> Follow
                </button>
            <?php endif; ?>
        </div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo $total_content; ?></span>
                <span class="stat-label">Contents</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $total_quizzes; ?></span>
                <span class="stat-label">Quizzes</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $avg_score; ?>%</span>
                <span class="stat-label">Avg Score</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $correct_percentage; ?>%</span>
                <span class="stat-label">Correct</span>
            </div>
        </div>
    </div>

    <!-- Bio -->
    <?php if (!empty($viewed_user['bio'])): ?>
        <div class="profile-bio">
            <h3><i class="fas fa-user-edit"></i> About</h3>
            <p><?php echo nl2br(htmlspecialchars($viewed_user['bio'])); ?></p>
        </div>
    <?php endif; ?>

    <!-- User's Contents -->
    <h3 class="section-title">
        <i class="fas fa-photo-video"></i> 
        <?php echo htmlspecialchars($viewed_user['username']); ?>'s Contents 
        <span style="font-size: 14px; opacity: 0.7;">(<?php echo $total_content; ?>)</span>
    </h3>
    
    <div class="content">
        <?php if ($user_contents_count > 0): ?>
            <?php mysqli_data_seek($user_contents_result, 0); ?>
            <?php while($row = mysqli_fetch_assoc($user_contents_result)): ?>
                <?php include('popups/post_card.php'); ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-upload fa-3x" style="margin-bottom: 20px;"></i>
                <h3>No Content Yet</h3>
                <p><?php echo htmlspecialchars($viewed_user['username']); ?> hasn't uploaded any content yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Follow/Unfollow functionality
function toggleFollow(userId) {
    const followBtn = document.getElementById('followBtn');
    const isFollowing = followBtn.classList.contains('following');
    
    fetch('follow_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            user_id: userId,
            action: isFollowing ? 'unfollow' : 'follow'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isFollowing) {
                followBtn.innerHTML = '<i class="fas fa-user-plus"></i> Follow';
                followBtn.classList.remove('following');
            } else {
                followBtn.innerHTML = '<i class="fas fa-user-check"></i> Following';
                followBtn.classList.add('following');
            }
        } else {
            alert(data.message || 'Action failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Check if already following
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$is_own_profile): ?>
    // Check follow status (you need to implement this)
    // fetch('check_follow.php?user_id=<?php echo $viewed_user_id; ?>')
    // .then(response => response.json())
    // .then(data => {
    //     if (data.is_following) {
    //         const followBtn = document.getElementById('followBtn');
    //         followBtn.innerHTML = '<i class="fas fa-user-check"></i> Following';
    //         followBtn.classList.add('following');
    //     }
    // });
    <?php endif; ?>
});
</script>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
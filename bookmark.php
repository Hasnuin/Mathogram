<?php
// bookmark.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// ============================================
// AMBIL DATA BOOKMARK USER
// ============================================
$bookmark_query = "SELECT b.*, c.*, u.username as content_author
                   FROM bookmarks b
                   JOIN contents c ON b.content_id = c.id
                   JOIN users u ON c.user_id = u.id
                   WHERE b.user_id = ?
                   ORDER BY b.created_at DESC";

$stmt = mysqli_prepare($conn, $bookmark_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$bookmark_result = mysqli_stmt_get_result($stmt);
$bookmark_count = mysqli_num_rows($bookmark_result);

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<style>
.bookmark-header {
    padding: 20px;
    color: var(--text-color);
    border-bottom: 2px solid var(--primary-blue);
    margin-bottom: 20px;
}

.bookmark-stats {
    display: flex;
    gap: 20px;
    margin-top: 15px;
    color: var(--text-color);
    opacity: 0.8;
}

.bookmark-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-color);
    opacity: 0.7;
    grid-column: 1 / -1;
}

.bookmark-empty i {
    font-size: 64px;
    color: var(--nav-hover);
    margin-bottom: 20px;
}

.bookmark-actions {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    gap: 10px;
    z-index: 2;
}

.bookmark-btn {
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.bookmark-btn:hover {
    background: var(--minor-orange);
    transform: scale(1.1);
}

.bookmark-btn.bookmarked {
    background: var(--minor-orange);
    color: white;
}

.bookmark-date {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
}

.post {
    position: relative;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    margin: 20px;
    flex-wrap: wrap;
}

.filter-btn {
    background: var(--nav-color);
    color: var(--text-color);
    border: 1px solid var(--primary-blue);
    padding: 8px 20px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn.active {
    background: var(--primary-blue);
    color: white;
}

.filter-btn:hover {
    background: var(--nav-hover);
}
</style>

<!-- HEADER -->
<div class="bookmark-header">
    <h1><i class="fas fa-bookmark"></i> My Bookmarks</h1>
    <p>All content you've saved for later</p>
    
    <div class="bookmark-stats">
        <div>
            <strong><?php echo $bookmark_count; ?></strong> bookmarked content
        </div>
        <div>
            <i class="fas fa-clock"></i> Last updated: <?php echo date('F j, Y'); ?>
        </div>
    </div>
</div>

<!-- FILTER BUTTONS -->
<div class="filter-buttons">
    <button class="filter-btn active" data-filter="all">All</button>
    <button class="filter-btn" data-filter="video">Videos</button>
    <button class="filter-btn" data-filter="image">Images</button>
    <button class="filter-btn" data-filter="document">Documents</button>
    <button class="filter-btn" data-filter="quiz">With Quiz</button>
</div>

<!-- BOOKMARKED CONTENT -->
<div class="content">
    <?php if ($bookmark_count > 0): ?>
        <?php mysqli_data_seek($bookmark_result, 0); ?>
        <?php while($row = mysqli_fetch_assoc($bookmark_result)): ?>
            <?php 
            // Tambahkan data untuk post_card.php
            $row['username'] = $row['content_author'];
            $row['bookmarked'] = true;
            $row['bookmark_id'] = $row['id']; // ID bookmark
            $row['bookmark_date'] = $row['created_at'];
            
            // Cek apakah konten ini punya quiz
            $quiz_check = "SELECT COUNT(*) as quiz_count FROM quiz_questions WHERE content_id = ?";
            $quiz_stmt = mysqli_prepare($conn, $quiz_check);
            mysqli_stmt_bind_param($quiz_stmt, "i", $row['content_id']);
            mysqli_stmt_execute($quiz_stmt);
            $quiz_check_result = mysqli_stmt_get_result($quiz_stmt);
            $quiz_data = mysqli_fetch_assoc($quiz_check_result);
            $row['has_quiz'] = ($quiz_data['quiz_count'] > 0);
            ?>
            
            <div class="post-container" 
                 data-type="<?php echo $row['file_type']; ?>"
                 data-quiz="<?php echo $row['has_quiz'] ? 'yes' : 'no'; ?>">
                <?php include('popups/post_card.php'); ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="bookmark-empty">
            <i class="fas fa-bookmark"></i>
            <h2>No Bookmarks Yet</h2>
            <p>You haven't bookmarked any content yet. Start exploring and save content you like!</p>
            <button class="buttons" onclick="window.location.href='dashboard.php'">
                <i class="fas fa-compass"></i> Explore Content
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        
        // Add active class to clicked button
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        const containers = document.querySelectorAll('.post-container');
        
        containers.forEach(container => {
            const type = container.getAttribute('data-type');
            const hasQuiz = container.getAttribute('data-quiz');
            
            let show = false;
            
            switch(filter) {
                case 'all':
                    show = true;
                    break;
                case 'video':
                    show = type === 'video';
                    break;
                case 'image':
                    show = type === 'image';
                    break;
                case 'document':
                    show = type === 'document';
                    break;
                case 'quiz':
                    show = hasQuiz === 'yes';
                    break;
            }
            
            container.style.display = show ? 'block' : 'none';
        });
    });
});

// Bookmark functionality (remove bookmark)
function removeBookmark(bookmarkId, contentId, element) {
    if (confirm('Remove this content from bookmarks?')) {
        fetch('bookmark_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                bookmark_id: bookmarkId,
                content_id: contentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the post from view
                const postContainer = element.closest('.post-container');
                if (postContainer) {
                    postContainer.remove();
                }
                
                // Update bookmark count
                const bookmarkCount = document.querySelector('.bookmark-stats strong');
                if (bookmarkCount) {
                    const currentCount = parseInt(bookmarkCount.textContent);
                    bookmarkCount.textContent = currentCount - 1;
                }
                
                // Show message if no bookmarks left
                const containers = document.querySelectorAll('.post-container');
                if (containers.length === 0) {
                    location.reload(); // Reload to show empty state
                }
                
            } else {
                alert('Failed to remove bookmark: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}
</script>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
<script src="js/script.js"></script>
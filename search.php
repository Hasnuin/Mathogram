<?php
// search.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<style>
.search-header {
    padding: 20px;
    background: var(--nav-color);
    border-radius: 15px;
    margin: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.search-stats {
    color: var(--text-color);
    opacity: 0.8;
    font-size: 14px;
    margin-top: 10px;
}

.search-tabs {
    display: flex;
    gap: 10px;
    margin: 20px;
    border-bottom: 2px solid var(--nav-hover);
    padding-bottom: 10px;
}

.search-tab {
    padding: 10px 20px;
    background: none;
    border: none;
    color: var(--text-color);
    cursor: pointer;
    border-radius: 10px 10px 0 0;
    font-weight: bold;
    transition: all 0.3s;
}

.search-tab.active {
    background: var(--primary-blue);
    color: white;
}

.search-tab:hover:not(.active) {
    background: var(--nav-hover);
}

.search-results-container {
    padding: 20px;
}

.search-result-section {
    margin-bottom: 40px;
}

.section-title {
    color: var(--text-color);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-blue);
}

.search-empty {
    text-align: center;
    padding: 50px 20px;
    color: var(--text-color);
    opacity: 0.7;
}

.search-empty i {
    font-size: 48px;
    margin-bottom: 20px;
    color: var(--nav-hover);
}

.pagination {
    display: flex;
    justify-content: center;
    margin: 30px 0;
    gap: 10px;
}

.pagination a, .pagination span {
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-color);
    background: var(--nav-color);
    border: 1px solid var(--nav-hover);
}

.pagination a:hover {
    background: var(--primary-blue);
    color: white;
}

.pagination .current {
    background: var(--primary-blue);
    color: white;
    font-weight: bold;
}

.search-highlight {
    background: yellow;
    color: black;
    padding: 0 2px;
    border-radius: 3px;
}
</style>

<div class="search-header">
    <h2 style="color: var(--text-color); margin-bottom: 10px;">
        <i class="fas fa-search"></i> Search Results
    </h2>
    
    <!-- Search Box di Header -->
    <div style="display: flex; gap: 10px; margin-top: 20px;">
        <form action="search.php" method="GET" style="flex: 1;">
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" 
                       class="search-input" 
                       name="q" 
                       placeholder="Search content, users, categories..."
                       value="<?php echo htmlspecialchars($query); ?>"
                       style="width: 100%;">
            </div>
        </form>
        <button class="buttons" onclick="document.querySelector('form').submit()">
            <i class="fas fa-search"></i> Search
        </button>
    </div>
    
    <?php if (!empty($query)): ?>
        <div class="search-stats">
            Showing results for: <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($query)): ?>
    <div class="search-tabs" id="searchTabs">
        <button class="search-tab active" data-tab="all">All Results</button>
        <button class="search-tab" data-tab="contents">Contents</button>
        <button class="search-tab" data-tab="users">Users</button>
        <button class="search-tab" data-tab="categories">Categories</button>
    </div>

    <div class="search-results-container">
        <?php
        // ============================================
        // SEARCH CONTENTS
        // ============================================
        $content_query = "SELECT SQL_CALC_FOUND_ROWS c.*, u.username
                          FROM contents c
                          JOIN users u ON c.user_id = u.id
                          WHERE c.status = 'published'
                            AND (c.title LIKE ? 
                                 OR c.description LIKE ? 
                                 OR c.category LIKE ?)
                          ORDER BY c.created_at DESC
                          LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($conn, $content_query);
        $search_term = "%{$query}%";
        mysqli_stmt_bind_param($stmt, "sssii", $search_term, $search_term, $search_term, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $contents_result = mysqli_stmt_get_result($stmt);
        
        // Get total rows
        $total_rows_result = mysqli_query($conn, "SELECT FOUND_ROWS()");
        $total_rows = mysqli_fetch_array($total_rows_result)[0];
        $total_pages = ceil($total_rows / $limit);
        ?>
        
        <!-- All Results Tab -->
        <div class="search-result-section" id="tab-all" style="display: block;">
            <h3 class="section-title">Contents (<?php echo $total_rows; ?>)</h3>
            
            <?php if (mysqli_num_rows($contents_result) > 0): ?>
                <div class="content">
                    <?php while($row = mysqli_fetch_assoc($contents_result)): ?>
                        <?php 
                        // Highlight search term in title
                        $highlighted_title = preg_replace(
                            "/(" . preg_quote($query, '/') . ")/i",
                            '<span class="search-highlight">$1</span>',
                            htmlspecialchars($row['title'])
                        );
                        $row['title'] = $highlighted_title;
                        ?>
                        <?php include('popups/post_card.php'); ?>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page-1; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" 
                               class="<?php echo $i == $page ? 'current' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page+1; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="search-empty">
                    <i class="fas fa-file-alt"></i>
                    <h3>No contents found</h3>
                    <p>Try different keywords or browse all contents</p>
                </div>
            <?php endif; ?>
            
            <!-- Users Section -->
            <?php
            $user_query = "SELECT id, username, email, profile_image
                           FROM users
                           WHERE username LIKE ? OR email LIKE ?
                           LIMIT 6";
            
            $stmt = mysqli_prepare($conn, $user_query);
            mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
            mysqli_stmt_execute($stmt);
            $users_result = mysqli_stmt_get_result($stmt);
            ?>
            
            <h3 class="section-title" style="margin-top: 40px;">Users</h3>
            
            <?php if (mysqli_num_rows($users_result) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding: 20px;">
                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                        <div class="user-card" onclick="window.location.href='user_profile.php?id=<?php echo $user['id']; ?>'"
                             style="background: var(--nav-color); padding: 15px; border-radius: 10px; cursor: pointer; text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto 10px;">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($user['username']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle" style="font-size: 60px; color: var(--primary-blue);"></i>
                                <?php endif; ?>
                            </div>
                            <h4 style="margin: 0; color: var(--text-color);">
                                <?php echo preg_replace(
                                    "/(" . preg_quote($query, '/') . ")/i",
                                    '<span class="search-highlight">$1</span>',
                                    htmlspecialchars($user['username'])
                                ); ?>
                            </h4>
                            <small style="color: var(--text-color); opacity: 0.7;"><?php echo htmlspecialchars($user['email']); ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="search-empty" style="padding: 20px;">
                    <i class="fas fa-users"></i>
                    <p>No users found</p>
                </div>
            <?php endif; ?>
            
            <!-- Categories Section -->
            <?php
            $category_query = "SELECT DISTINCT category
                               FROM contents
                               WHERE category LIKE ? AND status = 'published'
                               LIMIT 10";
            
            $stmt = mysqli_prepare($conn, $category_query);
            mysqli_stmt_bind_param($stmt, "s", $search_term);
            mysqli_stmt_execute($stmt);
            $categories_result = mysqli_stmt_get_result($stmt);
            ?>
            
            <h3 class="section-title" style="margin-top: 40px;">Categories</h3>
            
            <?php if (mysqli_num_rows($categories_result) > 0): ?>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; padding: 20px;">
                    <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                        <button class="buttons" 
                                onclick="window.location.href='category.php?name=<?php echo urlencode($category['category']); ?>'"
                                style="font-size: 14px; padding: 8px 15px;">
                            <?php echo preg_replace(
                                "/(" . preg_quote($query, '/') . ")/i",
                                '<span class="search-highlight">$1</span>',
                                htmlspecialchars($category['category'])
                            ); ?>
                        </button>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="search-empty" style="padding: 20px;">
                    <i class="fas fa-tags"></i>
                    <p>No categories found</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Other tabs would be similar but filtered -->
        
    </div>

    <script>
    // Tab switching functionality
    document.querySelectorAll('.search-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.search-tab').forEach(t => {
                t.classList.remove('active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all tab contents
            document.querySelectorAll('[id^="tab-"]').forEach(content => {
                content.style.display = 'none';
            });
            
            // Show selected tab content
            const tabId = this.getAttribute('data-tab');
            document.getElementById('tab-' + tabId).style.display = 'block';
            
            // Update URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        });
    });
    
    // Check URL for tab parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam) {
        const tab = document.querySelector(`.search-tab[data-tab="${tabParam}"]`);
        if (tab) {
            tab.click();
        }
    }
    </script>

<?php else: ?>
    <div class="search-empty">
        <i class="fas fa-search fa-3x"></i>
        <h2>Search Content and Users</h2>
        <p>Enter keywords in the search box above to find content, users, or categories</p>
        
        <div style="margin-top: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
            <h3 style="color: var(--text-color); margin-bottom: 15px;">Popular Categories:</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                <?php
                $popular_categories = ['Algebra', 'Geometry', 'Calculus', 'Statistics', 'Trigonometry', 'Basic Math'];
                foreach ($popular_categories as $category): ?>
                    <a href="category.php?name=<?php echo urlencode($category); ?>" class="buttons">
                        <?php echo htmlspecialchars($category); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
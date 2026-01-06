<?php
// category.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$category_name = isset($_GET['name']) ? urldecode($_GET['name']) : '';

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 

// Query untuk konten dalam kategori
$query = "SELECT c.*, u.username
          FROM contents c
          JOIN users u ON c.user_id = u.id
          WHERE c.category = ? AND c.status = 'published'
          ORDER BY c.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $category_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$count = mysqli_num_rows($result);
?>

<div style="padding: 20px;">
    <h1 style="color: var(--text-color);">
        <i class="fas fa-tag"></i> Category: <?php echo htmlspecialchars($category_name); ?>
    </h1>
    <p style="color: var(--text-color); opacity: 0.8;">
        <?php echo $count; ?> content<?php echo $count != 1 ? 's' : ''; ?> found
    </p>
</div>

<div class="content">
    <?php if ($count > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <?php include('popups/post_card.php'); ?>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; grid-column: 1 / -1;">
            <i class="fas fa-folder-open fa-3x" style="color: var(--nav-hover); margin-bottom: 20px;"></i>
            <h3>No content in this category yet</h3>
            <p>Be the first to upload content in "<?php echo htmlspecialchars($category_name); ?>"</p>
            <button class="buttons" onclick="openModal()">Upload Content</button>
        </div>
    <?php endif; ?>
</div>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
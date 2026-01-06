<?php
// post_card.php - Versi untuk dashboard
$content_id = $row['id'] ?? 0;
$thumbnail = $row['thumbnail_path'] ?? 'default.jpg';
$title = $row['title'] ?? 'Untitled';
$username = $row['username'] ?? 'Anonymous';
$category = $row['category'] ?? 'General';
$description = $row['description'] ?? '';
$file_path = $row['file_path'] ?? '';
$file_type = $row['file_type'] ?? 'image';
$created_at = $row['created_at'] ?? '';


// Format data untuk JavaScript
$js_title = htmlspecialchars($title, ENT_QUOTES);
$js_username = htmlspecialchars($username, ENT_QUOTES);
$js_category = htmlspecialchars($category, ENT_QUOTES);
$js_description = htmlspecialchars($description, ENT_QUOTES);
$js_thumbnail = htmlspecialchars($thumbnail, ENT_QUOTES);
$js_file_path = htmlspecialchars($file_path, ENT_QUOTES);

// Tentukan fungsi onclick
$onclick = "loadContentModal({
    id: $content_id,
    title: '$js_title',
    username: '$js_username',
    category: '$js_category',
    description: '$js_description',
    thumbnail: '$js_thumbnail',
    file_path: '$js_file_path',
    file_type: '$file_type',
    created_at: '$created_at'
})";

// Pastikan path thumbnail benar
if (!empty($thumbnail) && strpos($thumbnail, 'uploads/thumbnails/') === false) {
    $thumbnail_path = 'uploads/thumbnails/' . $thumbnail;
} else {
    $thumbnail_path = $thumbnail ?: 'uploads/thumbnails/default.jpg';
}
?>

<div class="post" onclick="<?php echo $onclick; ?>">
    <div class="image">
        <img src="<?php echo htmlspecialchars($thumbnail_path); ?>" 
             alt="<?php echo htmlspecialchars($title); ?>"
             onerror="this.src='https://via.placeholder.com/400x225/326090/ffffff?text=<?php echo urlencode(substr($title, 0, 20)); ?>'">
    </div>
    <div class="inner">             
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <div class="footer-row">
            <div class="user-info">
                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                <span class="username-text"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <button class="buttons"><?php echo htmlspecialchars($category); ?></button>
        </div>
    </div>
</div>
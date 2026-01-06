<?php
// bookmark_action.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';
$content_id = isset($data['content_id']) ? (int)$data['content_id'] : 0;
$bookmark_id = isset($data['bookmark_id']) ? (int)$data['bookmark_id'] : 0;

if ($content_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid content ID']);
    exit();
}

// Cek apakah konten exists
$content_check = "SELECT id FROM contents WHERE id = ? AND status = 'published'";
$stmt = mysqli_prepare($conn, $content_check);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);
$content_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($content_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Content not found']);
    exit();
}

if ($action === 'add') {
    // Tambah bookmark
    $query = "INSERT INTO bookmarks (user_id, content_id) VALUES (?, ?)
              ON DUPLICATE KEY UPDATE user_id = user_id";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $content_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $bookmark_id = mysqli_insert_id($conn);
        echo json_encode([
            'success' => true, 
            'message' => 'Content bookmarked',
            'bookmark_id' => $bookmark_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to bookmark']);
    }
    
} elseif ($action === 'remove') {
    // Hapus bookmark
    if ($bookmark_id > 0) {
        $query = "DELETE FROM bookmarks WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $bookmark_id, $user_id);
    } else {
        $query = "DELETE FROM bookmarks WHERE user_id = ? AND content_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $content_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Bookmark removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove bookmark']);
    }
    
} elseif ($action === 'check') {
    // Cek status bookmark
    $query = "SELECT id FROM bookmarks WHERE user_id = ? AND content_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $content_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $is_bookmarked = (mysqli_num_rows($result) > 0);
    $bookmark_data = $is_bookmarked ? mysqli_fetch_assoc($result) : null;
    
    echo json_encode([
        'success' => true,
        'is_bookmarked' => $is_bookmarked,
        'bookmark_id' => $bookmark_data['id'] ?? 0
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
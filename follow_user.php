<?php
// follow_user.php (opsional)
session_start();
require_once 'config.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$current_user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

$target_user_id = $data['user_id'] ?? 0;
$action = $data['action'] ?? 'follow';

if ($target_user_id <= 0 || $target_user_id == $current_user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit();
}

// Buat tabel followers jika belum ada
$create_table = "CREATE TABLE IF NOT EXISTS followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id)
)";
mysqli_query($conn, $create_table);

if ($action === 'follow') {
    $query = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)
              ON DUPLICATE KEY UPDATE follower_id = follower_id";
} else {
    $query = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
}

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $target_user_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => ucfirst($action) . ' successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
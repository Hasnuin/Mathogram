<?php
// search_ajax.php
require_once 'config.php';
session_start();

// Set header JSON
header('Content-Type: application/json');

// Ambil query parameter
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Query too short'
    ]);
    exit();
}

$search_results = [];

// ============================================
// 1. SEARCH CONTENTS
// ============================================
$content_query = "SELECT c.id, c.title, c.description, c.category, 
                         u.username, 'content' as type
                  FROM contents c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.status = 'published'
                    AND (c.title LIKE ? 
                         OR c.description LIKE ? 
                         OR c.category LIKE ?)
                  ORDER BY c.created_at DESC
                  LIMIT 10";

$stmt = mysqli_prepare($conn, $content_query);
$search_term = "%{$query}%";
mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
mysqli_stmt_execute($stmt);
$content_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($content_result)) {
    $search_results[] = [
        'id' => $row['id'],
        'type' => 'content',
        'title' => $row['title'],
        'meta' => 'By ' . $row['username'] . ' • ' . $row['category']
    ];
}

// ============================================
// 2. SEARCH USERS
// ============================================
$user_query = "SELECT id, username, email, 'user' as type
               FROM users
               WHERE username LIKE ? OR email LIKE ?
               LIMIT 5";

$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($user_result)) {
    $search_results[] = [
        'id' => $row['id'],
        'type' => 'user',
        'title' => $row['username'],
        'meta' => $row['email']
    ];
}

// ============================================
// 3. SEARCH CATEGORIES
// ============================================
$category_query = "SELECT DISTINCT category as title, 'category' as type
                   FROM contents
                   WHERE category LIKE ? AND status = 'published'
                   LIMIT 5";

$stmt = mysqli_prepare($conn, $category_query);
mysqli_stmt_bind_param($stmt, "s", $search_term);
mysqli_stmt_execute($stmt);
$category_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($category_result)) {
    $search_results[] = [
        'id' => $row['title'], // Untuk kategori, id adalah nama kategori
        'type' => 'category',
        'title' => $row['title'],
        'meta' => 'Category'
    ];
}

// Urutkan berdasarkan relevansi (bisa disesuaikan)
usort($search_results, function($a, $b) use ($query) {
    // Prioritaskan yang judulnya persis sama dengan query
    $a_exact = strtolower($a['title']) === strtolower($query);
    $b_exact = strtolower($b['title']) === strtolower($query);
    
    if ($a_exact && !$b_exact) return -1;
    if (!$a_exact && $b_exact) return 1;
    
    // Prioritaskan content, lalu user, lalu category
    $type_order = ['content' => 1, 'user' => 2, 'category' => 3];
    return $type_order[$a['type']] <=> $type_order[$b['type']];
});

echo json_encode([
    'success' => true,
    'results' => $search_results,
    'count' => count($search_results)
]);
?>
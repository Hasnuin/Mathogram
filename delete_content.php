<?php
// delete_content.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

// Ambil data dari POST
$data = json_decode(file_get_contents('php://input'), true);
$content_id = isset($data['id']) ? (int)$data['id'] : 0;
$user_id = $_SESSION['id'];

$response = ['success' => false, 'message' => ''];

// Validasi
if ($content_id <= 0) {
    $response['message'] = 'Invalid content ID';
    echo json_encode($response);
    exit();
}

// ============================================
// 1. VERIFIKASI KEPEMILIKAN
// ============================================
$check_query = "SELECT * FROM contents WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $content_id, $user_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) == 0) {
    $response['message'] = 'You don\'t have permission to delete this content';
    echo json_encode($response);
    exit();
}

$content = mysqli_fetch_assoc($check_result);

// ============================================
// 2. HAPUS FILE-FILE FISIK
// ============================================
// Hapus thumbnail
if (!empty($content['thumbnail_path']) && file_exists($content['thumbnail_path'])) {
    unlink($content['thumbnail_path']);
}

// Hapus file utama
if (!empty($content['file_path']) && file_exists($content['file_path'])) {
    unlink($content['file_path']);
}

// ============================================
// 3. HAPUS QUIZ DAN FILE TERKAIT
// ============================================
// Ambil semua pertanyaan untuk konten ini
$questions_query = "SELECT id, question_file FROM quiz_questions WHERE content_id = ?";
$stmt = mysqli_prepare($conn, $questions_query);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);
$questions_result = mysqli_stmt_get_result($stmt);

while ($question = mysqli_fetch_assoc($questions_result)) {
    // Hapus file gambar soal
    if (!empty($question['question_file']) && file_exists($question['question_file'])) {
        unlink($question['question_file']);
    }
    
    // Hapus jawaban
    $delete_answers = "DELETE FROM quiz_answers WHERE question_id = ?";
    $stmt2 = mysqli_prepare($conn, $delete_answers);
    mysqli_stmt_bind_param($stmt2, "i", $question['id']);
    mysqli_stmt_execute($stmt2);
}

// Hapus pertanyaan
$delete_questions = "DELETE FROM quiz_questions WHERE content_id = ?";
$stmt = mysqli_prepare($conn, $delete_questions);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);

// ============================================
// 4. HAPUS HASIL QUIZ USER
// ============================================
// Ambil semua quiz_result_id untuk konten ini
$results_query = "SELECT id FROM quiz_results WHERE content_id = ?";
$stmt = mysqli_prepare($conn, $results_query);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);
$results_result = mysqli_stmt_get_result($stmt);

while ($result = mysqli_fetch_assoc($results_result)) {
    // Hapus user_quiz_answers
    $delete_user_answers = "DELETE FROM user_quiz_answers WHERE quiz_result_id = ?";
    $stmt2 = mysqli_prepare($conn, $delete_user_answers);
    mysqli_stmt_bind_param($stmt2, "i", $result['id']);
    mysqli_stmt_execute($stmt2);
}

// Hapus quiz_results
$delete_results = "DELETE FROM quiz_results WHERE content_id = ?";
$stmt = mysqli_prepare($conn, $delete_results);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);

// ============================================
// 5. HAPUS KONTEN DARI DATABASE
// ============================================
$delete_query = "DELETE FROM contents WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($stmt, "ii", $content_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    $response['success'] = true;
    $response['message'] = 'Content deleted successfully';
} else {
    $response['message'] = 'Database error: ' . mysqli_error($conn);
}

echo json_encode($response);
?>
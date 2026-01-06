<?php
// submit_quiz.php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$user_id = (int)$_SESSION['id'];
$content_id = (int)$_POST['content_id'];
$answers = $_POST['answers'] ?? [];

// Validasi
if (!$user_id || !$content_id) {
    die("Invalid data");
}

// ============================================
// 1. HITUNG SKOR
// ============================================
$total_questions = 0;
$correct_answers = 0;
$total_score = 0;

// Ambil semua soal untuk konten ini
$questions_query = "SELECT q.id, q.points 
                    FROM quiz_questions q 
                    WHERE q.content_id = '$content_id'";
$questions_result = mysqli_query($conn, $questions_query);

while ($question = mysqli_fetch_assoc($questions_result)) {
    $question_id = $question['id'];
    $total_questions++;
    
    // Cek apakah user menjawab soal ini
    if (isset($answers[$question_id])) {
        $user_answer_id = (int)$answers[$question_id];
        
        // Cek apakah jawaban benar
        $check_query = "SELECT is_correct FROM quiz_answers 
                        WHERE id = '$user_answer_id' AND question_id = '$question_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if ($check_row = mysqli_fetch_assoc($check_result)) {
            if ($check_row['is_correct'] == 1) {
                $correct_answers++;
                $total_score += $question['points'];
            }
        }
    }
}

// Hitung persentase
$percentage = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;

// ============================================
// 2. SIMPAN HASIL KE DATABASE
// ============================================
$insert_query = "INSERT INTO quiz_results 
                 (user_id, content_id, score, correct_answers, total_questions, finished_at) 
                 VALUES ('$user_id', '$content_id', '$total_score', '$correct_answers', '$total_questions', NOW())";

if (mysqli_query($conn, $insert_query)) {
    $quiz_result_id = mysqli_insert_id($conn);
    
    // ============================================
    // 3. SIMPAN JAWABAN USER
    // ============================================
    foreach ($answers as $question_id => $answer_id) {
        $question_id = (int)$question_id;
        $answer_id = (int)$answer_id;
        
        // Cek apakah jawaban benar
        $is_correct_query = "SELECT is_correct FROM quiz_answers 
                             WHERE id = '$answer_id' AND question_id = '$question_id'";
        $is_correct_result = mysqli_query($conn, $is_correct_query);
        $is_correct = 0;
        
        if ($row = mysqli_fetch_assoc($is_correct_result)) {
            $is_correct = $row['is_correct'];
        }
        
        // Simpan jawaban user
        $answer_query = "INSERT INTO user_quiz_answers 
                         (quiz_result_id, question_id, answer_id, is_correct) 
                         VALUES ('$quiz_result_id', '$question_id', '$answer_id', '$is_correct')";
        mysqli_query($conn, $answer_query);
    }
    
    // ============================================
    // 4. REDIRECT KE HASIL QUIZ
    // ============================================
    header("Location: quiz_result.php?result_id=$quiz_result_id");
    exit();
    
} else {
    echo "Error saving quiz result: " . mysqli_error($conn);
}
?>
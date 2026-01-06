<?php
// update_content.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$user_id = $_SESSION['id'];
$content_id = (int)$_POST['content_id'];

// Validasi
if ($content_id <= 0) {
    die("Invalid content ID");
}

// ============================================
// 1. VERIFIKASI KEPEMILIKAN KONTEN
// ============================================
$check_query = "SELECT * FROM contents WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $content_id, $user_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) == 0) {
    die("You don't have permission to edit this content");
}

$current_content = mysqli_fetch_assoc($check_result);

// ============================================
// 2. PROSES DATA DASAR
// ============================================
$title = mysqli_real_escape_string($conn, $_POST['title']);
$description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
$category = mysqli_real_escape_string($conn, $_POST['category']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

// ============================================
// 3. PROSES THUMBNAIL (JIKA ADA UPLOAD BARU)
// ============================================
$thumbnail_path = $current_content['thumbnail_path'];

if (!empty($_FILES['thumbnail']['name'])) {
    $thumb_file = $_FILES['thumbnail'];
    $thumb_ext = strtolower(pathinfo($thumb_file['name'], PATHINFO_EXTENSION));
    $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($thumb_ext, $allowed_image_ext)) {
        // Hapus thumbnail lama jika ada
        if (!empty($thumbnail_path) && file_exists($thumbnail_path)) {
            unlink($thumbnail_path);
        }
        
        // Upload thumbnail baru
        $thumb_filename = time() . '_' . $user_id . '_thumb.' . $thumb_ext;
        $thumb_absolute_path = 'uploads/thumbnails/' . $thumb_filename;
        
        if (move_uploaded_file($thumb_file['tmp_name'], $thumb_absolute_path)) {
            $thumbnail_path = 'uploads/thumbnails/' . $thumb_filename;
        }
    }
}

// ============================================
// 4. PROSES FILE UTAMA (JIKA ADA UPLOAD BARU)
// ============================================
$file_path = $current_content['file_path'];
$file_type = $current_content['file_type'];

if (!empty($_FILES['main_file']['name'])) {
    $main_file = $_FILES['main_file'];
    $file_ext = strtolower(pathinfo($main_file['name'], PATHINFO_EXTENSION));
    
    // Hapus file lama jika ada
    if (!empty($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Upload file baru
    $file_filename = time() . '_' . $user_id . '_content.' . $file_ext;
    $file_absolute_path = 'uploads/content/' . $file_filename;
    
    if (move_uploaded_file($main_file['tmp_name'], $file_absolute_path)) {
        $file_path = 'uploads/content/' . $file_filename;
        
        // Tentukan tipe file
        $image_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $video_ext = ['mp4', 'webm', 'avi', 'mov', 'mkv'];
        
        if (in_array($file_ext, $image_ext)) {
            $file_type = 'image';
        } elseif (in_array($file_ext, $video_ext)) {
            $file_type = 'video';
        } else {
            $file_type = 'document';
        }
    }
}

// ============================================
// 5. UPDATE DATA KONTEN DI DATABASE
// ============================================
$update_query = "UPDATE contents SET 
                 title = ?, 
                 description = ?, 
                 category = ?, 
                 thumbnail_path = ?, 
                 file_path = ?, 
                 file_type = ?, 
                 status = ?, 
                 updated_at = NOW()
                 WHERE id = ? AND user_id = ?";

$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, "sssssssii", 
    $title, $description, $category, $thumbnail_path, 
    $file_path, $file_type, $status, $content_id, $user_id
);

if (mysqli_stmt_execute($stmt)) {
    // ============================================
    // 6. PROSES QUIZ QUESTIONS
    // ============================================
    if (isset($_POST['question_text']) && is_array($_POST['question_text'])) {
        $question_ids = $_POST['question_id'] ?? [];
        $question_texts = $_POST['question_text'];
        $points = $_POST['points'] ?? [];
        
        foreach ($question_texts as $index => $q_text) {
            if (!empty(trim($q_text))) {
                $q_text = mysqli_real_escape_string($conn, $q_text);
                $question_id = isset($question_ids[$index]) ? (int)$question_ids[$index] : 0;
                $point = isset($points[$index]) ? (int)$points[$index] : 1;
                
                // Proses gambar soal
                $question_file_path = NULL;
                if (!empty($_FILES['question_img']['name'][$index])) {
                    $q_file = $_FILES['question_img'];
                    $q_ext = strtolower(pathinfo($q_file['name'][$index], PATHINFO_EXTENSION));
                    
                    // Hapus file lama jika ada
                    if ($question_id > 0) {
                        $old_file_query = "SELECT question_file FROM quiz_questions WHERE id = ?";
                        $old_stmt = mysqli_prepare($conn, $old_file_query);
                        mysqli_stmt_bind_param($old_stmt, "i", $question_id);
                        mysqli_stmt_execute($old_stmt);
                        $old_result = mysqli_stmt_get_result($old_stmt);
                        if ($old_row = mysqli_fetch_assoc($old_result)) {
                            if (!empty($old_row['question_file']) && file_exists($old_row['question_file'])) {
                                unlink($old_row['question_file']);
                            }
                        }
                    }
                    
                    // Upload file baru
                    $q_filename = time() . '_' . $user_id . '_q' . $index . '.' . $q_ext;
                    $q_absolute_path = 'uploads/questions/' . $q_filename;
                    
                    if (move_uploaded_file($q_file['tmp_name'][$index], $q_absolute_path)) {
                        $question_file_path = 'uploads/questions/' . $q_filename;
                    }
                } elseif ($question_id > 0) {
                    // Jika tidak ada upload baru, pertahankan file lama
                    $old_file_query = "SELECT question_file FROM quiz_questions WHERE id = ?";
                    $old_stmt = mysqli_prepare($conn, $old_file_query);
                    mysqli_stmt_bind_param($old_stmt, "i", $question_id);
                    mysqli_stmt_execute($old_stmt);
                    $old_result = mysqli_stmt_get_result($old_stmt);
                    if ($old_row = mysqli_fetch_assoc($old_result)) {
                        $question_file_path = $old_row['question_file'];
                    }
                }
                
                // Update atau insert pertanyaan
                if ($question_id > 0) {
                    // Update existing question
                    $question_query = "UPDATE quiz_questions SET 
                                       question_text = ?, 
                                       question_file = ?, 
                                       points = ?, 
                                       question_order = ? 
                                       WHERE id = ? AND content_id = ?";
                    $q_stmt = mysqli_prepare($conn, $question_query);
                    mysqli_stmt_bind_param($q_stmt, "ssiiii", $q_text, $question_file_path, $point, $index, $question_id, $content_id);
                    mysqli_stmt_execute($q_stmt);
                } else {
                    // Insert new question
                    $question_query = "INSERT INTO quiz_questions (content_id, question_text, question_file, question_order, points) 
                                       VALUES (?, ?, ?, ?, ?)";
                    $q_stmt = mysqli_prepare($conn, $question_query);
                    mysqli_stmt_bind_param($q_stmt, "issii", $content_id, $q_text, $question_file_path, $index, $point);
                    mysqli_stmt_execute($q_stmt);
                    $question_id = mysqli_insert_id($conn);
                }
                
                // ============================================
                // 7. PROSES JAWABAN
                // ============================================
                if (isset($_POST['answers'][$index]) && is_array($_POST['answers'][$index])) {
                    $answer_ids = $_POST['answer_id'][$index] ?? [];
                    $answers = $_POST['answers'][$index];
                    $correct_index = isset($_POST['correct_answer'][$index]) ? (int)$_POST['correct_answer'][$index] : 0;
                    
                    foreach ($answers as $ans_index => $answer_text) {
                        if (!empty(trim($answer_text))) {
                            $answer_text = mysqli_real_escape_string($conn, $answer_text);
                            $answer_id = isset($answer_ids[$ans_index]) ? (int)$answer_ids[$ans_index] : 0;
                            $is_correct = ($ans_index == $correct_index) ? 1 : 0;
                            
                            if ($answer_id > 0) {
                                // Update existing answer
                                $answer_query = "UPDATE quiz_answers SET 
                                                 answer_text = ?, 
                                                 is_correct = ?, 
                                                 answer_order = ? 
                                                 WHERE id = ? AND question_id = ?";
                                $a_stmt = mysqli_prepare($conn, $answer_query);
                                mysqli_stmt_bind_param($a_stmt, "siiii", $answer_text, $is_correct, $ans_index, $answer_id, $question_id);
                                mysqli_stmt_execute($a_stmt);
                            } else {
                                // Insert new answer
                                $answer_query = "INSERT INTO quiz_answers (question_id, answer_text, is_correct, answer_order) 
                                                 VALUES (?, ?, ?, ?)";
                                $a_stmt = mysqli_prepare($conn, $answer_query);
                                mysqli_stmt_bind_param($a_stmt, "isii", $question_id, $answer_text, $is_correct, $ans_index);
                                mysqli_stmt_execute($a_stmt);
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Redirect ke halaman konten dengan pesan sukses
    header("Location: view_content.php?id=$content_id&mode=edit&success=1");
    exit();
    
} else {
    echo "Error updating content: " . mysqli_error($conn);
}
?>
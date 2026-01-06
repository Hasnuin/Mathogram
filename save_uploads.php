<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();
    $user_id = (int)$_SESSION['id'];
    
    // Validasi
    if (empty($_POST['title'])) die("Judul diperlukan");
    if (empty($_FILES['main_file']['name'])) die("File utama diperlukan");
    
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : NULL;
    
    // ============================================
    // 1. PROSES THUMBNAIL
    // ============================================
    $thumb_db_path = NULL; // Akan disimpan ke database
    if (!empty($_FILES['thumbnail']['name'])) {
        $thumb_original = basename($_FILES['thumbnail']['name']);
        $thumb_filename = time() . '_' . $user_id . '_' . $thumb_original;
        
        // Path absolut untuk upload
        $thumb_absolute_path = THUMBNAIL_PATH . $thumb_filename;
        
        // Path untuk database (relatif)
        $thumb_db_path = THUMBNAIL_DIR_DB . $thumb_filename; // "uploads/thumbnails/filename.jpg"
        
        // Validasi
        if ($_FILES['thumbnail']['size'] > MAX_FILE_SIZE) {
            die("Thumbnail terlalu besar");
        }
        
        // Pindahkan file
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumb_absolute_path)) {
            error_log("Thumbnail disimpan di: " . $thumb_absolute_path);
            error_log("Path di database: " . $thumb_db_path);
        } else {
            die("Gagal upload thumbnail");
        }
    }
    
    // ============================================
    // 2. PROSES FILE UTAMA
    // ============================================
    $file_original = basename($_FILES['main_file']['name']);
    $file_filename = time() . '_' . $user_id . '_' . $file_original;
    
    // Path absolut untuk upload
    $file_absolute_path = CONTENT_PATH . $file_filename;
    
    // Path untuk database (relatif)
    $file_db_path = CONTENT_DIR_DB . $file_filename; // "uploads/content/filename.mp4"
    
    // Validasi
    if ($_FILES['main_file']['size'] > MAX_FILE_SIZE) {
        die("File utama terlalu besar");
    }
    
    // Pindahkan file
    if (!move_uploaded_file($_FILES['main_file']['tmp_name'], $file_absolute_path)) {
        die("Gagal upload file utama");
    }
    
    // Tentukan tipe file
    $file_ext = strtolower(pathinfo($file_original, PATHINFO_EXTENSION));
    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $file_type = 'image';
    } elseif (in_array($file_ext, ['mp4', 'webm', 'avi'])) {
        $file_type = 'video';
    } else {
        $file_type = 'document';
    }
    
    error_log("File utama disimpan di: " . $file_absolute_path);
    error_log("Path di database: " . $file_db_path);
    
    // ============================================
    // 3. SIMPAN KE DATABASE
    // ============================================
    // Periksa apakah tabel punya kolom category
    $has_category = false;
    $result = mysqli_query($conn, "SHOW COLUMNS FROM contents LIKE 'category'");
    if (mysqli_num_rows($result) > 0) {
        $has_category = true;
    }
    
    if ($has_category && $category) {
        $query = "INSERT INTO contents (user_id, title, description, category, thumbnail_path, file_path, file_type, status) 
                  VALUES ('$user_id', '$title', '$description', '$category', '$thumb_db_path', '$file_db_path', '$file_type', 'published')";
    } else {
        $query = "INSERT INTO contents (user_id, title, description, thumbnail_path, file_path, file_type, status) 
                  VALUES ('$user_id', '$title', '$description', '$thumb_db_path', '$file_db_path', '$file_type', 'published')";
    }
    
    if (mysqli_query($conn, $query)) {
        $content_id = mysqli_insert_id($conn);
        error_log("Konten disimpan dengan ID: " . $content_id);
        
        // ============================================
        // 4. PROSES QUIZ (JIKA ADA)
        // ============================================
        if (isset($_POST['question_text']) && is_array($_POST['question_text'])) {
            foreach ($_POST['question_text'] as $index => $q_text) {
                if (!empty(trim($q_text))) {
                    $q_text = mysqli_real_escape_string($conn, $q_text);
                    
                    // Proses gambar soal
                    $question_db_path = NULL;
                    if (!empty($_FILES['question_img']['name'][$index])) {
                        $q_original = basename($_FILES['question_img']['name'][$index]);
                        $q_filename = time() . '_' . $user_id . '_q' . $index . '_' . $q_original;
                        
                        // Path absolut
                        $q_absolute_path = QUESTION_PATH . $q_filename;
                        
                        // Path untuk database
                        $question_db_path = QUESTION_DIR_DB . $q_filename; // "uploads/questions/filename.jpg"
                        
                        move_uploaded_file($_FILES['question_img']['tmp_name'][$index], $q_absolute_path);
                        error_log("Gambar soal disimpan: " . $q_absolute_path);
                    }
                    
                    // Simpan soal
                    $quiz_query = "INSERT INTO quiz_questions (content_id, question_text, question_file, question_order, points) 
                                   VALUES ('$content_id', '$q_text', '$question_db_path', '$index', 1)";
                    
                    if (mysqli_query($conn, $quiz_query)) {
                        $question_id = mysqli_insert_id($conn);
                        
                        // Simpan jawaban
                        if (isset($_POST['answers'][$index]) && is_array($_POST['answers'][$index])) {
                            $correct_index = isset($_POST['correct_answer'][$index]) ? (int)$_POST['correct_answer'][$index] : 0;
                            
                            foreach ($_POST['answers'][$index] as $ans_index => $answer_text) {
                                if (!empty(trim($answer_text))) {
                                    $answer_text = mysqli_real_escape_string($conn, $answer_text);
                                    $is_correct = ($ans_index == $correct_index) ? 1 : 0;
                                    
                                    $answer_query = "INSERT INTO quiz_answers (question_id, answer_text, is_correct, answer_order) 
                                                     VALUES ('$question_id', '$answer_text', '$is_correct', '$ans_index')";
                                    mysqli_query($conn, $answer_query);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // ============================================
        // 5. VERIFIKASI & REDIRECT
        // ============================================
        // Verifikasi data tersimpan
        $verify_query = "SELECT id, user_id, title, file_path, thumbnail_path FROM contents WHERE id = $content_id";
        $verify_result = mysqli_query($conn, $verify_query);
        
        if ($row = mysqli_fetch_assoc($verify_result)) {
            echo "<script>
                    alert('Upload berhasil! File tersimpan di:\\n" . 
                    addslashes($row['file_path']) . "\\n\\n" .
                    "Thumbnail: " . ($row['thumbnail_path'] ? addslashes($row['thumbnail_path']) : 'Tidak ada') . "');
                    
                    // Tampilkan modal sukses
                    if (document.getElementById('success')) {
                        document.getElementById('success').style.display='flex';
                        document.getElementById('overlay').style.display='none';
                    }
                    
                    // Redirect ke profile
                    setTimeout(function() {
                        window.location.href = 'profile.php?status=success&id=$content_id';
                    }, 2000);
                  </script>";
        } else {
            echo "<script>alert('Upload berhasil tapi verifikasi gagal');</script>";
        }
        
    } else {
        echo "Error database: " . mysqli_error($conn);
    }
}
?>
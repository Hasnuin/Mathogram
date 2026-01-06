<?php
// update_profile.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id'])) {
    die("Not logged in");
}

$user_id = $_SESSION['id'];
$response = ['success' => false, 'message' => ''];

// Validasi input
$required_fields = ['username', 'email', 'current_password'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $response['message'] = "Please fill all required fields";
        echo json_encode($response);
        exit();
    }
}

$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$bio = isset($_POST['bio']) ? mysqli_real_escape_string($conn, $_POST['bio']) : NULL;
$birth_date = isset($_POST['birth_date']) ? mysqli_real_escape_string($conn, $_POST['birth_date']) : NULL;
$website = isset($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : NULL;
$current_password = $_POST['current_password'];
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

// ============================================
// 1. VERIFIKASI PASSWORD SAAT INI
// ============================================
$check_query = "SELECT password FROM users WHERE id = '$user_id'";
$check_result = mysqli_query($conn, $check_query);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    $user_data = mysqli_fetch_assoc($check_result);
    
    // Verifikasi password (sesuaikan dengan cara Anda menyimpan password)
    // Jika menggunakan password_hash():
    if (!password_verify($current_password, $user_data['password'])) {
        $response['message'] = "Current password is incorrect";
        echo json_encode($response);
        exit();
    }
} else {
    $response['message'] = "User not found";
    echo json_encode($response);
    exit();
}

// ============================================
// 2. CEK DUPLIKAT USERNAME DAN EMAIL
// ============================================
$duplicate_query = "SELECT id FROM users 
                    WHERE (username = '$username' OR email = '$email') 
                    AND id != '$user_id'";
$duplicate_result = mysqli_query($conn, $duplicate_query);

if (mysqli_num_rows($duplicate_result) > 0) {
    $response['message'] = "Username or email already exists";
    echo json_encode($response);
    exit();
}

// ============================================
// 3. PROSES UPLOAD GAMBAR PROFIL
// ============================================
$profile_image_path = NULL;

if (!empty($_FILES['profile_picture']['name'])) {
    $file_name = $_FILES['profile_picture']['name'];
    $file_tmp = $_FILES['profile_picture']['tmp_name'];
    $file_size = $_FILES['profile_picture']['size'];
    $file_error = $_FILES['profile_picture']['error'];
    
    // Validasi file
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $response['message'] = "Invalid file type. Only JPG, PNG, GIF allowed.";
        echo json_encode($response);
        exit();
    }
    
    if ($file_size > 5 * 1024 * 1024) { // 5MB
        $response['message'] = "File is too large. Maximum size is 5MB.";
        echo json_encode($response);
        exit();
    }
    
    // Buat nama file unik
    $new_filename = time() . '_' . $user_id . '_profile.' . $file_extension;
    $upload_dir = 'uploads/profiles/';
    
    // Pastikan folder exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Hapus gambar profil lama jika ada
    $old_image_query = "SELECT profile_image FROM users WHERE id = '$user_id'";
    $old_image_result = mysqli_query($conn, $old_image_query);
    if ($old_image = mysqli_fetch_assoc($old_image_result)) {
        if (!empty($old_image['profile_image']) && file_exists($upload_dir . $old_image['profile_image'])) {
            unlink($upload_dir . $old_image['profile_image']);
        }
    }
    
    // Upload file baru
    if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
        $profile_image_path = $new_filename;
    } else {
        $response['message'] = "Failed to upload profile picture";
        echo json_encode($response);
        exit();
    }
}

// ============================================
// 4. UPDATE PASSWORD (JIKA ADA)
// ============================================
$password_update = '';
if (!empty($new_password)) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $password_update = ", password = '$hashed_password'";
}

// ============================================
// 5. UPDATE DATABASE
// ============================================
$update_fields = [];
$update_fields[] = "username = '$username'";
$update_fields[] = "email = '$email'";
$update_fields[] = "bio = " . ($bio ? "'$bio'" : "NULL");
$update_fields[] = "birth_date = " . ($birth_date ? "'$birth_date'" : "NULL");
$update_fields[] = "website = " . ($website ? "'$website'" : "NULL");

if ($profile_image_path) {
    $update_fields[] = "profile_image = '$profile_image_path'";
}

$update_fields[] = "updated_at = NOW()";

$update_query = "UPDATE users SET " . implode(', ', $update_fields) . $password_update . " WHERE id = '$user_id'";

if (mysqli_query($conn, $update_query)) {
    // Update session data
    $_SESSION['username'] = $username;
    
    $response['success'] = true;
    $response['message'] = "Profile updated successfully";
    
    // Jika ada gambar baru, berikan path lengkap
    if ($profile_image_path) {
        $response['profile_image'] = $upload_dir . $profile_image_path;
    }
    
} else {
    $response['message'] = "Database error: " . mysqli_error($conn);
}

echo json_encode($response);
?>
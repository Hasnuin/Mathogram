<?php
// get_user_data.php
session_start();
require_once 'config.php';

// Log untuk debugging
error_log("=== GET_USER_DATA.PHP STARTED ===");
error_log("Session ID: " . ($_SESSION['id'] ?? 'NOT SET'));

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    error_log("ERROR: User not logged in");
    echo json_encode([
        'success' => false, 
        'message' => 'Not logged in',
        'debug' => ['session' => $_SESSION]
    ]);
    exit();
}

$user_id = $_SESSION['id'];
error_log("User ID from session: " . $user_id);

// Pastikan user_id valid
if (!is_numeric($user_id) || $user_id <= 0) {
    error_log("ERROR: Invalid user ID: " . $user_id);
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid user ID',
        'debug' => ['user_id' => $user_id]
    ]);
    exit();
}

// Query untuk mengambil data user
$query = "SELECT username, email, profile_image, bio, birth_date, website 
          FROM users 
          WHERE id = ?";

error_log("SQL Query: " . $query);
error_log("Parameters: [" . $user_id . "]");

// Gunakan prepared statement
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    error_log("ERROR: Failed to prepare statement: " . mysqli_error($conn));
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'debug' => ['error' => mysqli_error($conn)]
    ]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

error_log("Query executed. Rows found: " . mysqli_num_rows($result));

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    error_log("User data found: " . print_r($user, true));
    
    // Format data
    $response_data = [
        'success' => true,
        'user' => [
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'bio' => $user['bio'] ?? '',
            'birth_date' => $user['birth_date'] ?? '',
            'website' => $user['website'] ?? '',
            'profile_image' => null
        ]
    ];
    
    // Handle profile image
    if (!empty($user['profile_image'])) {
        // Cek apakah path sudah lengkap
        if (strpos($user['profile_image'], 'uploads/profiles/') === 0) {
            $image_path = $user['profile_image'];
        } else {
            $image_path = 'uploads/profiles/' . $user['profile_image'];
        }
        
        // Cek apakah file benar-benar ada
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $image_path)) {
            $response_data['user']['profile_image'] = $image_path;
            error_log("Profile image found: " . $image_path);
        } else {
            error_log("Profile image file not found: " . $image_path);
            // Tetap set path meski file tidak ada, frontend akan handle error
            $response_data['user']['profile_image'] = $image_path;
        }
    } else {
        error_log("No profile image in database");
    }
    
    error_log("Sending response: " . json_encode($response_data));
    echo json_encode($response_data);
    
} else {
    error_log("ERROR: User not found in database. User ID: " . $user_id);
    echo json_encode([
        'success' => false, 
        'message' => 'User not found in database',
        'debug' => [
            'user_id' => $user_id,
            'query_error' => mysqli_error($conn)
        ]
    ]);
}

mysqli_stmt_close($stmt);
error_log("=== GET_USER_DATA.PHP ENDED ===");
?>
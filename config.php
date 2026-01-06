<?php
// config.php - FIXED PATHS

$host = "localhost";
$user = "root";
$pass = "";
$db   = "mathogram";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// ============================================
// PATH ABSOLUT DI SERVER (Untuk move_uploaded_file)
// ============================================
$root_path = dirname(__FILE__); // Lokasi file config.php

define('ROOT_PATH', $root_path);
define('UPLOAD_BASE_PATH', $root_path . '/uploads/');
define('CONTENT_PATH', UPLOAD_BASE_PATH . 'content/');
define('QUESTION_PATH', UPLOAD_BASE_PATH . 'questions/');
define('THUMBNAIL_PATH', UPLOAD_BASE_PATH . 'thumbnails/');
define('TEMP_PATH', UPLOAD_BASE_PATH . 'temps/');

// ============================================
// PATH RELATIF/URL (Untuk ditampilkan di web)
// ============================================
// Menghitung path relatif dari DOCUMENT_ROOT ke folder project
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$script_dir = dirname($_SERVER['SCRIPT_NAME']);

// Path untuk di database (misal: "uploads/content/filename.jpg")
define('UPLOAD_DIR_DB', 'uploads/');
define('CONTENT_DIR_DB', UPLOAD_DIR_DB . 'content/');
define('QUESTION_DIR_DB', UPLOAD_DIR_DB . 'questions/');
define('THUMBNAIL_DIR_DB', UPLOAD_DIR_DB . 'thumbnails/');

// URL untuk akses file di browser
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . $script_dir;
define('BASE_URL', $base_url);
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('CONTENT_URL', UPLOAD_URL . 'content/');
define('QUESTION_URL', UPLOAD_URL . 'questions/');
define('THUMBNAIL_URL', UPLOAD_URL . 'thumbnails/');

// Debug: Tampilkan semua path
error_log("ROOT_PATH: " . ROOT_PATH);
error_log("CONTENT_PATH (absolut): " . CONTENT_PATH);
error_log("CONTENT_URL (browser): " . CONTENT_URL);

// ============================================
// BUAT FOLDER JIKA BELUM ADA
// ============================================
$folders = [
    UPLOAD_BASE_PATH,
    CONTENT_PATH,
    QUESTION_PATH,
    THUMBNAIL_PATH,
    TEMP_PATH
];

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
        error_log("Created folder: " . $folder);
    }
    
    // Cek permission
    if (!is_writable($folder)) {
        die("Folder tidak bisa ditulis: $folder. Ubah permission ke 755.");
    }
}

// ============================================
// KONFIGURASI FILE
// ============================================
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
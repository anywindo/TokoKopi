<?php
ob_start();
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/debug_log.txt');

try {
    if (!file_exists(__DIR__ . '/koneksi.php')) {
        throw new Exception("koneksi.php not found in " . __DIR__);
    }
    include __DIR__ . '/koneksi.php';

    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Pastikan user sudah login
    if (!isset($_SESSION['role'])) {
        ob_clean();
        echo json_encode(['error' => 'Not logged in']);
        exit();
    }

    $username = $_SESSION['username'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;

    // Tangani request update profil
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name'] ?? '');
        $telp = trim($_POST['telp'] ?? '');
        
        $profile_photo_sql = "";
        $params = [$full_name, $telp];
        $types = "ss";

        // Tangani upload foto profil
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

// Return safe session data
echo json_encode([
    'username' => $_SESSION['username'] ?? 'User',
    'role' => $_SESSION['role'],
    'id_user' => $_SESSION['id_user'] ?? 0
]);
?>

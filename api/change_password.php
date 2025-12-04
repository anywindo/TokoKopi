<?php
session_start();
header('Content-Type: application/json');

// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

try {
    if (!file_exists(__DIR__ . '/koneksi.php')) {
        throw new Exception("koneksi.php not found");
    }
    include __DIR__ . '/koneksi.php';

    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed");
    }

    // Ensure user is logged in
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }

    $username = $_SESSION['username'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("All fields are required");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }

        $row = $result->fetch_assoc();
        if (!password_verify($current_password, $row['password'])) {
            throw new Exception("Incorrect current password");
        }

        // Update password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $new_password_hash, $username);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            throw new Exception("Failed to update password");
        }
    } else {
        throw new Exception("Invalid request method");
    }

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

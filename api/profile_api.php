<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Return safe session data
echo json_encode([
    'username' => $_SESSION['username'] ?? 'User',
    'role' => $_SESSION['role'],
    'id_user' => $_SESSION['user_id'] ?? 0
]);
?>

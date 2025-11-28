<?php
session_start();
include 'koneksi.php';

// Allow id from GET
$id = $_GET['id'] ?? ($_SESSION['id_user'] ?? null);

if (!$id) {
    http_response_code(400);
    exit;
}

$stmt = $conn->prepare("SELECT photo FROM users WHERE id_user=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($blob);
$stmt->fetch();

if ($blob !== null) {
    header("Content-Type: image/jpeg");
    echo $blob;
} else {
    http_response_code(404); // triggers onerror fallback
}

$stmt->close();
$conn->close();

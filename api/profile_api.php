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

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_photo_sql = ", profile_photo = ?";
                    $params[] = $newFileName;
                    $types .= "s";
                } else {
                    throw new Exception("Error moving uploaded file.");
                }
            } else {
                throw new Exception("Invalid file type.");
            }
        }

        $params[] = $username;
        $types .= "s";

        $stmt = $conn->prepare("UPDATE users SET full_name = ?, telp = ?" . $profile_photo_sql . " WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            ob_clean();
            echo json_encode(['success' => true]);
            exit();
        } else {
            throw new Exception("Update failed: " . $stmt->error);
        }
    }

    $branch_name = '';
    $full_name = '';
    $profile_photo = '';
    $telp = '';

    // Ambil data profil user saat ini
    if ($username) {
        $stmt = $conn->prepare("SELECT u.full_name, u.profile_photo, u.telp, b.nama as branch_name FROM users u LEFT JOIN branch b ON u.id_branch = b.id_branch WHERE u.username = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $branch_name = $row['branch_name'];
            $full_name = $row['full_name'];
            $profile_photo = $row['profile_photo'];
            $telp = $row['telp'];
        }
    }

    ob_clean();
    echo json_encode([
        'username' => $username,
        'full_name' => $full_name ?? $username,
        'profile_photo' => $profile_photo,
        'telp' => $telp,
        'role' => $_SESSION['role'],
        'id_user' => $_SESSION['user_id'] ?? 0,
        'branch_name' => $branch_name
    ]);
} catch (Exception $e) {
    error_log("Profile API Error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

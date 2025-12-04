<?php
include 'koneksi.php'; 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $telp = trim($_POST['phone']); 
    $role = $_POST['role'];
    $branch_name = $_POST['branch'] ?? null;

    if ($password !== $password2) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
        exit();
    }

    if ($role === 'manager' && empty($branch_name)) {
        echo json_encode(['success' => false, 'error' => 'Branch must be selected for Managers.']);
        exit();
    }

    // Periksa apakah username sudah ada
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Username already taken.']);
        exit();
    }
    $stmt->close();

    $id_branch = null;
    // Jika role adalah manager, validasi dan ambil ID cabang yang dipilih
    if ($role === 'manager') {
        $stmt = $conn->prepare("SELECT id_branch FROM branch WHERE nama=?");
        $stmt->bind_param("s", $branch_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid branch selected.']);
            exit();
        }
        $row = $result->fetch_assoc();
        $id_branch = $row['id_branch'];
        $stmt->close();
    }

    // Tangani upload foto profil jika ada
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
        $fileName = $_FILES['profile_photo']['name'];
        $fileSize = $_FILES['profile_photo']['size'];
        $fileType = $_FILES['profile_photo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $profile_photo = $newFileName;
            } else {
                echo json_encode(['success' => false, 'error' => 'There was some error moving the file to upload directory.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions)]);
            exit();
        }
    }

    // Hash password untuk keamanan
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Masukkan user baru ke database
    $stmt = $conn->prepare(
        "INSERT INTO users (username, full_name, password, role, telp, id_branch, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssis", $username, $full_name, $password_hash, $role, $telp, $id_branch, $profile_photo);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database insert failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
include 'koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $telp = trim($_POST['phone']); 
    $role = $_POST['role'];
    $branch_name = $_POST['branch'] ?? null;

    if ($password !== $password2) {
        die("Passwords do not match.");
    }

    if ($role === 'manager' && empty($branch_name)) {
        die("Branch must be selected for Managers.");
    }

    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        die("Username already taken.");
    }
    $stmt->close();

    $id_branch = null;
    if ($role === 'manager') {
        $stmt = $conn->prepare("SELECT id_branch FROM branch WHERE nama=?");
        $stmt->bind_param("s", $branch_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            die("Invalid branch selected.");
        }
        $row = $result->fetch_assoc();
        $id_branch = $row['id_branch'];
        $stmt->close();
    }

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
                die("There was some error moving the file to upload directory.");
            }
        } else {
            die("Upload failed. Allowed file types: " . implode(',', $allowedfileExtensions));
        }
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, full_name, password, role, telp, id_branch, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssis", $username, $full_name, $password_hash, $role, $telp, $id_branch, $profile_photo);

    if ($stmt->execute()) {
        header("Location: ../index.xhtml");
    } else {
        die("Database insert failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>

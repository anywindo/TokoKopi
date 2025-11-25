<?php
include 'koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $telp = trim($_POST['phone']); 
    $role = $_POST['role'];
    $branch_name = $_POST['branch'];

    if ($password !== $password2) {
        die("Passwords do not match.");
    }

    if (empty($branch_name)) {
        die("Branch must be selected.");
    }

    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        die("Username already taken.");
    }
    $stmt->close();

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

    // hash password
    $password_hash = password_hash($password, PASSWORD_ARGON2ID);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, telp, id_branch) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssi", $username, $password_hash, $role, $telp, $id_branch);

    if ($stmt->execute()) {
        header("Location: ../frontend/registerSukses.xhtml");
    } else {
        die("Database insert failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
include 'koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("empty username or password.");
    }

    $stmt = $conn->prepare("SELECT role, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("no user with this username.");
    }else{
        $row = $result->fetch_assoc(); // fetch the row once
        $actualPassword = $row['password'];
        $role = $row['role'];
    }
    $stmt->close();

    if (password_verify($password, $actualPassword)) {
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        if ($role === 'corporate') {
            header("Location: ../views/corporate.xhtml");
        } else { 
            header("Location: ../views/manager.xhtml");
        }
        exit();
    } else {
        echo "invalid password.";
    }

    $conn->close();
}
?>

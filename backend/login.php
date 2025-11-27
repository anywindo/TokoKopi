<?php
session_start();
include 'koneksi.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("empty username or password.");
    }

    $stmt = $conn->prepare("SELECT id_user, role, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("no user with this username.");
    }else{
        $row = $result->fetch_assoc(); // fetch the row once
        $actualPassword = $row['password'];
        $role = $row['role'];
        $id_user = $row['id_user'];
    }
    $stmt->close();

    if (password_verify($password, $actualPassword)) {
        // Set session variables
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['id_user'] = $id_user;

        if($role === "manager") {
            header("Location: ../frontend/manager.xhtml");
        } else if($role === "corporate"){
            header("Location: ../frontend/corporate.xhtml");
        }else{
            echo "error role." + $role;
        }
        exit();
    } else {
        echo "invalid password.";
    }

    $conn->close();
}
?>

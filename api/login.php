<?php
include 'koneksi.php'; 

// Periksa apakah metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("empty username or password.");
    }

    // Admin Login
    if ($username === 'admin' && $password === 'admin123') {
        session_start();
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['user_id'] = 0; // ID dummy
        header("Location: ../views/admin.xhtml");
        exit();
    }

    // Siapkan statement SQL untuk mengambil role user dan hash password
    $stmt = $conn->prepare("SELECT role, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("no user with this username.");
    }else{
        $row = $result->fetch_assoc();
        $actualPassword = $row['password'];
        $role = $row['role'];
    }
    $stmt->close();

    // Verifikasi password yang dikirim dengan hash yang tersimpan
    if (password_verify($password, $actualPassword)) {
        // Mulai sesi dan simpan data user
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        // Redirect berdasarkan role user
        if ($role === 'corporate') {
            header("Location: ../views/corporate.xhtml");
        } else if ($role === 'admin') {
            header("Location: ../views/admin.xhtml");
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

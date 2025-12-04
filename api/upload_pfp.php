<?php
include 'koneksi.php';

// Get username
$id_user = $_POST['id_user'];

// Get file binary
$imgData = file_get_contents($_FILES['photo']['tmp_name']);
$imgData = mysqli_real_escape_string($conn, $imgData);

$sql = "UPDATE users SET photo='$imgData' WHERE id_user='$id_user'";
mysqli_query($conn, $sql);

header("Location: ../views/profile.xhtml");
exit();
?>

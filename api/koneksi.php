<?php
$conn = mysqli_connect("localhost", "root", "", "pwd_coffeeshop");

if (!$conn) {
    die("Gagal koneksi: " . mysqli_connect_error());
}
?>

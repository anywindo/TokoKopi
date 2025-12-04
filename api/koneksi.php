<?php
// Membuat koneksi ke database MySQL
// Host: localhost, User: root, Password: (kosong), Database: pwd_coffeeshop
$conn = mysqli_connect("localhost", "root", "", "pwd_coffeeshop");

if (!$conn) {
    die("Gagal koneksi: " . mysqli_connect_error());
}

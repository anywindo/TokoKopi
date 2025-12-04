<?php
// Mulai sesi untuk mengakses data sesi saat ini
session_start();
// Hapus semua data sesi untuk logout user
session_destroy();
header('Location: ../index.xhtml');
exit();
?>

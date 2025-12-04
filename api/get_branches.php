<?php
include 'koneksi.php';

// Ambil semua nama cabang untuk pilihan dropdown
$sql = "SELECT nama FROM branch";
$result = $conn->query($sql);

$branches = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row['nama'];
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($branches);
?>

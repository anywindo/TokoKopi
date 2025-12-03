<?php
include 'koneksi.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id_branch, id_user FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_res = $stmt->get_result()->fetch_assoc();
$my_branch_id = $user_res['id_branch'];
$my_user_id = $user_res['id_user'];

if (!$my_branch_id) {
    echo json_encode(['error' => 'No branch assigned']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_history':
        getHistory($conn, $my_branch_id);
        break;
    case 'add_revenue':
        addRevenue($conn, $my_branch_id, $my_user_id);
        break;
    case 'add_stock':
        addStock($conn, $my_branch_id, $my_user_id);
        break;
    case 'delete_report':
        deleteReport($conn, $my_branch_id);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getHistory($conn, $branch_id) {
    $data = [];
    
    $rev_start = $_GET['rev_start'] ?? '';
    $rev_end = $_GET['rev_end'] ?? '';
    
    $stock_start = $_GET['stock_start'] ?? '';
    $stock_end = $_GET['stock_end'] ?? '';

    $sql = "SELECT * FROM omzet WHERE id_branch = ?";
    $params = [$branch_id];
    $types = "i";

    if ($rev_start !== '') {
        $sql .= " AND tanggal >= ?";
        $params[] = $rev_start;
        $types .= "s";
    }
    if ($rev_end !== '') {
        $sql .= " AND tanggal <= ?";
        $params[] = $rev_end;
        $types .= "s";
    }
    $sql .= " ORDER BY tanggal DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $revenue = [];
    while($row = $res->fetch_assoc()) {
        $revenue[] = $row;
    }
    $data['revenue'] = $revenue;

    $sql = "SELECT * FROM pemakaian WHERE id_branch = ?";
    $params = [$branch_id];
    $types = "i";

    if ($stock_start !== '') {
        $sql .= " AND tanggal >= ?";
        $params[] = $stock_start;
        $types .= "s";
    }
    if ($stock_end !== '') {
        $sql .= " AND tanggal <= ?";
        $params[] = $stock_end;
        $types .= "s";
    }
    $sql .= " ORDER BY tanggal DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $stock = [];
    while($row = $res->fetch_assoc()) {
        $stock[] = $row;
    }
    $data['stock'] = $stock;

    echo json_encode($data);
}

function addRevenue($conn, $branch_id, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $tanggal = $data['tanggal'];
    $omzet = $data['omzet'];

    $stmt = $conn->prepare("INSERT INTO omzet (id_pelapor, id_branch, tanggal, omzet) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $user_id, $branch_id, $tanggal, $omzet);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function addStock($conn, $branch_id, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $tanggal = $data['tanggal'];
    $arabica = $data['arabica'];
    $robusta = $data['robusta'];
    $liberica = $data['liberica'];
    $decaf = $data['decaf'];
    $susu = $data['susu'];

    $stmt = $conn->prepare("INSERT INTO pemakaian (id_pelapor, id_branch, tanggal, arabica, robusta, liberica, decaf, susu) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisddddd", $user_id, $branch_id, $tanggal, $arabica, $robusta, $liberica, $decaf, $susu);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function deleteReport($conn, $branch_id) {
    $type = $_POST['type'];
    $id = $_POST['id'];

    if ($type === 'revenue') {
        $stmt = $conn->prepare("DELETE FROM omzet WHERE id_laporan = ? AND id_branch = ?");
    } else {
        $stmt = $conn->prepare("DELETE FROM pemakaian WHERE id_laporan = ? AND id_branch = ?");
    }
    
    $stmt->bind_param("ii", $id, $branch_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

$conn->close();
?>

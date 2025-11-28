<?php
include 'koneksi.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_stats':
        getStats($conn);
        break;
    case 'get_users':
        getUsers($conn);
        break;
    case 'add_user':
        addUser($conn);
        break;
    case 'delete_user':
        deleteUser($conn);
        break;
    case 'get_branches':
        getBranches($conn);
        break;
    case 'add_branch':
        addBranch($conn);
        break;
    case 'delete_branch':
        deleteBranch($conn);
        break;
    case 'check_session':
        checkSession();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function checkSession() {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'corporate') {
        echo json_encode(['authenticated' => false]);
    } else {
        echo json_encode(['authenticated' => true, 'username' => $_SESSION['username']]);
    }
}

function getStats($conn) {
    $stats = [];
    
    // Top Sales (Highest Omzet)
    $sql = "SELECT MAX(omzet) as max_omzet FROM omzet";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats['top_sales'] = $row['max_omzet'] ? 'Rp ' . number_format($row['max_omzet'], 0, ',', '.') : 'Rp 0';

    // Top Branch (Most Revenue)
    $sql = "SELECT b.nama, SUM(o.omzet) as total_omzet 
            FROM omzet o 
            JOIN branch b ON o.id_branch = b.id_branch 
            GROUP BY o.id_branch 
            ORDER BY total_omzet DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['top_branch'] = $row['nama'];
    } else {
        $stats['top_branch'] = '-';
    }

    // Top Product (Highest Usage - Simplified logic: max of any bean type)
    // This is a bit complex with current schema, simplifying to just finding max of one type for demo
    $sql = "SELECT SUM(arabica) as arabica, SUM(robusta) as robusta, SUM(liberica) as liberica 
            FROM pemakaian";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_val = 0;
    $top_prod = '-';
    if ($row) {
        foreach ($row as $key => $val) {
            if ($val > $max_val) {
                $max_val = $val;
                $top_prod = ucfirst($key);
            }
        }
    }
    $stats['top_product'] = $top_prod;

    echo json_encode($stats);
}

function getUsers($conn) {
    $sql = "SELECT u.id_user, u.username, u.role, b.nama as branch_name 
            FROM users u 
            LEFT JOIN branch b ON u.id_branch = b.id_branch";
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
}

function addUser($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_ARGON2ID);
    $role = $data['role'];
    $branch_id = $data['branch_id']; // Assuming ID is passed, or name if logic adapted

    // Simple insert (needs validation in real app)
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, id_branch) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $password, $role, $branch_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function deleteUser($conn) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function getBranches($conn) {
    $sql = "SELECT * FROM branch";
    $result = $conn->query($sql);
    $branches = [];
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
    echo json_encode($branches);
}

function addBranch($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $nama = $data['nama'];
    $alamat = $data['alamat'];

    $stmt = $conn->prepare("INSERT INTO branch (nama, alamat) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $alamat);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function deleteBranch($conn) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM branch WHERE id_branch = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

$conn->close();
?>

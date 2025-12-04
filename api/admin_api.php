<?php
include 'koneksi.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Tangani aksi yang berbeda berdasarkan parameter 'action'
switch ($action) {
    case 'update_user':
        updateUser($conn);
        break;
    case 'update_branch':
        updateBranch($conn);
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

// Fungsi untuk memeriksa apakah user terautentikasi sebagai corporate
function checkSession() {
    session_start();
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'corporate' && $_SESSION['role'] !== 'admin')) {
        echo json_encode(['authenticated' => false]);
    } else {
        echo json_encode(['authenticated' => true, 'username' => $_SESSION['username']]);
    }
}

function updateUser($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id_user'];
    $username = $data['username'];
    $role = $data['role'];
    $branch_id = $data['branch_id'] ?: null; // Handle empty/null
    
    $sql = "UPDATE users SET username = ?, role = ?, id_branch = ?";
    $params = [$username, $role, $branch_id];
    $types = "ssi";

    if (!empty($data['password'])) {
        $sql .= ", password = ?";
        $params[] = password_hash($data['password'], PASSWORD_ARGON2ID);
        $types .= "s";
    }

    $sql .= " WHERE id_user = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function updateBranch($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id_branch'];
    $nama = $data['nama'];
    $alamat = $data['alamat'];

    $stmt = $conn->prepare("UPDATE branch SET nama = ?, alamat = ? WHERE id_branch = ?");
    $stmt->bind_param("ssi", $nama, $alamat, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

// Fungsi untuk mengambil semua user
function getUsers($conn) {
    $sql = "SELECT u.id_user, u.username, u.role, u.id_branch, b.nama as branch_name 
            FROM users u 
            LEFT JOIN branch b ON u.id_branch = b.id_branch";
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
}

// Fungsi untuk menambahkan user baru
// Fungsi untuk menambahkan user baru
function addUser($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';
    $branch_id = !empty($data['branch_id']) ? $data['branch_id'] : null;

    if (empty($username) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, id_branch) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $password_hash, $role, $branch_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

// Fungsi untuk menghapus user
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

// Fungsi untuk mengambil semua cabang
function getBranches($conn) {
    $sql = "SELECT * FROM branch";
    $result = $conn->query($sql);
    $branches = [];
    while ($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
    echo json_encode($branches);
}

// Fungsi untuk menambahkan cabang baru
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

// Fungsi untuk menghapus cabang
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

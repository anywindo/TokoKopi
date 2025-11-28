<?php
include 'koneksi.php';
session_start();

header('Content-Type: application/json');

// AUTH
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'corporate') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    case 'get_dashboard_data':
        getDashboardData($conn);
        break;

    case 'get_revenue_history':
        getRevenueHistory($conn);
        break;

    case 'get_stock_history':
        getStockHistory($conn);
        break;

    case 'get_all_omzet':
        getAllOmzet($conn);
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

    case 'update_branch':
        updateBranch($conn);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}



// ==========================
// DASHBOARD
// ==========================
function getDashboardData($conn) {
    $data = [];

    // CHART OMZET 7 HARI
    $sql = "SELECT tanggal, SUM(omzet) AS total 
            FROM omzet 
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY tanggal
            ORDER BY tanggal ASC";
    $result = $conn->query($sql);

    $revenue = [];
    while ($row = $result->fetch_assoc()) {
        $revenue[] = $row;
    }
    $data['revenue_chart'] = $revenue;

    // RATA-RATA PEMAKAIAN 7 HARI
    $sql = "SELECT 
                AVG(arabica) AS arabica,
                AVG(robusta) AS robusta,
                AVG(liberica) AS liberica,
                AVG(decaf) AS decaf,
                AVG(susu) AS susu
            FROM pemakaian
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $result = $conn->query($sql);
    $data['stock_avg'] = $result->fetch_assoc();

    // TOTAL OMZET PER BRANCH
    $sql = "SELECT b.nama, SUM(o.omzet) AS total
            FROM omzet o
            JOIN branch b ON o.id_branch = b.id_branch
            WHERE o.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY o.id_branch";
    $result = $conn->query($sql);

    $branchRev = [];
    while ($row = $result->fetch_assoc()) {
        $branchRev[] = $row;
    }
    $data['branch_revenue'] = $branchRev;

    echo json_encode($data);
}



// ==========================
// DETAIL OMZET
// ==========================
function getRevenueHistory($conn) {
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $branch = isset($_GET['branch']) ? $_GET['branch'] : '';

    $sql = "SELECT 
                o.id_laporan,
                o.tanggal,
                o.omzet,
                u.username AS pelapor,
                b.nama AS branch_name
            FROM omzet o
            LEFT JOIN users u ON o.id_pelapor = u.id_user
            LEFT JOIN branch b ON o.id_branch = b.id_branch
            WHERE 1=1";

    if ($date !== '') {
        $sql .= " AND o.tanggal = '" . $conn->real_escape_string($date) . "'";
    }

    if ($branch !== '') {
        $sql .= " AND o.id_branch = " . intval($branch);
    }

    $sql .= " ORDER BY o.tanggal DESC";

    $result = $conn->query($sql);
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
}

function getAllOmzet($conn) {
    $sql = "SELECT 
                o.id_laporan,
                o.tanggal,
                o.omzet,
                u.username AS pelapor,
                b.nama AS branch_name
            FROM omzet o
            LEFT JOIN users u ON o.id_pelapor = u.id_user
            LEFT JOIN branch b ON o.id_branch = b.id_branch
            ORDER BY o.tanggal DESC";

    $result = $conn->query($sql);
    $rows = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    echo json_encode($rows);
}



// ==========================
// DETAIL PEMAKAIAN
// ==========================
function getStockHistory($conn) {
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $branch = isset($_GET['branch']) ? $_GET['branch'] : '';

    $sql = "SELECT 
                p.*,
                b.nama AS branch_name,
                u.username AS pelapor
            FROM pemakaian p
            LEFT JOIN branch b ON p.id_branch = b.id_branch
            LEFT JOIN users u ON p.id_pelapor = u.id_user
            WHERE 1=1";

    if ($date !== '') {
        $sql .= " AND p.tanggal = '" . $conn->real_escape_string($date) . "'";
    }

    if ($branch !== '') {
        $sql .= " AND p.id_branch = " . intval($branch);
    }

    $sql .= " ORDER BY p.tanggal DESC";

    $result = $conn->query($sql);
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
}



// ==========================
// BRANCH
// ==========================
function getBranches($conn) {
    $res = $conn->query("SELECT * FROM branch ORDER BY id_branch ASC");

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }

    echo json_encode($rows);
}



// ADD BRANCH
function addBranch($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        return;
    }

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
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        return;
    }

    $id = intval($data['id']);

    $check = $conn->query("SELECT * FROM omzet WHERE id_branch = $id LIMIT 1");
    if ($check->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Branch tidak bisa dihapus karena memiliki laporan'
        ]);
        return;
    }

    $check2 = $conn->query("SELECT * FROM pemakaian WHERE id_branch = $id LIMIT 1");
    if ($check2->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Branch tidak bisa dihapus karena memiliki data stock'
        ]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM branch WHERE id_branch = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function updateBranch($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id']) || !isset($data['nama']) || !isset($data['alamat'])) {
        echo json_encode(['success' => false, 'error' => 'Incomplete data']);
        return;
    }

    $id = intval($data['id']);
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



$conn->close();
?>

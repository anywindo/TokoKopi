<?php
include 'koneksi.php';
session_start();

header('Content-Type: application/json');

// Auth Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'corporate') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

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
    case 'get_branches':
        getBranches($conn);
        break;
    case 'add_branch':
        addBranch($conn);
        break;
    case 'update_branch':
        updateBranch($conn);
        break;
    case 'update_omzet':
        updateOmzet($conn);
        break;
    case 'update_stock':
        updateStock($conn);
        break;
    case 'delete_branch':
        deleteBranch($conn);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getDashboardData($conn) {
    $data = [];

    // 1. Total Pemasukan Omset Harian (7 Hari)
    $sql = "SELECT tanggal, SUM(omzet) as total 
            FROM omzet 
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            GROUP BY tanggal 
            ORDER BY tanggal ASC";
    $result = $conn->query($sql);
    $revenue_chart = [];
    while($row = $result->fetch_assoc()) {
        $revenue_chart[] = $row;
    }
    $data['revenue_chart'] = $revenue_chart;

    // 2. Sisa Stok Harian (Average per Branch for today/latest)
    $sql = "SELECT AVG(arabica) as arabica, AVG(robusta) as robusta, AVG(liberica) as liberica, AVG(decaf) as decaf, AVG(susu) as susu 
            FROM pemakaian 
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $result = $conn->query($sql);
    $data['stock_avg'] = $result->fetch_assoc();

    // 3. Pemasukan Omset Detail per Branch (7 Hari)
    $sql = "SELECT b.nama, SUM(o.omzet) as total 
            FROM omzet o 
            JOIN branch b ON o.id_branch = b.id_branch 
            WHERE o.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            GROUP BY o.id_branch";
    $result = $conn->query($sql);
    $branch_revenue = [];
    while($row = $result->fetch_assoc()) {
        $branch_revenue[] = $row;
    }
    $data['branch_revenue'] = $branch_revenue;

    echo json_encode($data);
}

function getRevenueHistory($conn) {
    $date_filter = $_GET['date'] ?? '';
    $branch_filter = $_GET['branch'] ?? '';

    $sql = "SELECT o.id_laporan, o.tanggal, o.omzet, u.username as pelapor, b.nama as branch_name 
            FROM omzet o 
            LEFT JOIN users u ON o.id_pelapor = u.id_user 
            LEFT JOIN branch b ON o.id_branch = b.id_branch 
            WHERE 1=1";

    if ($date_filter) {
        $sql .= " AND o.tanggal = '$date_filter'";
    }
    if ($branch_filter) {
        $sql .= " AND o.id_branch = '$branch_filter'";
    }
    
    $sql .= " ORDER BY o.tanggal DESC";

    $result = $conn->query($sql);
    $rows = [];
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
}

function getStockHistory($conn) {
    $date_filter = $_GET['date'] ?? '';
    $branch_filter = $_GET['branch'] ?? '';

    $sql = "SELECT p.*, b.nama as branch_name 
            FROM pemakaian p 
            LEFT JOIN branch b ON p.id_branch = b.id_branch 
            WHERE 1=1";

    if ($date_filter) {
        $sql .= " AND p.tanggal = '$date_filter'";
    }
    if ($branch_filter) {
        $sql .= " AND p.id_branch = '$branch_filter'";
    }

    $sql .= " ORDER BY p.tanggal DESC";

    $result = $conn->query($sql);
    $rows = [];
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
}

function getBranches($conn) {
    $sql = "SELECT * FROM branch";
    $result = $conn->query($sql);
    $rows = [];
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
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

function updateBranch($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
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

function updateOmzet($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $omzet = $data['omzet'];
    
    $stmt = $conn->prepare("UPDATE omzet SET omzet = ? WHERE id_laporan = ?");
    $stmt->bind_param("di", $omzet, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}

function updateStock($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $arabica = $data['arabica'];
    $robusta = $data['robusta'];
    $liberica = $data['liberica'];
    $decaf = $data['decaf'];
    $susu = $data['susu'];

    $stmt = $conn->prepare("UPDATE pemakaian SET arabica=?, robusta=?, liberica=?, decaf=?, susu=? WHERE id_laporan=?");
    $stmt->bind_param("dddddi", $arabica, $robusta, $liberica, $decaf, $susu, $id);
    
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

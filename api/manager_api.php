<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'koneksi.php';

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// AUTH
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    respond(['error' => 'Unauthorized'], 400);
}

$action = $_GET['action'] ?? null;

function float_or_zero($v){ return is_numeric($v) ? (float)$v : 0.0; }
function int_or_null($v){ return ($v !== '' && $v !== null) ? intval($v) : null; }
function is_post(){ return $_SERVER['REQUEST_METHOD'] === 'POST'; }

try {
    switch ($action) {

        case 'get_reports':
            $sql = "SELECT o.id_laporan AS 'ID LAPORAN',
                    o.tanggal AS 'TANGGAL LAPOR',
                    o.omzet AS 'OMZET BRUTO',
                    b.nama AS 'BRANCH LOKASI',
                    CASE 
                        WHEN o.tanggal < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 'Kadaluarsa'
                        ELSE 'Valid'
                    END AS STATUS
                FROM omzet o
                JOIN branch b ON o.id_branch = b.id_branch
                ORDER BY o.id_laporan DESC";

            $res = $conn->query($sql);
            $out = [];
            while ($r = $res->fetch_assoc()) $out[] = $r;

            respond($out);
            break;

        case 'get_stok':
            $sql = "SELECT p.id_laporan AS 'ID LAPORAN',
                    p.tanggal AS 'TANGGAL CEK',
                    p.arabica AS 'ARABICA (KG)',
                    p.robusta AS 'ROBUSTA (KG)',
                    p.liberica AS 'LIBERICA (KG)',
                    p.decaf AS 'DECAF (KG)',
                    p.susu AS 'SUSU (LITER)',
                    b.nama AS 'BRANCH LOKASI',
                    CASE 
                        WHEN p.tanggal < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 'Kadaluarsa'
                        ELSE 'Valid'
                    END AS STATUS
                FROM pemakaian p
                JOIN branch b ON p.id_branch = b.id_branch
                ORDER BY p.id_laporan DESC";

            $res = $conn->query($sql);
            $out = [];
            while ($r = $res->fetch_assoc()) $out[] = $r;

            respond($out);
            break;

        case 'get_detail':
            $id = int_or_null($_GET['id'] ?? null);
            if (!$id) respond(['error'=>'id required'], 400);

            // OMZET
            $stmt = $conn->prepare("SELECT o.*, b.nama AS 'BRANCH LOKASI'
                            FROM omzet o
                            JOIN branch b ON o.id_branch = b.id_branch
                            WHERE o.id_laporan = ?");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $omzet = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // PEMAKAIAN
            $stmt = $conn->prepare("SELECT p.*, b.nama AS 'BRANCH LOKASI'
                            FROM pemakaian p
                            JOIN branch b ON p.id_branch = b.id_branch
                            WHERE p.id_laporan = ?");

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stok = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            respond(['omzet'=>$omzet, 'stok'=>$stok]);
            break;

        case 'add_report':
            if (!is_post()) respond(['error'=>'POST required'], 405);

            $tanggal = $_POST['tanggal'] ?? null;
            $omzet   = float_or_zero($_POST['omzet'] ?? null);
            $id_branch = int_or_null($_POST['id_branch'] ?? null);
            $id_pelapor = int_or_null($_SESSION['id_user'] ?? null);

            if (!$tanggal || !$id_branch) {
                respond(['error'=>'tanggal dan id_branch wajib'], 400);
            }

            $stmt = $conn->prepare("INSERT INTO omzet (id_branch, id_pelapor, tanggal, omzet) VALUES (?, ?, ?, ?)");

            $stmt->bind_param("iisd", $id_branch, $id_pelapor, $tanggal, $omzet);

            if ($stmt->execute()) {
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            } else respond(['error'=>$stmt->error], 500);
            break;

        case 'update_report':
            if (!is_post()) respond(['error'=>'POST required'], 405);

            $id = int_or_null($_GET['id'] ?? null);
            if (!$id) respond(['error'=>'id required'], 400);

            $tanggal = $_POST['tanggal'] ?? null;
            $omzet   = float_or_zero($_POST['omzet'] ?? null);
            $id_branch = int_or_null($_POST['id_branch'] ?? null);

            if (!$tanggal || !$id_branch) respond(['error'=>'tanggal dan id_branch wajib'],400);

            $stmt = $conn->prepare("UPDATE omzet SET tanggal=?, omzet=?, id_branch=? WHERE id_laporan = ?");

            $stmt->bind_param("sdii", $tanggal, $omzet, $id_branch, $id);

            if ($stmt->execute()) {
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            }else respond(['error'=>$stmt->error],500);
            break;

        case 'delete_report':
            $id = int_or_null($_GET['id']);
            if (!$id) respond(['error'=>'id required'],400);

            // Hapus omzet
            $stmt = $conn->prepare("DELETE FROM omzet WHERE id_laporan = ?");
            $stmt->bind_param("i",$id);
            $stmt->execute();

            // Hapus pemakaian terkait
            $stmt = $conn->prepare("DELETE FROM pemakaian WHERE id_laporan = ?");
            $stmt->bind_param("i",$id);
            if($stmt->execute()){
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            }else respond(['error'=>$stmt->error],500);
            break;

        case 'add_stock':
            if (!is_post()) respond(['error'=>'POST required'],405);

            $tanggal = $_POST['tanggal'] ?? null;
            $id_branch = int_or_null($_POST['id_branch'] ?? null);
            $id_pelapor = int_or_null($_SESSION['id_user'] ?? null);

            $arabica  = float_or_zero($_POST['arabica'] ?? 0);
            $robusta  = float_or_zero($_POST['robusta'] ?? 0);
            $liberica = float_or_zero($_POST['liberica'] ?? 0);
            $decaf    = float_or_zero($_POST['decaf'] ?? 0);
            $susu     = float_or_zero($_POST['susu'] ?? 0);

            if (!$tanggal || !$id_branch) respond(['error'=>'tanggal & id_branch wajib'],400);

            $stmt = $conn->prepare("INSERT INTO pemakaian (id_branch, id_pelapor, tanggal, arabica, robusta, liberica, decaf, susu)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "iisddddd",
                $id_branch, $id_pelapor, $tanggal,
                $arabica, $robusta, $liberica, $decaf, $susu
            );

            if($stmt->execute()){
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            }else respond(['error'=>$stmt->error],500);
            break;

        case 'update_stock':
            if (!is_post()) respond(['error'=>'POST required'],405);

            $id = int_or_null($_GET['id']);
            if (!$id) respond(['error'=>'id required'],400);

            $tanggal = $_POST['tanggal'] ?? null;
            $id_branch = int_or_null($_POST['id_branch'] ?? null);

            $arabica  = float_or_zero($_POST['arabica'] ?? 0);
            $robusta  = float_or_zero($_POST['robusta'] ?? 0);
            $liberica = float_or_zero($_POST['liberica'] ?? 0);
            $decaf    = float_or_zero($_POST['decaf'] ?? 0);
            $susu     = float_or_zero($_POST['susu'] ?? 0);

            if (!$tanggal || !$id_branch) respond(['error'=>'tanggal & id_branch wajib'],400);

            $stmt = $conn->prepare(" UPDATE pemakaian
                            SET tanggal=?, arabica=?, robusta=?, liberica=?, decaf=?, susu=?, id_branch=?
                            WHERE id_laporan = ?");

            $stmt->bind_param(
                "sddddiii",
                $tanggal, $arabica, $robusta, $liberica, $decaf, $susu,
                $id_branch, $id
            );

            if($stmt->execute()){
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            }else respond(['error'=>$stmt->error],500);
            break;

        case 'delete_stock':
            $id = int_or_null($_GET['id']);
            if (!$id) respond(['error'=>'id required'],400);

            $stmt = $conn->prepare("DELETE FROM pemakaian WHERE id_laporan = ?");
            $stmt->bind_param("i",$id);
            
            if($stmt->execute()){
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            }else respond(['error'=>$stmt->error],500);
            break;

        case 'add_revenue':
            if (!is_post()) respond(['error'=>'POST required'],405);

            $id = int_or_null($_POST['id'] ?? null);
            $tambah = float_or_zero($_POST['tambah'] ?? null);

            if (!$id || $tambah === null) respond(['error'=>'id & tambah required'],400);

            $stmt = $conn->prepare(" UPDATE omzet
                                SET omzet = omzet + ?
                                WHERE id_laporan = ?");

            $stmt->bind_param("di", $tambah, $id);

            if($stmt->execute()){
                respond(['success'=>true, 'id'=>$conn->insert_id]);
            }else respond(['error'=>$stmt->error],500);
            break;

        case 'get_history':
            $out = [];

            $sql1 = "SELECT o.id_laporan AS 'ID LAPORAN',
                    o.tanggal AS TANGGAL,
                    o.omzet AS OMZET,
                    b.nama AS 'BRANCH LOKASI'
                FROM omzet o
                JOIN branch b ON o.id_branch = b.id_branch
                WHERE o.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY o.tanggal DESC";

            $res1 = $conn->query($sql1);
            while ($r = $res1->fetch_assoc()) $out[] = $r;

            $sql2 = " SELECT p.id_laporan AS 'ID LAPORAN',
                    p.tanggal AS TANGGAL,
                    CONCAT(
                        'A:',p.arabica,', R:',p.robusta,
                        ', L:',p.liberica,', D:',p.decaf,', S:',p.susu
                    ) AS VALUE,
                    b.nama AS 'BRANCH LOKASI'
                FROM pemakaian p
                JOIN branch b ON p.id_branch = b.id_branch
                WHERE p.tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                ORDER BY p.tanggal DESC";

            $res2 = $conn->query($sql2);
            while ($r = $res2->fetch_assoc()) $out[] = $r;

            respond($out);
            break;

        default:
            respond(['error'=>'Unknown action'],400);
    }
} catch (Exception $e) {
    respond(['error'=>'Exception: '.$e->getMessage()], 500);
}
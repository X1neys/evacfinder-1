<?php
// ajax/delete_inactivation_report.ajax.php
session_start();
require_once "../config/connection.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

$report_id = $_POST['report_id'];
$file_path = $_POST['file_path'] ?? '';

try {
    $db = new Connection();
    $pdo = $db->connect();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM saved_reports WHERE report_id = :report_id");
    $stmt->execute([':report_id' => $report_id]);
    
    // Delete physical file if it exists
    if (!empty($file_path)) {
        // Convert web path to filesystem path
        $full_path = "../" . $file_path;
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../models/centers.model.php';
require_once '../models/reports.model.php';
require_once '../controllers/reports.controller.php';

ControllerReports::ctrSaveInactivationReport();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$center_id = $_POST['center_id'] ?? '';

if (empty($center_id)) {
    echo json_encode(['success' => false, 'message' => 'Center ID required']);
    exit;
}

$generated_by = $_SESSION['userid'] ?? 'System';
$result = ModelReports::mdlSaveInactivationReport($center_id, $generated_by);

echo json_encode($result);
?>
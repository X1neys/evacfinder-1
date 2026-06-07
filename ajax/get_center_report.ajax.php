<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "../models/centers.model.php";
require_once "../models/connection.php";

header('Content-Type: application/json');

if (isset($_GET['center_id']) || isset($_POST['center_id'])) {
    $center_id = $_GET['center_id'] ?? $_POST['center_id'];
    $report = ModelCenters::mdlGetCenterReport($center_id);
    if ($report) {
        echo json_encode(['success' => true, 'report' => $report]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Center not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing center_id']);
}
?>
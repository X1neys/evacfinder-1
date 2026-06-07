<?php
session_start();
header('Content-Type: application/json');
require_once "../models/centers.model.php";

$userid = $_SESSION['userid'] ?? null;
if (!$userid) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

try {
    $center = ModelCenters::mdlGetCenterByAssignedUser($userid);
    if ($center) {
        echo json_encode(["success" => true, "center_id" => $center['center_id'], "center" => $center]);
    } else {
        echo json_encode(["success" => true, "center_id" => null, "center" => null]);
    }
} catch (Throwable $e) {
    error_log('get_my_assignment error: ' . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "../models/centers.model.php";
require_once "../models/connection.php";

header('Content-Type: application/json');

if(isset($_POST["center_id"])) {
    $assigned_by = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;
    try {
        $center_id = $_POST["center_id"];
        if (empty($center_id)) throw new Exception('center_id is required');

        $result = ModelCenters::mdlUnassignLGUFromCenter($center_id, $assigned_by);
        if ($result) {
            echo json_encode(["success" => true, "message" => "LGU assignment removed successfully"]);
        } else {
            $err = error_get_last();
            $msg = ($err && isset($err['message'])) ? $err['message'] : 'Failed to remove assignment';
            error_log('remove_lgu_assignment failed: ' . $msg);
            echo json_encode(["success" => false, "message" => $msg]);
        }
    } catch (Throwable $e) {
        error_log('remove_lgu_assignment exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing required parameter: center_id"]);
}
?>
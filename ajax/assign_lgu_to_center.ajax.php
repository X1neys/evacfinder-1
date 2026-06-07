<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "../models/centers.model.php";
require_once "../models/connection.php";

header('Content-Type: application/json');

if(isset($_POST["center_id"]) && isset($_POST["lgu_user_id"])) {
    $assigned_by = isset($_SESSION["userid"]) ? $_SESSION["userid"] : null;
    try {
        $center_id = $_POST["center_id"];
        $lgu_user_id = $_POST["lgu_user_id"];

        if (empty($center_id) || empty($lgu_user_id)) {
            throw new Exception('center_id and lgu_user_id must be provided');
        }

        $result = ModelCenters::mdlAssignLGUToCenter($center_id, $lgu_user_id, $assigned_by);

        if($result) {
            // fetch updated center report to return to the client
            $report = ModelCenters::mdlGetCenterReport($center_id);
            echo json_encode(["success" => true, "message" => "LGU user assigned successfully", "report" => $report]);
        } else {
            // model returned false — provide a helpful message and log
            $err = error_get_last();
            $msg = ($err && isset($err['message'])) ? $err['message'] : 'Failed to assign LGU user';
            error_log('assign_lgu_to_center failed: ' . $msg);
            echo json_encode(["success" => false, "message" => $msg]);
        }
    } catch (Throwable $e) {
        // return exception message for debugging and log the full trace
        error_log('assign_lgu_to_center exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
}
?>
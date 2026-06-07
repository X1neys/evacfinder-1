<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "../models/connection.php";

header('Content-Type: application/json');

if(!isset($_POST["evacuee_id"]) || !isset($_POST["status"])) {
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit;
}

$evacuee_id = $_POST["evacuee_id"];
$new_status = $_POST["status"];
$center_id = isset($_POST["center_id"]) ? $_POST["center_id"] : null;
$departure_date = isset($_POST["departure_date"]) && $_POST["departure_date"] !== "" ? $_POST["departure_date"] : null;
$transfer_center_id = isset($_POST["transfer_center_id"]) && $_POST["transfer_center_id"] !== "" ? $_POST["transfer_center_id"] : null;
$remarks = isset($_POST["remarks"]) ? $_POST["remarks"] : "";
$performed_by = isset($_SESSION["userid"]) ? $_SESSION["userid"] : "System";

$db = new Connection();
$pdo = $db->connect();

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    
    // Get current evacuee info
    $stmt = $pdo->prepare("SELECT * FROM evacuees WHERE evacuee_id = :evacuee_id");
    $stmt->bindParam(":evacuee_id", $evacuee_id);
    $stmt->execute();
    $evacuee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$evacuee) {
        throw new Exception("Evacuee not found");
    }
    
    $old_status = $evacuee['evacuee_status'];
    $old_center_id = $evacuee['evacuation_center_id'];
    
    // Update evacuee status
    $update_sql = "UPDATE evacuees SET evacuee_status = :status";
    $params = [":status" => $new_status, ":evacuee_id" => $evacuee_id];
    
    if($departure_date) {
        $update_sql .= ", departure_date = :departure_date";
        $params[":departure_date"] = $departure_date;
    }
    
    $update_sql .= " WHERE evacuee_id = :evacuee_id";
    
    $stmt = $pdo->prepare($update_sql);
    foreach($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    // Handle transfer to another center
    if($new_status == "Transferred" && $transfer_center_id) {
        // Update evacuee's center
        $stmt = $pdo->prepare("UPDATE evacuees SET evacuation_center_id = :new_center_id WHERE evacuee_id = :evacuee_id");
        $stmt->bindParam(":new_center_id", $transfer_center_id);
        $stmt->bindParam(":evacuee_id", $evacuee_id);
        $stmt->execute();
        
        // Update occupancy for old center (decrease)
        if($old_center_id) {
            $stmt = $pdo->prepare("UPDATE centers SET current_occupants = current_occupants - 1 WHERE center_id = :center_id");
            $stmt->bindParam(":center_id", $old_center_id);
            $stmt->execute();
        }
        
        // Update occupancy for new center (increase)
        $stmt = $pdo->prepare("UPDATE centers SET current_occupants = current_occupants + 1 WHERE center_id = :center_id");
        $stmt->bindParam(":center_id", $transfer_center_id);
        $stmt->execute();
    } 
    // Handle departure (decrease occupancy)
    else if($new_status == "Departed" && $old_center_id && $old_status == "Active") {
        $stmt = $pdo->prepare("UPDATE centers SET current_occupants = current_occupants - 1 WHERE center_id = :center_id");
        $stmt->bindParam(":center_id", $old_center_id);
        $stmt->execute();
    }
    
    // Record in history
    $history_desc = "Evacuee " . $evacuee['first_name'] . " " . $evacuee['last_name'] . " status changed from " . $old_status . " to " . $new_status;
    if($remarks) {
        $history_desc .= ". Remarks: " . $remarks;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO center_history (center_id, action_type, description, performed_by) 
        VALUES (:center_id, 'EVACUEE_STATUS_CHANGE', :description, :performed_by)
    ");
    $stmt->bindParam(":center_id", $old_center_id);
    $stmt->bindParam(":description", $history_desc);
    $stmt->bindParam(":performed_by", $performed_by);
    $stmt->execute();
    
    $pdo->commit();
    
    echo json_encode(["success" => true, "message" => "Evacuee status updated successfully"]);
    
} catch(Exception $e) {
    $pdo->rollBack();
    error_log("Update evacuee status error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}




// After adding or updating evacuees, check and update center status
function updateCenterStatusBasedOnOccupancy($pdo, $center_id) {
    // Get current active evacuee count
    $stmt = $pdo->prepare("SELECT COUNT(*) as active_count FROM evacuees WHERE evacuation_center_id = :center_id AND evacuee_status = 'Active'");
    $stmt->execute([':center_id' => $center_id]);
    $activeCount = $stmt->fetch(PDO::FETCH_ASSOC)['active_count'];
    
    // Get center capacity
    $stmt = $pdo->prepare("SELECT capacity, status FROM centers WHERE center_id = :center_id");
    $stmt->execute([':center_id' => $center_id]);
    $center = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $newStatus = $center['status'];
    
    if ($activeCount >= $center['capacity'] && $center['capacity'] > 0) {
        $newStatus = 'Full';
    } elseif ($center['status'] == 'Full' && $activeCount < $center['capacity']) {
        $newStatus = 'Active';
    }
    
    if ($newStatus != $center['status']) {
        $stmt = $pdo->prepare("UPDATE centers SET status = :status WHERE center_id = :center_id");
        $stmt->execute([':status' => $newStatus, ':center_id' => $center_id]);
    }
    
    return $newStatus;
}


?>
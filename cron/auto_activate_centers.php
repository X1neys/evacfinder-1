<?php
// For cron job - use absolute path to be safe
require_once dirname(__DIR__) . "/models/centers.model.php";

// Or use relative path (may not work in cron)
// require_once "../models/centers.model.php";

$db = new Connection();
$pdo = $db->connect();

// Get pending schedules that are due
$sql = "SELECT cs.*, c.center_name 
        FROM center_schedules cs
        JOIN centers c ON cs.center_id = c.center_id
        WHERE cs.status = 'Pending' 
        AND cs.scheduled_datetime <= NOW()";
        
$stmt = $pdo->query($sql);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($schedules as $schedule) {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Update the center to Active status
        $updateSql = "UPDATE centers SET 
                      status = 'Active',
                      capacity = :capacity,
                      water_supply = :water_supply,
                      electricity = :electricity,
                      num_rooms = :num_rooms,
                      has_wifi = :has_wifi,
                      has_canteen = :has_canteen,
                      has_medical = :has_medical,
                      restrooms_count = :restrooms_count,
                      remarks = CONCAT(IFNULL(remarks, ''), '\n[Auto-activated on ', NOW(), ']: ', :notes)
                      WHERE center_id = :center_id";
        
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':capacity' => $schedule['capacity'] ?? 0,
            ':water_supply' => $schedule['water_supply'],
            ':electricity' => $schedule['electricity'],
            ':num_rooms' => $schedule['num_rooms'],
            ':has_wifi' => $schedule['has_wifi'],
            ':has_canteen' => $schedule['has_canteen'],
            ':has_medical' => $schedule['has_medical'],
            ':restrooms_count' => $schedule['restrooms_count'],
            ':notes' => $schedule['notes'],
            ':center_id' => $schedule['center_id']
        ]);
        
        // Update schedule status
        $updateSchedule = $pdo->prepare("UPDATE center_schedules 
                                        SET status = 'Executed', executed_at = NOW() 
                                        WHERE schedule_id = :schedule_id");
        $updateSchedule->execute([':schedule_id' => $schedule['schedule_id']]);
        
        // Log to history table
        $historySql = "INSERT INTO center_history (center_id, action_type, description, performed_by, created_at)
                       VALUES (:center_id, 'CENTER_AUTO_ACTIVATED', :description, 'System', NOW())";
        
        $historyStmt = $pdo->prepare($historySql);
        $historyStmt->execute([
            ':center_id' => $schedule['center_id'],
            ':description' => "Center automatically activated via scheduled task. Capacity set to {$schedule['capacity']}. Notes: {$schedule['notes']}"
        ]);
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error activating center {$schedule['center_id']}: " . $e->getMessage());
    }
}

echo count($schedules) . " center(s) activated at " . date('Y-m-d H:i:s') . "\n";
?>
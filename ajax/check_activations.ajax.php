<?php
date_default_timezone_set('Asia/Manila');
require_once "../models/centers.model.php";
session_start();

header('Content-Type: application/json');

try {
    $db = new Connection();
    $pdo = $db->connect();
    $pdo->exec("SET time_zone = '+08:00'");
    
    // Check if center_schedules table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'center_schedules'");
    if ($checkTable->rowCount() == 0) {
        echo json_encode(['activated' => 0, 'centers' => [], 'message' => 'No schedules table']);
        exit;
    }
    
    $sql = "SELECT cs.*, c.center_name, c.capacity as current_capacity
            FROM center_schedules cs
            JOIN centers c ON cs.center_id = c.center_id
            WHERE cs.status = 'Pending' 
            AND cs.scheduled_datetime <= NOW()";
    
    $stmt = $pdo->query($sql);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $activated_count = 0;
    $activated_centers = [];
    
    foreach ($schedules as $schedule) {
        try {
            $pdo->beginTransaction();
            
            // Get the capacity from additional_info JSON
            $additionalInfo = json_decode($schedule['additional_info'], true);
            $newCapacity = $additionalInfo['capacity'] ?? 100;
            
            // Update center with new capacity and status
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
                          remarks = CONCAT(IFNULL(remarks, ''), '\n[Activated on ', NOW(), '] Capacity set to: ', :capacity, ' | Notes: ', :notes)
                          WHERE center_id = :center_id";
            
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':capacity' => $newCapacity,
                ':water_supply' => $schedule['water_supply'],
                ':electricity' => $schedule['electricity'],
                ':num_rooms' => $schedule['num_rooms'],
                ':has_wifi' => $schedule['has_wifi'],
                ':has_canteen' => $schedule['has_canteen'],
                ':has_medical' => $schedule['has_medical'],
                ':restrooms_count' => $schedule['restrooms_count'],
                ':notes' => $additionalInfo['notes'] ?? '',
                ':center_id' => $schedule['center_id']
            ]);
            
            // Update schedule status
            $updateSchedule = $pdo->prepare("UPDATE center_schedules 
                                            SET status = 'Executed', executed_at = NOW() 
                                            WHERE schedule_id = :schedule_id");
            $updateSchedule->execute([':schedule_id' => $schedule['schedule_id']]);
            
            $pdo->commit();
            $activated_count++;
            $activated_centers[] = $schedule['center_name'] . " (Capacity: " . number_format($newCapacity) . ")";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error activating center: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'activated' => $activated_count,
        'centers' => $activated_centers,
        'message' => $activated_count > 0 ? "$activated_count center(s) activated" : "No pending activations"
    ]);
    
} catch (Exception $e) {
    echo json_encode(['activated' => 0, 'centers' => [], 'message' => 'Error: ' . $e->getMessage()]);
}
?>
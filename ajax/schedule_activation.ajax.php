<?php
date_default_timezone_set('Asia/Manila');
require_once "../models/centers.model.php";
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    $db = new Connection();
    $pdo = $db->connect();
    $pdo->exec("SET time_zone = '+08:00'");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

switch ($action) {
    case 'create_schedule':
        createSchedule($pdo, $response);
        break;
    case 'get_schedules':
        getSchedules($pdo, $response);
        break;
    case 'cancel_schedule':
        cancelSchedule($pdo, $response);
        break;
    default:
        $response['message'] = 'Invalid action';
}

echo json_encode($response);

function createSchedule($pdo, &$response) {
    $center_id = $_POST['center_id'] ?? '';
    $scheduled_datetime = $_POST['scheduled_datetime'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $created_by = $_SESSION['userid'] ?? '00006';
    
    if (empty($center_id)) {
        $response['message'] = 'Center ID is required';
        return;
    }
    
    if (empty($scheduled_datetime)) {
        $response['message'] = 'Schedule date and time is required';
        return;
    }
    
    if (empty($capacity) || $capacity <= 0) {
        $response['message'] = 'Valid capacity is required';
        return;
    }
    
    try {
        // Check if center_schedules table exists
        $checkTable = $pdo->query("SHOW TABLES LIKE 'center_schedules'");
        if ($checkTable->rowCount() == 0) {
            $response['message'] = 'Schedules table does not exist';
            return;
        }
        
        // Check if center exists
        $checkCenter = $pdo->prepare("SELECT center_id FROM centers WHERE center_id = :center_id");
        $checkCenter->execute([':center_id' => $center_id]);
        if ($checkCenter->rowCount() == 0) {
            $response['message'] = 'Center not found';
            return;
        }
        
        // UPDATE the center's capacity immediately
        $updateCenter = $pdo->prepare("UPDATE centers SET capacity = :capacity WHERE center_id = :center_id");
        $updateCenter->execute([
            ':capacity' => $capacity,
            ':center_id' => $center_id
        ]);
        
        // Generate schedule ID
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM center_schedules");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $schedule_id = 'SCH' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        
        // Store ALL info including capacity in additional_info JSON
        $additional_info = json_encode([
            'capacity' => $capacity,
            'water_supply' => $_POST['water_supply'] ?? '',
            'electricity' => $_POST['electricity'] ?? '',
            'num_rooms' => $_POST['num_rooms'] ?? 0,
            'has_wifi' => isset($_POST['has_wifi']) ? 1 : 0,
            'has_canteen' => isset($_POST['has_canteen']) ? 1 : 0,
            'has_medical' => isset($_POST['has_medical']) ? 1 : 0,
            'restrooms_count' => $_POST['restrooms_count'] ?? 0,
            'notes' => $_POST['notes'] ?? ''
        ]);
        
        $sql = "INSERT INTO center_schedules (
                    schedule_id, center_id, scheduled_datetime, status, 
                    additional_info,
                    water_supply, electricity, num_rooms, 
                    has_wifi, has_canteen, has_medical, restrooms_count, 
                    notes, created_by, created_at
                ) VALUES (
                    :schedule_id, :center_id, :scheduled_datetime, 'Pending', 
                    :additional_info,
                    :water_supply, :electricity, :num_rooms,
                    :has_wifi, :has_canteen, :has_medical, :restrooms_count,
                    :notes, :created_by, NOW()
                )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':schedule_id' => $schedule_id,
            ':center_id' => $center_id,
            ':scheduled_datetime' => $scheduled_datetime,
            ':additional_info' => $additional_info,
            ':water_supply' => $_POST['water_supply'] ?? '',
            ':electricity' => $_POST['electricity'] ?? '',
            ':num_rooms' => $_POST['num_rooms'] ?? 0,
            ':has_wifi' => isset($_POST['has_wifi']) ? 1 : 0,
            ':has_canteen' => isset($_POST['has_canteen']) ? 1 : 0,
            ':has_medical' => isset($_POST['has_medical']) ? 1 : 0,
            ':restrooms_count' => $_POST['restrooms_count'] ?? 0,
            ':notes' => $_POST['notes'] ?? '',
            ':created_by' => $created_by
        ]);
        
        $response['success'] = true;
        $response['message'] = 'Schedule created successfully. Center capacity updated to ' . number_format($capacity) . '.';
        $response['schedule_id'] = $schedule_id;
        $response['new_capacity'] = $capacity;
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

function getSchedules($pdo, &$response) {
    try {
        $sql = "SELECT cs.*, c.center_name, c.capacity as current_capacity
                FROM center_schedules cs
                JOIN centers c ON cs.center_id = c.center_id
                WHERE cs.status = 'Pending' 
                AND cs.scheduled_datetime > NOW()
                ORDER BY cs.scheduled_datetime ASC";
        
        $stmt = $pdo->query($sql);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse additional_info to get the scheduled capacity
        foreach ($schedules as &$schedule) {
            $info = json_decode($schedule['additional_info'], true);
            $schedule['scheduled_capacity'] = $info['capacity'] ?? 'Not set';
        }
        
        $response['success'] = true;
        $response['schedules'] = $schedules;
        
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Error fetching schedules: ' . $e->getMessage();
        $response['schedules'] = [];
    }
}

function cancelSchedule($pdo, &$response) {
    $schedule_id = $_POST['schedule_id'] ?? '';
    
    if (empty($schedule_id)) {
        $response['message'] = 'Schedule ID required';
        return;
    }
    
    try {
        // Get the schedule info before cancelling
        $getSchedule = $pdo->prepare("SELECT center_id, additional_info FROM center_schedules WHERE schedule_id = :schedule_id");
        $getSchedule->execute([':schedule_id' => $schedule_id]);
        $schedule = $getSchedule->fetch(PDO::FETCH_ASSOC);
        
        if ($schedule) {
            // Restore the center's previous capacity? Or just cancel the schedule
            // For now, we'll just cancel the schedule without changing capacity
            // The capacity remains as updated when schedule was created
        }
        
        $sql = "UPDATE center_schedules SET status = 'Cancelled' WHERE schedule_id = :schedule_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':schedule_id' => $schedule_id]);
        
        $response['success'] = true;
        $response['message'] = 'Schedule cancelled successfully';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}
?>
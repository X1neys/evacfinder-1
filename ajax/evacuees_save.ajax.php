<?php
date_default_timezone_set('Asia/Manila');
require_once "../models/centers.model.php";
session_start();

header('Content-Type: text/html');

$response = 'error';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'error';
    exit;
}

try {
    $db = new Connection();
    $pdo = $db->connect();
    $pdo->exec("SET time_zone = '+08:00'");
    
    $trans_type = $_POST['trans_type'] ?? 'New';
    $encodedby = $_POST['encodedby'] ?? $_SESSION['userid'] ?? '00006';
    
    // Generate evacuee ID
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM evacuees");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $evacuee_id = 'Evac' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    
    // Prepare data
    $registration_date = $_POST['registration_date'] ?? date('Y-m-d');
    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $extension_name = $_POST['extension_name'] ?? '';
    $relation_to_head = $_POST['relation_to_head'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $age = !empty($_POST['age']) ? $_POST['age'] : null;
    $civil_status = $_POST['civil_status'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $complete_address = $_POST['complete_address'] ?? '';
    $emergency_contact_person = $_POST['emergency_contact_person'] ?? '';
    $emergency_contact_number = $_POST['emergency_contact_number'] ?? '';
    $condition_pregnant = isset($_POST['condition_pregnant']) ? 1 : 0;
    $condition_lactating = isset($_POST['condition_lactating']) ? 1 : 0;
    $condition_elderly = isset($_POST['condition_elderly']) ? 1 : 0;
    $condition_pwd = isset($_POST['condition_pwd']) ? 1 : 0;
    $condition_4ps = isset($_POST['condition_4ps']) ? 1 : 0;
    $pwd_type = $_POST['pwd_type'] ?? '';
    $health_status = $_POST['health_status'] ?? '';
    $emergency_medical_condition = $_POST['emergency_medical_condition'] ?? '';
    $medications_taken = $_POST['medications_taken'] ?? '';
    $known_allergies = $_POST['known_allergies'] ?? '';
    $evacuation_center_id = $_POST['evacuation_center_id'] ?? null;
    $arrival_date = $_POST['arrival_date'] ?? date('Y-m-d');
    $evacuee_status = $_POST['evacuee_status'] ?? 'Active';
    
    // Validate required fields
    if (empty($last_name) || empty($first_name) || empty($sex)) {
        echo 'error';
        exit;
    }
    
    $sql = "INSERT INTO evacuees (
                evacuee_id, registration_date, last_name, first_name, 
                middle_name, extension_name, relation_to_head, sex, 
                birth_date, age, civil_status, occupation, 
                contact_number, complete_address, 
                emergency_contact_person, emergency_contact_number,
                condition_pregnant, condition_lactating, condition_elderly, 
                condition_pwd, condition_4ps, pwd_type,
                health_status, emergency_medical_condition, 
                medications_taken, known_allergies,
                evacuation_center_id, arrival_date, evacuee_status, 
                encodedby, created_at, registered_by_lgu_id
            ) VALUES (
                :evacuee_id, :registration_date, :last_name, :first_name,
                :middle_name, :extension_name, :relation_to_head, :sex,
                :birth_date, :age, :civil_status, :occupation,
                :contact_number, :complete_address,
                :emergency_contact_person, :emergency_contact_number,
                :condition_pregnant, :condition_lactating, :condition_elderly,
                :condition_pwd, :condition_4ps, :pwd_type,
                :health_status, :emergency_medical_condition,
                :medications_taken, :known_allergies,
                :evacuation_center_id, :arrival_date, :evacuee_status,
                :encodedby, NOW(), :registered_by_lgu_id
            )";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':evacuee_id' => $evacuee_id,
        ':registration_date' => $registration_date,
        ':last_name' => $last_name,
        ':first_name' => $first_name,
        ':middle_name' => $middle_name,
        ':extension_name' => $extension_name,
        ':relation_to_head' => $relation_to_head,
        ':sex' => $sex,
        ':birth_date' => $birth_date,
        ':age' => $age,
        ':civil_status' => $civil_status,
        ':occupation' => $occupation,
        ':contact_number' => $contact_number,
        ':complete_address' => $complete_address,
        ':emergency_contact_person' => $emergency_contact_person,
        ':emergency_contact_number' => $emergency_contact_number,
        ':condition_pregnant' => $condition_pregnant,
        ':condition_lactating' => $condition_lactating,
        ':condition_elderly' => $condition_elderly,
        ':condition_pwd' => $condition_pwd,
        ':condition_4ps' => $condition_4ps,
        ':pwd_type' => $pwd_type,
        ':health_status' => $health_status,
        ':emergency_medical_condition' => $emergency_medical_condition,
        ':medications_taken' => $medications_taken,
        ':known_allergies' => $known_allergies,
        ':evacuation_center_id' => $evacuation_center_id,
        ':arrival_date' => $arrival_date,
        ':evacuee_status' => $evacuee_status,
        ':encodedby' => $encodedby,
        ':registered_by_lgu_id' => $_SESSION['userid'] ?? null
    ]);
    
    if ($result) {
        // Update center current_occupants (optional, but good for tracking)
        $updateCenter = $pdo->prepare("UPDATE centers SET current_occupants = (
            SELECT COUNT(*) FROM evacuees WHERE evacuation_center_id = :center_id AND evacuee_status = 'Active'
        ) WHERE center_id = :center_id");
        $updateCenter->execute([':center_id' => $evacuation_center_id]);
        
        $response = 'success';
    } else {
        $response = 'error';
    }
    
} catch (Exception $e) {
    error_log("Evacuee save error: " . $e->getMessage());
    $response = 'error';
}

echo $response;
?>
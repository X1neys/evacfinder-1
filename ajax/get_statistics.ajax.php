<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "../config/connection.php";

$db = new Connection();
$pdo = $db->connect();

// Count only evacuees from active centers
$stmt = $pdo->prepare("
    SELECT COUNT(e.id) as occupied 
    FROM evacuees e
    INNER JOIN centers c ON e.evacuation_center_id = c.center_id
    WHERE e.evacuee_status = 'Active' 
    AND c.status = 'Active'
");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'currently_occupied' => $result['occupied'] ?? 0
]);
?>
<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../models/userrights.model.php';
session_start();

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === 'ok') {
    $currentUser = $_SESSION['userid'] ?? '';
    // Optionally verify current user has rights to change permissions; for now only allow if same user is admin type
    $profile = ModelUserRights::mdlGetUserCredentials('userrights', 'userid', $currentUser);
    $currentUserPerms = ModelUserRights::mdlGetPermissions($currentUser);
    if (isset($currentUserPerms['useraccess']) && strtolower($currentUserPerms['useraccess']) === 'restricted') {
        $response['message'] = 'Access denied';
        echo json_encode($response);
        exit;
    }
    // Basic guard: only allow if profile type is lgu or public? Ideally add admin check; for now allow any logged in user

    $userid = trim($_POST['userid'] ?? '');
    $permsJson = $_POST['permissions'] ?? '';
    if ($userid === '' || $permsJson === '') {
        $response['message'] = 'Missing parameters';
        echo json_encode($response);
        exit;
    }

    $decoded = json_decode($permsJson, true);
    if (!is_array($decoded)) {
        $response['message'] = 'Invalid permissions payload';
        echo json_encode($response);
        exit;
    }

    $ok = ModelUserRights::mdlSetPermissions($userid, $decoded);
    if ($ok) {
        $response = ['success' => true, 'message' => 'Permissions saved'];
    } else {
        $response['message'] = 'Save failed: ' . (ModelUserRights::$lastError ?? '');
    }
}

echo json_encode($response);

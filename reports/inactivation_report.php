<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../models/centers.model.php';

if (!isset($_GET['center_id']) && !isset($_POST['center_id'])) {
    die('Center ID required');
}

$center_id = $_GET['center_id'] ?? $_POST['center_id'];

$db = new Connection();
$pdo = $db->connect();
$pdo->exec("SET time_zone = '+08:00'");

// Get center info
$stmt = $pdo->prepare("
    SELECT c.* 
    FROM centers c
    WHERE c.center_id = :center_id
");
$stmt->bindParam(":center_id", $center_id);
$stmt->execute();
$center = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$center) {
    die('Center not found');
}

// Get ALL evacuees
$stmt2 = $pdo->prepare("
    SELECT * FROM evacuees 
    WHERE evacuation_center_id = :center_id 
    ORDER BY arrival_date DESC
");
$stmt2->bindParam(":center_id", $center_id);
$stmt2->execute();
$allEvacuees = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Get center history
$stmt3 = $pdo->prepare("
    SELECT * FROM center_history 
    WHERE center_id = :center_id 
    ORDER BY created_at DESC
");
$stmt3->bindParam(":center_id", $center_id);
$stmt3->execute();
$centerHistory = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Get status history
$stmt4 = $pdo->prepare("
    SELECT * FROM center_status_history 
    WHERE center_id = :center_id 
    ORDER BY changed_at DESC
");
$stmt4->bindParam(":center_id", $center_id);
$stmt4->execute();
$statusHistory = $stmt4->fetchAll(PDO::FETCH_ASSOC);

// Get schedules
$stmt5 = $pdo->prepare("
    SELECT * FROM center_schedules 
    WHERE center_id = :center_id 
    ORDER BY scheduled_datetime DESC
");
$stmt5->bindParam(":center_id", $center_id);
$stmt5->execute();
$schedules = $stmt5->fetchAll(PDO::FETCH_ASSOC);

// Separate evacuees
$activeEvacuees = array_filter($allEvacuees, function($e) {
    return $e['evacuee_status'] == 'Active';
});
$departedEvacuees = array_filter($allEvacuees, function($e) {
    return $e['evacuee_status'] != 'Active';
});

// Calculate statistics
$stats = [
    'active' => count($activeEvacuees),
    'departed' => count($departedEvacuees),
    'total' => count($allEvacuees),
    'male' => 0,
    'female' => 0,
    'children' => 0,
    'elderly' => 0,
    'pwd' => 0,
    'pregnant' => 0,
    'lactating' => 0
];

foreach ($allEvacuees as $e) {
    if ($e['sex'] == 'Male') $stats['male']++;
    if ($e['sex'] == 'Female') $stats['female']++;
    if ($e['age'] && $e['age'] < 18) $stats['children']++;
    if ($e['condition_elderly']) $stats['elderly']++;
    if ($e['condition_pwd']) $stats['pwd']++;
    if ($e['condition_pregnant']) $stats['pregnant']++;
    if ($e['condition_lactating']) $stats['lactating']++;
}

// Get last activation and inactivation info
$lastActivation = null;
$lastInactivation = null;
$lastSchedule = null;

foreach ($statusHistory as $history) {
    if ($history['new_status'] == 'Active' && !$lastActivation) {
        $lastActivation = $history;
    }
    if ($history['new_status'] == 'Inactive' && !$lastInactivation) {
        $lastInactivation = $history;
    }
}

foreach ($schedules as $schedule) {
    if ($schedule['status'] == 'Executed' && !$lastSchedule) {
        $lastSchedule = $schedule;
    }
}

// Get schedule info
$scheduleInfo = null;
if ($lastSchedule) {
    $scheduleInfo = json_decode($lastSchedule['additional_info'], true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INACTIVATION REPORT - <?php echo htmlspecialchars($center['center_name']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; font-size: 12px; background: #f0f0f0; }
        .report-container { max-width: 1200px; margin: 0 auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .report-content { padding: 20px; }
        .header { text-align: center; padding: 20px; border-bottom: 2px solid #333; margin-bottom: 20px; }
        .header h1 { font-size: 24px; }
        .header h2 { font-size: 18px; color: #dc3545; margin-top: 10px; }
        .inactivation-box { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin-bottom: 20px; text-align: center; border-radius: 5px; }
        .inactivation-box h3 { color: #721c24; margin-bottom: 10px; }
        .info-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; background: #f9f9f9; }
        .info-card table { width: 100%; border-collapse: collapse; }
        .info-card td { padding: 8px; vertical-align: top; }
        .timeline-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; background: #fff; }
        .timeline-item { padding: 10px; border-left: 3px solid; margin-bottom: 10px; background: #fafafa; }
        .timeline-item.activation { border-left-color: #28a745; }
        .timeline-item.inactivation { border-left-color: #dc3545; }
        .timeline-item.schedule { border-left-color: #ffc107; }
        .timeline-date { font-weight: bold; color: #333; }
        .timeline-action { font-size: 13px; margin-top: 5px; }
        .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-grid td { border: 1px solid #ddd; padding: 12px; text-align: center; background: white; }
        .stats-grid h3 { font-size: 24px; margin: 0; color: #2c3e50; }
        .section-title { font-size: 16px; font-weight: bold; margin: 25px 0 15px; padding-bottom: 8px; border-bottom: 2px solid #ddd; }
        .evacuee-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
        .evacuee-table th, .evacuee-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .evacuee-table th { background: #f5f5f5; }
        .history-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
        .history-table th, .history-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .history-table th { background: #f5f5f5; }
        .empty-state { text-align: center; padding: 30px; border: 1px solid #ddd; background: #fafafa; color: #666; }
        .signature-section { margin-top: 40px; display: flex; gap: 40px; }
        .signature-box { flex: 1; text-align: center; }
        .signature-line { border-top: 1px solid #000; margin: 30px auto 8px; width: 80%; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9px; }
        .btn-print { background: #2c3e50; color: white; padding: 10px 25px; border: none; cursor: pointer; margin-bottom: 20px; font-size: 12px; border-radius: 4px; }
        @media print { body { padding: 0; background: white; } .btn-print { display: none; } }
        .condition-tag { display: inline-block; padding: 2px 6px; border: 1px solid #ccc; font-size: 9px; background: #fafafa; border-radius: 3px; margin: 2px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>
    
    <div class="report-container">
        <div class="report-content">
            <div class="header">
                <h1>🏢 EVACUATION CENTER INACTIVATION REPORT</h1>
                <h2>⚠️ OFFICIAL CENTER CLOSURE DOCUMENT</h2>
                <p>Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                <p>Report ID: INACT-<?php echo date('YmdHis') . '-' . substr($center['center_id'], -5); ?></p>
            </div>
            
            <div class="inactivation-box">
                <h3>⚠️ THIS CENTER HAS BEEN OFFICIALLY INACTIVATED</h3>
                <p>The evacuation center is no longer accepting evacuees. All active evacuees have been marked as DEPARTED.</p>
                <p><strong>Inactivation Date & Time:</strong> <?php echo date('F d, Y h:i A'); ?></p>
                <p><strong>Inactivated By:</strong> <?php echo htmlspecialchars($_SESSION['userid'] ?? 'System'); ?></p>
                <p><strong>Center Status:</strong> <span class="badge badge-danger">INACTIVE</span></p>
            </div>
            
            <!-- Center Information -->
            <div class="info-card">
                <table>
                    <tr><td style="width: 50%;"><strong>Center Name:</strong> <?php echo htmlspecialchars($center['center_name']); ?></span>
                        <td style="width: 50%;"><strong>Center ID:</strong> <?php echo htmlspecialchars($center['center_id']); ?></span>
                    </tr>
                    <tr><td><strong>Category:</strong> <?php echo htmlspecialchars($center['category']); ?></span>
                        <td><strong>Location:</strong> <?php echo htmlspecialchars($center['barangay'] . ', ' . $center['city']); ?></span>
                    </tr>
                    <tr><td><strong>Province:</strong> <?php echo htmlspecialchars($center['province']); ?></span>
                        <td><strong>Address:</strong> <?php echo htmlspecialchars($center['address'] ?: 'N/A'); ?></span>
                    </tr>
                    <tr><td><strong>Contact Number:</strong> <?php echo htmlspecialchars($center['contact_number'] ?: 'N/A'); ?></span>
                        <td><strong>Contact Person:</strong> <?php echo htmlspecialchars($center['contact_person'] ?: 'N/A'); ?></span>
                    </tr>
                </table>
            </div>
            
            <!-- Facility Information -->
            <div class="info-card">
                <table>
                    <tr><td><strong>💧 Water Supply:</strong> <?php echo htmlspecialchars($center['water_supply'] ?? 'N/A'); ?></span>
                        <td><strong>⚡ Electricity:</strong> <?php echo htmlspecialchars($center['electricity'] ?? 'N/A'); ?></span>
                    </tr>
                    <tr><td><strong>🚪 Rooms:</strong> <?php echo htmlspecialchars($center['num_rooms'] ?? 'N/A'); ?></span>
                        <td><strong>🚻 Restrooms:</strong> <?php echo htmlspecialchars($center['restrooms_count'] ?? 'N/A'); ?></span>
                    </tr>
                    <tr><td><strong>📶 WiFi:</strong> <?php echo ($center['has_wifi'] ?? 0) ? '✓ Available' : '✗ Not Available'; ?></span>
                        <td><strong>🍽️ Canteen:</strong> <?php echo ($center['has_canteen'] ?? 0) ? '✓ Available' : '✗ Not Available'; ?></span>
                    </tr>
                    <tr><td><strong>🏥 Medical Station:</strong> <?php echo ($center['has_medical'] ?? 0) ? '✓ Available' : '✗ Not Available'; ?></span>
                        <td><strong>♿ Accessibility:</strong> <?php echo htmlspecialchars($center['accessibility'] ?: 'N/A'); ?></span>
                    </tr>
                </table>
            </div>
            
            <!-- Timeline -->
            <div class="section-title">📅 Center Timeline</div>
            <div class="timeline-card">
                <?php if ($lastSchedule): ?>
                <div class="timeline-item schedule">
                    <div class="timeline-date">📅 Scheduled Activation</div>
                    <div class="timeline-action">
                        <strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($lastSchedule['scheduled_datetime'])); ?><br>
                        <strong>Status:</strong> <?php echo $lastSchedule['status']; ?><br>
                        <strong>Scheduled Capacity:</strong> <?php echo number_format($scheduleInfo['capacity'] ?? $lastSchedule['capacity'] ?? 'N/A'); ?><br>
                        <strong>Notes:</strong> <?php echo htmlspecialchars($lastSchedule['notes'] ?: 'None'); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($lastActivation): ?>
                <div class="timeline-item activation">
                    <div class="timeline-date">✅ Last Activation</div>
                    <div class="timeline-action">
                        <strong>Date & Time:</strong> <?php echo date('F d, Y h:i A', strtotime($lastActivation['changed_at'])); ?><br>
                        <strong>Changed By:</strong> <?php echo htmlspecialchars($lastActivation['changed_by'] ?: 'System'); ?><br>
                        <strong>Status Changed From:</strong> <?php echo $lastActivation['old_status']; ?> → <strong>Active</strong>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($lastInactivation): ?>
                <div class="timeline-item inactivation">
                    <div class="timeline-date">❌ Current Inactivation</div>
                    <div class="timeline-action">
                        <strong>Date & Time:</strong> <?php echo date('F d, Y h:i A', strtotime($lastInactivation['changed_at'])); ?><br>
                        <strong>Changed By:</strong> <?php echo htmlspecialchars($lastInactivation['changed_by'] ?: 'System'); ?><br>
                        <strong>Status Changed From:</strong> <?php echo $lastInactivation['old_status']; ?> → <strong>Inactive</strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Final Statistics -->
            <div class="section-title">📊 Final Summary Statistics</div>
            <table class="stats-grid">
                <tr><td><h3><?php echo $stats['total']; ?></h3><p>Total Evacuees Served</p></span><td><h3><?php echo $stats['active']; ?></h3><p>Active at Inactivation</p></span><td><h3><?php echo $stats['departed']; ?></h3><p>Previously Departed</p></span><td><h3><?php echo $stats['male']; ?></h3><p>Male</p></span></tr>
                <tr><td><h3><?php echo $stats['female']; ?></h3><p>Female</p></span><td><h3><?php echo $stats['children']; ?></h3><p>Children (<18)</p></span><td><h3><?php echo $stats['elderly']; ?></h3><p>Elderly (60+)</p></span><td><h3><?php echo $stats['pwd']; ?></h3><p>PWD</p></span></tr>
                <tr><td><h3><?php echo $stats['pregnant']; ?></h3><p>Pregnant</p></span><td><h3><?php echo $stats['lactating']; ?></h3><p>Lactating</p></span><td colspan="2"></span></tr>
            </table>
            
            <!-- Active Evacuees at Inactivation -->
            <div class="section-title">👥 Active Evacuees at Time of Inactivation (<?php echo count($activeEvacuees); ?> evacuees)</div>
            <?php if (count($activeEvacuees) > 0): ?>
            <table class="evacuee-table">
                <thead><tr><th>#</th><th>Full Name</th><th>Age</th><th>Sex</th><th>Special Conditions</th><th>Arrival Date</th></tr></thead>
                <tbody>
                    <?php foreach ($activeEvacuees as $index => $e): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($e['last_name'] . ', ' . $e['first_name']); ?></td>
                        <td><?php echo $e['age'] ?: 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($e['sex']); ?></td>
                        <td><?php
                            if ($e['condition_pregnant']) echo '<span class="condition-tag">🤰 Pregnant</span> ';
                            if ($e['condition_lactating']) echo '<span class="condition-tag">🍼 Lactating</span> ';
                            if ($e['condition_elderly']) echo '<span class="condition-tag">👴 Elderly</span> ';
                            if ($e['condition_pwd']) echo '<span class="condition-tag">♿ PWD</span> ';
                            if (!$e['condition_pregnant'] && !$e['condition_lactating'] && !$e['condition_elderly'] && !$e['condition_pwd']) echo 'None';
                        ?></td>
                        <td><?php echo date('M d, Y', strtotime($e['arrival_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">📭 No active evacuees at time of inactivation.</div>
            <?php endif; ?>
            
            <!-- All Evacuees -->
            <div class="section-title">📋 Complete Evacuee History (Total: <?php echo $stats['total']; ?>)</div>
            <?php if (count($allEvacuees) > 0): ?>
            <table class="evacuee-table">
                <thead><tr><th>#</th><th>Full Name</th><th>Age</th><th>Sex</th><th>Status</th><th>Arrival Date</th><th>Departure Date</th></tr></thead>
                <tbody>
                    <?php foreach ($allEvacuees as $index => $e): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($e['last_name'] . ', ' . $e['first_name']); ?></td>
                        <td><?php echo $e['age'] ?: 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($e['sex']); ?></td>
                        <td><span class="badge badge-<?php echo $e['evacuee_status'] == 'Active' ? 'success' : 'warning'; ?>"><?php echo $e['evacuee_status']; ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($e['arrival_date'])); ?></td>
                        <td><?php echo $e['departure_date'] ? date('M d, Y', strtotime($e['departure_date'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No evacuees recorded for this center.</div>
            <?php endif; ?>
            
            <!-- Center Activity History -->
            <div class="section-title">📜 Center Activity Log</div>
            <?php if (count($centerHistory) > 0): ?>
            <table class="history-table">
                <thead><tr><th>Date & Time</th><th>Action Type</th><th>Description</th><th>Performed By</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($centerHistory, 0, 50) as $history): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i A', strtotime($history['created_at'])); ?></td>
                        <td><?php echo str_replace('_', ' ', $history['action_type']); ?></td>
                        <td><?php echo htmlspecialchars($history['description'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($history['performed_by'] ?: 'System'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No activity logs available.</div>
            <?php endif; ?>
            
            <!-- Status Change History -->
            <div class="section-title">🔄 Status Change History</div>
            <?php if (count($statusHistory) > 0): ?>
            <table class="history-table">
                <thead><tr><th>Date & Time</th><th>Old Status</th><th>New Status</th><th>Changed By</th></tr></thead>
                <tbody>
                    <?php foreach ($statusHistory as $history): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i A', strtotime($history['changed_at'])); ?></td>
                        <td><?php echo $history['old_status'] ?: 'Unknown'; ?></td>
                        <td><strong><?php echo $history['new_status']; ?></strong></td>
                        <td><?php echo htmlspecialchars($history['changed_by'] ?: 'System'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No status change history available.</div>
            <?php endif; ?>
            
            <!-- Remarks -->
            <?php if ($center['remarks']): ?>
            <div class="section-title">📝 Center Remarks</div>
            <div style="border: 1px solid #ddd; padding: 12px; background: #f9f9f9; margin-bottom: 20px;">
                <?php echo nl2br(htmlspecialchars($center['remarks'])); ?>
            </div>
            <?php endif; ?>
            
            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Prepared By</div>
                    <div><?php echo htmlspecialchars($_SESSION['userid'] ?? 'System'); ?></div>
                    <div class="signature-label" style="font-size: 9px; margin-top: 5px;"><?php echo date('F d, Y'); ?></div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">LGU Coordinator</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Date Received</div>
                </div>
            </div>
            
            <div class="footer">
                This is an official inactivation report for the evacuation center. All data is accurate as of the generation date.
                <br>EvacFinder System - <?php echo date('Y'); ?>
            </div>
        </div>
    </div>
</body>
</html>
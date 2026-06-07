<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../models/centers.model.php';

if (!isset($_GET['center_id'])) {
    die('Center ID required');
}

$center_id = $_GET['center_id'];

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

// Get ALL evacuees (active and departed)
$stmt2 = $pdo->prepare("
    SELECT * FROM evacuees 
    WHERE evacuation_center_id = :center_id 
    ORDER BY arrival_date DESC
");
$stmt2->bindParam(":center_id", $center_id);
$stmt2->execute();
$allEvacuees = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Separate active and departed
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

$occupancy = $stats['active'];
$capacity = $center['capacity'];
$percentage = ($capacity > 0) ? round(($occupancy / $capacity) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Center Report - <?php echo htmlspecialchars($center['center_name']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; font-size: 12px; background: #f0f0f0; }
        .report-container { max-width: 1200px; margin: 0 auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .report-content { padding: 20px; }
        .header { text-align: center; padding: 20px; border-bottom: 2px solid #333; margin-bottom: 20px; }
        .header h1 { font-size: 24px; }
        .info-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; background: #f9f9f9; }
        .info-card table { width: 100%; border-collapse: collapse; }
        .info-card td { padding: 8px; vertical-align: top; }
        .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-grid td { border: 1px solid #ddd; padding: 12px; text-align: center; background: white; }
        .stats-grid h3 { font-size: 24px; margin: 0; color: #2c3e50; }
        .section-title { font-size: 16px; font-weight: bold; margin: 25px 0 15px; padding-bottom: 8px; border-bottom: 2px solid #ddd; }
        .evacuee-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
        .evacuee-table th, .evacuee-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .evacuee-table th { background: #f5f5f5; }
        .empty-state { text-align: center; padding: 30px; border: 1px solid #ddd; background: #fafafa; color: #666; }
        .signature-section { margin-top: 40px; display: flex; gap: 40px; }
        .signature-box { flex: 1; text-align: center; }
        .signature-line { border-top: 1px solid #000; margin: 30px auto 8px; width: 80%; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9px; }
        .btn-print { background: #2c3e50; color: white; padding: 10px 25px; border: none; cursor: pointer; margin-bottom: 20px; font-size: 12px; border-radius: 4px; }
        @media print { body { padding: 0; background: white; } .btn-print { display: none; } }
        .condition-tag { display: inline-block; padding: 2px 6px; border: 1px solid #ccc; font-size: 9px; background: #fafafa; border-radius: 3px; margin: 2px; }
        .center-status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>
    
    <div class="report-container">
        <div class="report-content">
            <div class="header">
                <h1>🏢 EVACUATION CENTER REPORT</h1>
                <p>Generated on: <?php echo date('F d, Y h:i A'); ?></p>
            </div>
            
            <div class="info-card">
                <table>
                    <tr><td style="width: 50%;"><strong>Center Name:</strong> <?php echo htmlspecialchars($center['center_name']); ?></span>
                        <td style="width: 50%;"><strong>Category:</strong> <?php echo htmlspecialchars($center['category']); ?></span>
                    </tr>
                    <tr><td><strong>Status:</strong> <span class="center-status-badge status-<?php echo strtolower($center['status']); ?>"><?php echo htmlspecialchars($center['status']); ?></span></span>
                        <td><strong>Capacity:</strong> <?php echo number_format($center['capacity']); ?> persons</span>
                    </tr>
                    <tr><td><strong>Location:</strong> <?php echo htmlspecialchars($center['barangay'] . ', ' . $center['city'] . ', ' . $center['province']); ?></span>
                        <td><strong>Current Occupancy:</strong> <?php echo number_format($occupancy); ?> / <?php echo number_format($capacity); ?> (<?php echo $percentage; ?>%)</span>
                    </tr>
                    <tr><td><strong>Contact:</strong> <?php echo htmlspecialchars($center['contact_number'] ?: 'N/A'); ?></span>
                        <td><strong>Contact Person:</strong> <?php echo htmlspecialchars($center['contact_person'] ?: 'N/A'); ?></span>
                    </tr>
                </table>
            </div>
            
            <div class="section-title">📊 Demographic Summary</div>
            <table class="stats-grid">
                <tr><td><h3><?php echo $stats['active']; ?></h3><p>Active</p></span><td><h3><?php echo $stats['departed']; ?></h3><p>Departed</p></span><td><h3><?php echo $stats['total']; ?></h3><p>Total Served</p></span><td><h3><?php echo $stats['male']; ?></h3><p>Male</p></span></tr>
                <tr><td><h3><?php echo $stats['female']; ?></h3><p>Female</p></span><td><h3><?php echo $stats['children']; ?></h3><p>Children</p></span><td><h3><?php echo $stats['elderly']; ?></h3><p>Elderly</p></span><td><h3><?php echo $stats['pwd']; ?></h3><p>PWD</p></span></tr>
                <tr><td><h3><?php echo $stats['pregnant']; ?></h3><p>Pregnant</p></span><td><h3><?php echo $stats['lactating']; ?></h3><p>Lactating</p></span><td colspan="2"></span></tr>
            </table>
            
            <div class="section-title">👥 Active Evacuees (<?php echo count($activeEvacuees); ?>)</div>
            <?php if (count($activeEvacuees) > 0): ?>
            <table class="evacuee-table">
                <thead><tr><th>#</th><th>Full Name</th><th>Age</th><th>Sex</th><th>Conditions</th><th>Arrival Date</th></tr></thead>
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
            <div class="empty-state">No active evacuees.</div>
            <?php endif; ?>
            
            <div class="section-title">🚪 Departed Evacuees (<?php echo count($departedEvacuees); ?>)</div>
            <?php if (count($departedEvacuees) > 0): ?>
            <table class="evacuee-table">
                <thead><tr><th>#</th><th>Full Name</th><th>Age</th><th>Sex</th><th>Conditions</th><th>Arrival</th><th>Departure</th></tr></thead>
                <tbody>
                    <?php foreach ($departedEvacuees as $index => $e): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($e['last_name'] . ', ' . $e['first_name']); ?></td>
                        <td><?php echo $e['age'] ?: 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($e['sex']); ?></td>
                        <td><?php
                            if ($e['condition_pregnant']) echo '<span class="condition-tag">Pregnant</span> ';
                            if ($e['condition_lactating']) echo '<span class="condition-tag">Lactating</span> ';
                            if ($e['condition_elderly']) echo '<span class="condition-tag">Elderly</span> ';
                            if ($e['condition_pwd']) echo '<span class="condition-tag">PWD</span> ';
                            if (!$e['condition_pregnant'] && !$e['condition_lactating'] && !$e['condition_elderly'] && !$e['condition_pwd']) echo 'None';
                        ?></td>
                        <td><?php echo date('M d, Y', strtotime($e['arrival_date'])); ?></td>
                        <td><?php echo $e['departure_date'] ? date('M d, Y', strtotime($e['departure_date'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No departed evacuees.</div>
            <?php endif; ?>
            
            <?php if ($center['remarks']): ?>
            <div class="section-title">📝 Remarks</div>
            <div style="border: 1px solid #ddd; padding: 12px; background: #f9f9f9;"><?php echo nl2br(htmlspecialchars($center['remarks'])); ?></div>
            <?php endif; ?>
            
            <div class="signature-section">
                <div class="signature-box"><div class="signature-line"></div><div class="signature-label">Prepared By</div><div><?php echo htmlspecialchars($_SESSION['userid'] ?? 'System'); ?></div></div>
                <div class="signature-box"><div class="signature-line"></div><div class="signature-label">Center Manager</div></div>
            </div>
            <div class="footer">EvacFinder System - <?php echo date('Y'); ?></div>
        </div>
    </div>
</body>
</html>
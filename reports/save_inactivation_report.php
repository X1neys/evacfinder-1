<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../models/centers.model.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$center_id = $_POST['center_id'] ?? '';

if (empty($center_id)) {
    echo json_encode(['success' => false, 'message' => 'Center ID required']);
    exit;
}

try {
    $db = new Connection();
    $pdo = $db->connect();
    $pdo->exec("SET time_zone = '+08:00'");
    
    // Get center info
    $stmt = $pdo->prepare("
        SELECT c.*, 
               CONCAT(l.first_name, ' ', l.last_name) as assigned_lgu_name
        FROM centers c
        LEFT JOIN userrights u ON c.assigned_lgu_user_id = u.userid
        LEFT JOIN lgu_users l ON u.email = l.office_email_address
        WHERE c.center_id = :center_id
    ");
    $stmt->bindParam(":center_id", $center_id);
    $stmt->execute();
    $center = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$center) {
        echo json_encode(['success' => false, 'message' => 'Center not found']);
        exit;
    }
    
    // Get ALL evacuees for this center
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
    $activeDate = !empty($center['date_established']) && $center['date_established'] != '0000-00-00' 
        ? date('F d, Y', strtotime($center['date_established'])) 
        : 'Not specified';
    
    // Create reports directory if not exists
    $reportsDir = __DIR__ . '/saved_reports';
    if (!file_exists($reportsDir)) {
        mkdir($reportsDir, 0777, true);
    }
    
    // Generate unique filename
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "inactivation_report_{$center['center_id']}_{$timestamp}.html";
    $filepath = $reportsDir . '/' . $filename;
    
    // Generate HTML content
    $html = generateReportHTML($center, $stats, $allEvacuees, $activeEvacuees, $departedEvacuees, $occupancy, $capacity, $percentage, $activeDate);
    
    // Save file
    file_put_contents($filepath, $html);
    
    // Also save to database for tracking if table exists
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'saved_reports'");
        if ($checkTable->rowCount() > 0) {
            $saveSql = "INSERT INTO saved_reports (report_id, center_id, center_name, report_type, file_path, generated_by, generated_at) 
                        VALUES (:report_id, :center_id, :center_name, 'Inactivation', :file_path, :generated_by, NOW())";
            $report_id = 'RPT_' . date('Ymd_His') . '_' . uniqid();
            
            $saveStmt = $pdo->prepare($saveSql);
            $saveStmt->execute([
                ':report_id' => $report_id,
                ':center_id' => $center['center_id'],
                ':center_name' => $center['center_name'],
                ':file_path' => $filename,
                ':generated_by' => $_SESSION['userid'] ?? 'System'
            ]);
            $response['report_id'] = $report_id;
        }
    } catch (Exception $e) {
        // Table doesn't exist, just skip database save
    }
    
    $response['success'] = true;
    $response['message'] = 'Inactivation report saved successfully';
    $response['file'] = $filename;
    $response['file_path'] = 'reports/saved_reports/' . $filename;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);

function generateReportHTML($center, $stats, $allEvacuees, $activeEvacuees, $departedEvacuees, $occupancy, $capacity, $percentage, $activeDate) {
    ob_start();
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
            .info-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; background: #f9f9f9; }
            .info-card table { width: 100%; border-collapse: collapse; }
            .info-card td { padding: 8px; vertical-align: top; }
            .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .stats-grid td { border: 1px solid #ddd; padding: 12px; text-align: center; background: white; }
            .stats-grid h3 { font-size: 24px; margin: 0; color: #2c3e50; }
            .section-title { font-size: 16px; font-weight: bold; margin: 25px 0 15px; padding-bottom: 8px; border-bottom: 2px solid #ddd; color: #2c3e50; }
            .evacuee-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px; }
            .evacuee-table th, .evacuee-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .evacuee-table th { background: #f5f5f5; }
            .empty-state { text-align: center; padding: 30px; border: 1px solid #ddd; background: #fafafa; color: #666; }
            .signature-section { margin-top: 40px; display: flex; gap: 40px; }
            .signature-box { flex: 1; text-align: center; }
            .signature-line { border-top: 1px solid #000; margin: 30px auto 8px; width: 80%; }
            .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9px; color: #777; }
            .inactivation-box { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin-bottom: 20px; text-align: center; border-radius: 5px; }
            .inactivation-box h3 { color: #721c24; margin-bottom: 10px; }
            .condition-tag { display: inline-block; padding: 2px 6px; border: 1px solid #ccc; font-size: 9px; background: #fafafa; border-radius: 3px; margin: 2px; }
            .conditions-list { display: flex; flex-wrap: wrap; gap: 4px; }
            .center-status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; }
            .status-inactive { background: #f8d7da; color: #721c24; }
        </style>
    </head>
    <body>
        <div class="report-container">
            <div class="report-content">
                <div class="header">
                    <h1>🏢 EVACUATION CENTER INACTIVATION REPORT</h1>
                    <h2>⚠️ CENTER CLOSURE / INACTIVATION NOTICE</h2>
                    <p>Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                </div>
                
                <div class="inactivation-box">
                    <h3>⚠️ THIS CENTER HAS BEEN OFFICIALLY INACTIVATED</h3>
                    <p>The evacuation center is no longer accepting evacuees. All active evacuees have been marked as DEPARTED.</p>
                    <p><strong>Inactivation Date & Time:</strong> <?php echo date('F d, Y h:i A'); ?></p>
                    <p><strong>Inactivated By:</strong> <?php echo htmlspecialchars($_SESSION['userid'] ?? 'System'); ?></p>
                </div>
                
                <div class="info-card">
                    <table>
                        <tr><td style="width: 50%;"><strong>Center Name:</strong> <?php echo htmlspecialchars($center['center_name']); ?></span>
                            <td style="width: 50%;"><strong>Category:</strong> <?php echo htmlspecialchars($center['category']); ?></span>
                        </tr>
                        <tr><td><strong>Final Status:</strong> <span class="center-status-badge status-inactive">INACTIVE</span></span>
                            <td><strong>Original Capacity:</strong> <?php echo number_format($center['capacity']); ?> persons</span>
                        </tr>
                        <tr><td><strong>Location:</strong> <?php echo htmlspecialchars($center['barangay'] . ', ' . $center['city'] . ', ' . $center['province']); ?></span>
                            <td><strong>Contact:</strong> <?php echo htmlspecialchars($center['contact_number'] ?: 'N/A'); ?></span>
                        </tr>
                        <tr><td colspan="2"><strong>Contact Person:</strong> <?php echo htmlspecialchars($center['contact_person'] ?: 'N/A'); ?></span></tr>
                    </table>
                </div>
                
                <div class="section-title">📊 Final Summary Statistics</div>
                <table class="stats-grid">
                    <tr><td><h3><?php echo $stats['total']; ?></h3><p>Total Evacuees Served</p></span>
                        <td><h3><?php echo $stats['active']; ?></h3><p>Active at Inactivation</p></span>
                        <td><h3><?php echo $stats['departed']; ?></h3><p>Previously Departed</p></span>
                        <td><h3><?php echo $stats['male']; ?></h3><p>Male</p></span>
                    </tr>
                    <tr><td><h3><?php echo $stats['female']; ?></h3><p>Female</p></span>
                        <td><h3><?php echo $stats['children']; ?></h3><p>Children</p></span>
                        <td><h3><?php echo $stats['elderly']; ?></h3><p>Elderly</p></span>
                        <td><h3><?php echo $stats['pwd']; ?></h3><p>PWD</p></span>
                    </tr>
                    <tr><td><h3><?php echo $stats['pregnant']; ?></h3><p>Pregnant</p></span>
                        <td><h3><?php echo $stats['lactating']; ?></h3><p>Lactating</p></span>
                        <td colspan="2"></span>
                    </tr>
                </table>
                
                <div class="section-title">👥 Active Evacuees at Time of Inactivation (<?php echo count($activeEvacuees); ?> evacuees)</div>
                <?php if (count($activeEvacuees) > 0): ?>
                <table class="evacuee-table">
                    <thead><tr><th>#</th><th>Full Name</th><th>Age</th><th>Sex</th><th>Special Conditions</th><th>Arrival Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($activeEvacuees as $index => $e): ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($e['last_name'] . ', ' . $e['first_name']); ?></td>
                            <td style="text-align: center;"><?php echo $e['age'] ?: 'N/A'; ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($e['sex']); ?></td>
                            <td><div class="conditions-list"><?php
                                if ($e['condition_pregnant']) echo '<span class="condition-tag">🤰 Pregnant</span>';
                                if ($e['condition_lactating']) echo '<span class="condition-tag">🍼 Lactating</span>';
                                if ($e['condition_elderly']) echo '<span class="condition-tag">👴 Elderly</span>';
                                if ($e['condition_pwd']) echo '<span class="condition-tag">♿ PWD</span>';
                                if (!$e['condition_pregnant'] && !$e['condition_lactating'] && !$e['condition_elderly'] && !$e['condition_pwd']) echo 'None';
                            ?></div></td>
                            <td style="text-align: center;"><?php echo date('M d, Y', strtotime($e['arrival_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">📭 No active evacuees at time of inactivation.</div>
                <?php endif; ?>
                
                <div class="section-title">📋 All Evacuees Served (Total: <?php echo $stats['total']; ?>)</div>
                <table class="evacuee-table">
                    <thead><tr><th>#</th><th>Full Name</th><th>Age</th><th>Sex</th><th>Status</th><th>Arrival Date</th><th>Departure Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($allEvacuees as $index => $e): ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($e['last_name'] . ', ' . $e['first_name']); ?></td>
                            <td style="text-align: center;"><?php echo $e['age'] ?: 'N/A'; ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($e['sex']); ?></td>
                            <td style="text-align: center;"><?php echo $e['evacuee_status']; ?></td>
                            <td style="text-align: center;"><?php echo date('M d, Y', strtotime($e['arrival_date'])); ?></td>
                            <td style="text-align: center;"><?php echo $e['departure_date'] ? date('M d, Y', strtotime($e['departure_date'])) : 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">Prepared By</div>
                        <div><?php echo htmlspecialchars($_SESSION['userid'] ?? 'System'); ?></div>
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
                    This is an official inactivation report for the evacuation center. EvacFinder System - <?php echo date('Y'); ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>
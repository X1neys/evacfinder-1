<?php
// ajax/inactivate_center.ajax.php
session_start();
require_once "../config/connection.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['center_id'])) {
    echo json_encode(['success' => false, 'message' => 'Center ID is required']);
    exit;
}

$center_id = $_POST['center_id'];
$performed_by_id = $_SESSION['userid'] ?? 'System';

// Get user full name from userrights table
$performed_by_name = 'System';
try {
    $db_temp = new Connection();
    $pdo_temp = $db_temp->connect();
    $stmt_user = $pdo_temp->prepare("SELECT * FROM userrights WHERE userid = :userid");
    $stmt_user->execute([':userid' => $performed_by_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check if user is LGU or Public
        if ($user['Type'] == 'lgu') {
            $stmt_lgu = $pdo_temp->prepare("SELECT * FROM lgu_users WHERE lgu_id = :lgu_id");
            $stmt_lgu->execute([':lgu_id' => $performed_by_id]);
            $lgu = $stmt_lgu->fetch(PDO::FETCH_ASSOC);
            if ($lgu) {
                $performed_by_name = $lgu['first_name'] . ' ' . $lgu['last_name'];
            } else {
                $performed_by_name = $performed_by_id;
            }
        } elseif ($user['Type'] == 'public') {
            $stmt_public = $pdo_temp->prepare("SELECT * FROM personal_users WHERE user_id = :user_id");
            $stmt_public->execute([':user_id' => $performed_by_id]);
            $public = $stmt_public->fetch(PDO::FETCH_ASSOC);
            if ($public) {
                $performed_by_name = $public['first_name'] . ' ' . $public['last_name'];
            } else {
                $performed_by_name = $performed_by_id;
            }
        } else {
            $performed_by_name = $performed_by_id;
        }
    }
} catch (Exception $e) {
    $performed_by_name = $performed_by_id;
}

try {
    $db = new Connection();
    $pdo = $db->connect();
    $pdo->beginTransaction();

    // Get center details first
    $stmt = $pdo->prepare("SELECT * FROM centers WHERE center_id = :center_id");
    $stmt->execute([':center_id' => $center_id]);
    $center = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$center) {
        throw new Exception('Center not found');
    }

    if ($center['status'] === 'Inactive') {
        throw new Exception('Center is already inactive');
    }

    // Get ALL evacuees (not just active) for complete history
    $stmt = $pdo->prepare("SELECT * FROM evacuees WHERE evacuation_center_id = :center_id");
    $stmt->execute([':center_id' => $center_id]);
    $allEvacuees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get active evacuees only
    $stmt = $pdo->prepare("SELECT * FROM evacuees WHERE evacuation_center_id = :center_id AND evacuee_status = 'Active'");
    $stmt->execute([':center_id' => $center_id]);
    $activeEvacuees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $evacueeCount = count($activeEvacuees);

    // Update ALL active evacuees to Departed
    if ($evacueeCount > 0) {
        $departure_date = date('Y-m-d');
        $updateStmt = $pdo->prepare("UPDATE evacuees SET evacuee_status = 'Departed', departure_date = :departure_date WHERE evacuation_center_id = :center_id AND evacuee_status = 'Active'");
        $updateStmt->execute([
            ':departure_date' => $departure_date,
            ':center_id' => $center_id
        ]);

        // Log each evacuee departure in center_history
        foreach ($activeEvacuees as $evacuee) {
            $historyStmt = $pdo->prepare("INSERT INTO center_history (center_id, action_type, description, performed_by) VALUES (:center_id, 'EVACUEE_DEPARTED', :description, :performed_by)");
            $historyStmt->execute([
                ':center_id' => $center_id,
                ':description' => "Evacuee {$evacuee['first_name']} {$evacuee['last_name']} was automatically departed due to center inactivation",
                ':performed_by' => $performed_by_id
            ]);
        }
    }

    // Update center status to Inactive and clear current_occupants
    $updateCenter = $pdo->prepare("UPDATE centers SET status = 'Inactive', current_occupants = 0 WHERE center_id = :center_id");
    $updateCenter->execute([':center_id' => $center_id]);

    // Insert into center_status_history
    $statusHistory = $pdo->prepare("INSERT INTO center_status_history (center_id, old_status, new_status, changed_by) VALUES (:center_id, :old_status, 'Inactive', :changed_by)");
    $statusHistory->execute([
        ':center_id' => $center_id,
        ':old_status' => $center['status'],
        ':changed_by' => $performed_by_id
    ]);

    // Log center inactivation in center_history
    $evacueeMessage = $evacueeCount > 0 ? "{$evacueeCount} evacuee(s) were automatically departed." : "No evacuees to clear.";
    $historyStmt = $pdo->prepare("INSERT INTO center_history (center_id, action_type, description, performed_by) VALUES (:center_id, 'CENTER_INACTIVATED', :description, :performed_by)");
    $historyStmt->execute([
        ':center_id' => $center_id,
        ':description' => "Center manually inactivated. {$evacueeMessage} Active evacuees before inactivation: {$evacueeCount}",
        ':performed_by' => $performed_by_id
    ]);

    // Create reports directory if not exists
    $report_dir = "../reports/inactivation_reports/";
    if (!file_exists($report_dir)) {
        mkdir($report_dir, 0777, true);
    }

    // Generate simple sequential report ID
    // Get the last report number
    $last_report_stmt = $pdo->query("SELECT report_id FROM saved_reports WHERE report_id LIKE 'RPT-%' ORDER BY id DESC LIMIT 1");
    $last_report = $last_report_stmt->fetch(PDO::FETCH_ASSOC);
    
    $last_number = 0;
    if ($last_report) {
        preg_match('/RPT-(\d+)/', $last_report['report_id'], $matches);
        if (isset($matches[1])) {
            $last_number = intval($matches[1]);
        }
    }
    
    $new_number = $last_number + 1;
    $report_id = 'RPT-' . str_pad($new_number, 5, '0', STR_PAD_LEFT);
    
    $report_filename = $report_id . '.html';
    $report_path = $report_dir . $report_filename;
    
    // Store relative path for web access
    $web_report_path = "reports/inactivation_reports/" . $report_filename;

    // Generate nice HTML report
    $html = generateNiceInactivationReport($center, $allEvacuees, $activeEvacuees, $evacueeCount, $performed_by_name, $performed_by_id, $report_id);
    file_put_contents($report_path, $html);

    // SAVE TO saved_reports TABLE
    $saveReport = $pdo->prepare("INSERT INTO saved_reports (report_id, center_id, center_name, report_type, file_path, generated_by, generated_at) VALUES (:report_id, :center_id, :center_name, 'Inactivation', :file_path, :generated_by, NOW())");
    $saveReport->execute([
        ':report_id' => $report_id,
        ':center_id' => $center_id,
        ':center_name' => $center['center_name'],
        ':file_path' => $web_report_path,
        ':generated_by' => $performed_by_name
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Center has been inactivated. {$evacueeCount} evacuee(s) have been marked as departed.",
        'report_url' => $web_report_path,
        'report_id' => $report_id,
        'evacuees_cleared' => $evacueeCount
    ]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function generateNiceInactivationReport($center, $allEvacuees, $activeEvacuees, $evacueeCount, $performed_by_name, $performed_by_id, $report_id) {
    $date = date('F d, Y g:i A');
    $totalEvacueesEver = count($allEvacuees);
    
    // Calculate statistics
    $totalMales = 0;
    $totalFemales = 0;
    $totalChildren = 0; // under 18
    $totalSeniors = 0; // 60 and above
    $totalPWD = 0;
    $totalPregnant = 0;
    $totalLactating = 0;
    $total4ps = 0;
    
    foreach ($allEvacuees as $e) {
        if ($e['sex'] == 'Male') $totalMales++;
        if ($e['sex'] == 'Female') $totalFemales++;
        if ($e['age'] !== null && $e['age'] !== '' && $e['age'] < 18) $totalChildren++;
        if ($e['age'] !== null && $e['age'] !== '' && $e['age'] >= 60) $totalSeniors++;
        if ($e['condition_pwd'] == 1) $totalPWD++;
        if ($e['condition_pregnant'] == 1) $totalPregnant++;
        if ($e['condition_lactating'] == 1) $totalLactating++;
        if ($e['condition_4ps'] == 1) $total4ps++;
    }
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>Inactivation Report - ' . htmlspecialchars($center['center_name']) . '</title>
        <meta charset="UTF-8">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "Segoe UI", Arial, sans-serif;
                background: #f0f2f5;
                padding: 40px;
                color: #333;
            }
            
            .report-container {
                max-width: 1100px;
                margin: 0 auto;
                background: white;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .report-header {
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                color: white;
                padding: 30px 40px;
                text-align: center;
            }
            
            .report-header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }
            
            .report-header p {
                opacity: 0.9;
                font-size: 14px;
            }
            
            .report-body {
                padding: 40px;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .info-card {
                background: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                text-align: center;
                border-top: 4px solid #dc3545;
            }
            
            .info-card h3 {
                font-size: 12px;
                text-transform: uppercase;
                color: #6c757d;
                margin-bottom: 10px;
                letter-spacing: 1px;
            }
            
            .info-card .value {
                font-size: 20px;
                font-weight: bold;
                color: #dc3545;
                word-break: break-all;
            }
            
            .info-card .label {
                font-size: 11px;
                color: #6c757d;
                margin-top: 8px;
            }
            
            .section {
                margin-bottom: 30px;
            }
            
            .section-title {
                font-size: 18px;
                font-weight: bold;
                padding-bottom: 10px;
                border-bottom: 2px solid #e9ecef;
                margin-bottom: 20px;
                color: #495057;
            }
            
            .section-title i {
                margin-right: 10px;
                color: #dc3545;
            }
            
            .center-details {
                background: #f8f9fa;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .center-details table {
                width: 100%;
            }
            
            .center-details td {
                padding: 8px;
                vertical-align: top;
            }
            
            .center-details td:first-child {
                width: 35%;
                font-weight: 600;
                color: #495057;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                margin-bottom: 20px;
            }
            
            .stat-box {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 15px;
                text-align: center;
            }
            
            .stat-number {
                font-size: 28px;
                font-weight: bold;
                color: #dc3545;
            }
            
            .stat-label {
                font-size: 11px;
                color: #6c757d;
                margin-top: 5px;
            }
            
            table.data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            
            table.data-table th,
            table.data-table td {
                border: 1px solid #dee2e6;
                padding: 10px;
                text-align: left;
                font-size: 13px;
            }
            
            table.data-table th {
                background: #f8f9fa;
                font-weight: 600;
                color: #495057;
            }
            
            table.data-table tr:hover {
                background: #f8f9fa;
            }
            
            .badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
            }
            
            .badge-departed {
                background: #dc3545;
                color: white;
            }
            
            .summary-box {
                background: #fff3cd;
                border: 1px solid #ffecb5;
                border-radius: 12px;
                padding: 20px;
                margin-top: 20px;
            }
            
            .summary-box h3 {
                color: #856404;
                margin-bottom: 15px;
            }
            
            .footer {
                background: #f8f9fa;
                padding: 20px 40px;
                text-align: center;
                font-size: 12px;
                color: #6c757d;
                border-top: 1px solid #e9ecef;
            }
            
            @media (max-width: 768px) {
                body {
                    padding: 20px;
                }
                .report-body {
                    padding: 20px;
                }
                .info-grid {
                    grid-template-columns: 1fr;
                }
                .stats-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
        </style>
    </head>
    <body>
        <div class="report-container">
            <div class="report-header">
                <h1>🏢 CENTER INACTIVATION REPORT</h1>
                <p>Official Inactivation Document</p>
            </div>
            
            <div class="report-body">
                <!-- Report Info -->
                <div class="info-grid">
                    <div class="info-card">
                        <h3>REPORT ID</h3>
                        <div class="value">' . htmlspecialchars($report_id) . '</div>
                        <div class="label">Unique identifier for this report</div>
                    </div>
                    <div class="info-card">
                        <h3>GENERATED BY</h3>
                        <div class="value">' . htmlspecialchars($performed_by_name) . '</div>
                        <div class="label">User who performed inactivation</div>
                    </div>
                    <div class="info-card">
                        <h3>INACTIVATION DATE</h3>
                        <div class="value">' . date('M d, Y', strtotime($date)) . '</div>
                        <div class="label">' . date('h:i A', strtotime($date)) . '</div>
                    </div>
                </div>
                
                <!-- Center Information -->
                <div class="section">
                    <div class="section-title">
                        <i>📍</i> Center Information
                    </div>
                    <div class="center-details">
                        <table>
                            <tr><td style="width: 35%;"><strong>Center Name:</strong></td>
                            <td>' . htmlspecialchars($center['center_name']) . '</td>
                            </tr>
                            <tr><td><strong>Center ID:</strong></td>
                            <td>' . htmlspecialchars($center['center_id']) . '</td>
                            </tr>
                            <tr><td><strong>Category:</strong></td>
                            <td>' . htmlspecialchars($center['category']) . '</td>
                            </tr>
                            <tr><td><strong>Location:</strong></td>
                            <td>' . htmlspecialchars($center['barangay'] . ', ' . $center['city'] . ', ' . $center['province']) . '</td>
                            </tr>
                            <tr><td><strong>Full Address:</strong></td>
                            <td>' . htmlspecialchars($center['address'] ?: 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>Contact Number:</strong></td>
                            <td>' . htmlspecialchars($center['contact_number'] ?: 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>Contact Person:</strong></td>
                            <td>' . htmlspecialchars($center['contact_person'] ?: 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>Original Capacity:</strong></td>
                            <td>' . number_format($center['capacity']) . ' persons</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Evacuee Statistics -->
                <div class="section">
                    <div class="section-title">
                        <i>👥</i> Evacuee Demographics
                    </div>
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-number">' . $totalEvacueesEver . '</div>
                            <div class="stat-label">Total Served</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $evacueeCount . '</div>
                            <div class="stat-label">Active at Inactivation</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $totalMales . '</div>
                            <div class="stat-label">Male</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $totalFemales . '</div>
                            <div class="stat-label">Female</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $totalChildren . '</div>
                            <div class="stat-label">Children (Under 18)</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $totalSeniors . '</div>
                            <div class="stat-label">Seniors (60+)</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $totalPWD . '</div>
                            <div class="stat-label">PWD</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">' . $totalPregnant . '</div>
                            <div class="stat-label">Pregnant</div>
                        </div>
                    </div>
                </div>';
    
    // Active Evacuees Table
    if ($evacueeCount > 0) {
        $html .= '<div class="section">
                    <div class="section-title">
                        <i>📋</i> Cleared Evacuees (' . $evacueeCount . ')
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Evacuee ID</th>
                                <th>Full Name</th>
                                <th>Sex</th>
                                <th>Age</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($activeEvacuees as $evacuee) {
            $html .= '<tr>
                            <td>' . htmlspecialchars($evacuee['evacuee_id']) . '</td>
                            <td>' . htmlspecialchars($evacuee['last_name'] . ', ' . $evacuee['first_name']) . ' ' . htmlspecialchars($evacuee['middle_name'] ?? '') . '</td>
                            <td>' . htmlspecialchars($evacuee['sex'] ?: 'N/A') . '</td>
                            <td>' . htmlspecialchars($evacuee['age'] ?: 'N/A') . '</td>
                            <td><span class="badge badge-departed">Departed</span></td>
                         </tr>';
        }
        
        $html .= '</tbody>
                    </table>
                </div>';
    } else {
        $html .= '<div class="summary-box" style="background: #d4edda; border-color: #c3e6cb;">
                    <p style="margin: 0; text-align: center;">✅ <strong>No Active Evacuees</strong> - The center had no active evacuees at the time of inactivation.</p>
                </div>';
    }
    
    // Facility Information
    $html .= '<div class="section">
                    <div class="section-title">
                        <i>🏗️</i> Facility Information
                    </div>
                    <div class="center-details">
                        <table>
                            <tr><td style="width: 35%;"><strong>Water Supply:</strong></td>
                            <td>' . htmlspecialchars($center['water_supply'] ?? 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>Electricity:</strong></td>
                            <td>' . htmlspecialchars($center['electricity'] ?? 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>Number of Rooms:</strong></td>
                            <td>' . htmlspecialchars($center['num_rooms'] ?? 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>Restrooms:</strong></td>
                            <td>' . htmlspecialchars($center['restrooms_count'] ?? 'N/A') . '</td>
                            </tr>
                            <tr><td><strong>WiFi Available:</strong></td>
                            <td>' . (($center['has_wifi'] ?? 0) ? '✓ Yes' : '✗ No') . '</td>
                            </tr>
                            <tr><td><strong>Canteen Available:</strong></td>
                            <td>' . (($center['has_canteen'] ?? 0) ? '✓ Yes' : '✗ No') . '</td>
                            </tr>
                            <tr><td><strong>Medical Station:</strong></td>
                            <td>' . (($center['has_medical'] ?? 0) ? '✓ Yes' : '✗ No') . '</td>
                            </tr>
                        </table>
                    </div>
                </div>';
    
    // Remarks if any
    if (!empty($center['remarks'])) {
        $html .= '<div class="section">
                    <div class="section-title">
                        <i>📝</i> Remarks
                    </div>
                    <div class="center-details">
                        <p>' . nl2br(htmlspecialchars($center['remarks'])) . '</p>
                    </div>
                </div>';
    }
    
    // Summary
    $html .= '<div class="summary-box">
                    <h3>📊 Inactivation Summary</h3>
                    <p><strong>Status:</strong> <span class="badge badge-departed">INACTIVE</span></p>
                    <p><strong>Total Evacuees Cleared:</strong> ' . $evacueeCount . '</p>
                    <p><strong>Action Taken:</strong> Center has been manually inactivated. All active evacuees have been marked as departed.</p>
                    <p><strong>Next Steps:</strong> To reactivate this center, please use the "Schedule Activation" or manual status change option.</p>
                </div>
            </div>
            
            <div class="footer">
                <p>This is an official inactivation report generated by EvacFinder Evacuation Management System</p>
                <p>Generated on: ' . $date . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>
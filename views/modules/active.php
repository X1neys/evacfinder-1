<?php
date_default_timezone_set('Asia/Manila');

// Use your existing model
require_once "models/centers.model.php";

// Function to check scheduled activations on page load
function checkScheduledActivationsOnLoad() {
    try {
        $db = new Connection();
        $pdo = $db->connect();
        $pdo->exec("SET time_zone = '+08:00'");
    } catch (Exception $e) {
        return 0;
    }
    
    // Check if center_schedules table exists
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'center_schedules'");
        if ($checkTable->rowCount() == 0) {
            return 0;
        }
    } catch (Exception $e) {
        return 0;
    }
    
    $sql = "SELECT cs.*, c.center_name 
            FROM center_schedules cs
            JOIN centers c ON cs.center_id = c.center_id
            WHERE cs.status = 'Pending' 
            AND cs.scheduled_datetime <= NOW()";
    
    $stmt = $pdo->query($sql);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $activated = 0;
    
    foreach ($schedules as $schedule) {
        try {
            $pdo->beginTransaction();
            
            $additionalInfo = json_decode($schedule['additional_info'], true);
            $newCapacity = $additionalInfo['capacity'] ?? $schedule['capacity'] ?? 100;
            
            $updateSql = "UPDATE centers SET 
                          status = 'Active',
                          capacity = :capacity,
                          water_supply = :water_supply,
                          electricity = :electricity,
                          num_rooms = :num_rooms,
                          has_wifi = :has_wifi,
                          has_canteen = :has_canteen,
                          has_medical = :has_medical,
                          restrooms_count = :restrooms_count
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
                ':center_id' => $schedule['center_id']
            ]);
            
            $updateSchedule = $pdo->prepare("UPDATE center_schedules 
                                            SET status = 'Executed', executed_at = NOW() 
                                            WHERE schedule_id = :schedule_id");
            $updateSchedule->execute([':schedule_id' => $schedule['schedule_id']]);
            
            $pdo->commit();
            $activated++;
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
    return $activated;
}

$activated_on_load = checkScheduledActivationsOnLoad();

$summary = ModelCenters::mdlGetCenterSummary();
$allCenters = ModelCenters::mdlGetAllCenters();

// Get default datetime for schedule (1 hour from now in PH time)
$default_datetime = date('Y-m-d\TH:i', strtotime('+1 hour'));
?>

<div class="home-dashboard">
<div class="container-fluid flex-grow-1 container-p-y">
    <?php if($activated_on_load > 0): ?>
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fa fa-check-circle me-2"></i>
        <strong><?php echo $activated_on_load; ?> center(s) automatically activated!</strong> 
        Scheduled activations have been processed.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 stats-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-uppercase text-primary fw-bold">Total Centers</p>
                        <h3 class="mb-0"><?php echo number_format($summary['total_centers']); ?></h3>
                    </div>
                    <div class="stats-card-icon stats-card-icon-primary text-white rounded-3">
                        <i class="fa fa-home"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 stats-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-uppercase text-success fw-bold">Active Capacity</p>
                        <h3 class="mb-0"><?php echo number_format($summary['total_capacity']); ?></h3>
                    </div>
                    <div class="stats-card-icon stats-card-icon-success text-white rounded-3">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 stats-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-uppercase text-warning fw-bold">Currently Occupied</p>
                        <h3 class="mb-0" id="currentlyOccupiedCount"><?php echo number_format(max(0, $summary['currently_occupied'])); ?></h3>
                    </div>
                    <div class="stats-card-icon stats-card-icon-warning text-white rounded-3">
                        <i class="fa fa-bed"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 stats-card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-uppercase text-info fw-bold">Active Centers</p>
                        <h3 class="mb-0"><?php echo number_format($summary['active_centers']); ?></h3>
                    </div>
                    <div class="stats-card-icon stats-card-icon-info text-white rounded-3">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Evacuation Centers</h5>
                    <div>
                        <a href="?route=centers" class="btn btn-primary btn-sm me-2">
                            <i class="fa fa-plus"></i> Add Evacuation Center
                        </a>
                        <button type="button" class="btn btn-info btn-sm me-2" id="viewSchedulesBtn">
                            <i class="fa fa-calendar-alt"></i> View Schedules
                        </button>
                        <button type="button" class="btn btn-warning btn-sm me-2" id="checkActivationsNow">
                            <i class="fa fa-clock-o"></i> Check Now
                        </button>
                        <a href="?route=active" class="btn btn-secondary btn-sm">
                            <i class="fa fa-refresh"></i> Refresh
                        </a>
                    </div>
                </div>
                
                <div class="card-body border-bottom bg-light pb-3 pt-3">
                    <div class="row g-2">
                        <div class="col-md-1">
                            <button type="button" id="resetCenterFilters" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters">
                                <i class="fa fa-undo"></i> Clear
                            </button>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" id="searchCenterFilter" class="form-control" placeholder="Search by Center Name or Location...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending for Activation">Pending for Activation</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="categoryFilter" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="School">School</option>
                                <option value="Gymnasium / Sports Complex">Gymnasium / Sports Complex</option>
                                <option value="Church">Church</option>
                                <option value="Community Center / Multipurpose Hall">Community Center / Multipurpose Hall</option>
                                <option value="Covered Court">Covered Court</option>
                                <option value="Private Facility / Commercial Building">Private Facility / Commercial Building</option>
                                <option value="Open Field / Evacuation Ground">Open Field / Evacuation Ground</option>
                            </select>
                        </div>                        
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="centersTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30px;"></th>
                                    <th>Center Name</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Current Occupancy</th>
                                    <th>Status</th>
                                    <th style="width: 60px;">Print</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($allCenters) > 0): ?>
                                    <?php foreach($allCenters as $center): ?>
                                    <?php
                                        $statusText = htmlspecialchars($center['status']);
                                        $badgeClass = 'bg-secondary';
                                        
                                        $db = new Connection();
                                        $pdo = $db->connect();
                                        
                                        // Check if center has pending schedule
                                        $hasPendingSchedule = false;
                                        $pendingScheduleTime = '';
                                        try {
                                            $stmtPending = $pdo->prepare("SELECT scheduled_datetime FROM center_schedules 
                                                                           WHERE center_id = :center_id AND status = 'Pending' 
                                                                           AND scheduled_datetime > NOW() 
                                                                           ORDER BY scheduled_datetime ASC LIMIT 1");
                                            $stmtPending->bindParam(":center_id", $center['center_id']);
                                            $stmtPending->execute();
                                            $pendingSchedule = $stmtPending->fetch(PDO::FETCH_ASSOC);
                                            if ($pendingSchedule) {
                                                $hasPendingSchedule = true;
                                                $pendingScheduleTime = date('M d, Y h:i A', strtotime($pendingSchedule['scheduled_datetime']));
                                                $statusText = 'Pending for Activation';
                                                $badgeClass = 'bg-info';
                                            }
                                        } catch (Exception $e) {
                                            // Table might not exist yet
                                        }
                                        
                                        if ($statusText === 'Active') {
                                            $badgeClass = 'bg-success';
                                        } elseif ($statusText === 'Inactive') {
                                            $badgeClass = 'bg-warning';
                                        }
                                        
                                        $stmt = $pdo->prepare("SELECT * FROM centers WHERE center_id = :center_id");
                                        $stmt->bindParam(":center_id", $center['center_id']);
                                        $stmt->execute();
                                        $fullCenter = $stmt->fetch(PDO::FETCH_ASSOC);
                                        
                                        $stmtCount = $pdo->prepare("SELECT COUNT(*) as active_count FROM evacuees WHERE evacuation_center_id = :center_id AND evacuee_status = 'Active'");
                                        $stmtCount->bindParam(":center_id", $center['center_id']);
                                        $stmtCount->execute();
                                        $activeCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
                                        $actualOccupancy = $activeCount['active_count'];
                                    ?>
                                    <tr class="center-row" data-center-id="<?php echo $center['center_id']; ?>" style="cursor: pointer;">
                                        <td class="expand-icon text-center">
                                            <i class="fa fa-chevron-right"></i>
                                        </span>
                                        <td><?php echo htmlspecialchars($center['center_name']); ?></span>
                                        <td><?php echo htmlspecialchars($center['category']); ?></span>
                                        <td><?php echo htmlspecialchars(trim($center['barangay'] . ', ' . $center['city'] . ', ' . $center['province'])); ?></span>
                                        <td class="capacity-cell">
                                            <span class="capacity-display-<?php echo $center['center_id']; ?>"><?php echo number_format($center['capacity']); ?></span>
                                        </span>
                                        <td class="occupancy-cell">
                                            <span class="occupancy-display-<?php echo $center['center_id']; ?>"><?php echo number_format($actualOccupancy); ?></span>
                                        </span>
                                        <td>
                                            <?php if($hasPendingSchedule): ?>
                                                <span class="badge <?php echo $badgeClass; ?> status-badge-<?php echo $center['center_id']; ?>" 
                                                      style="cursor: pointer;" 
                                                      onclick="showPendingInfo('<?php echo addslashes($center['center_name']); ?>', '<?php echo $pendingScheduleTime; ?>')">
                                                    <?php echo $statusText; ?>
                                                    <i class="fa fa-clock-o" style="font-size: 8px; margin-left: 4px;"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge <?php echo $badgeClass; ?> status-badge-<?php echo $center['center_id']; ?> status-clickable" 
                                                      onclick="openChangeStatusModal('<?php echo $center['center_id']; ?>', '<?php echo addslashes($center['center_name']); ?>', '<?php echo $statusText; ?>')"
                                                      style="cursor: pointer;">
                                                    <?php echo $statusText; ?>
                                                    <i class="fa fa-pencil" style="font-size: 8px; margin-left: 4px;"></i>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-secondary print-report" 
                                                    data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"
                                                    title="Print Center Report">
                                                <i class="fa fa-print"></i>
                                            </button>
                                        </span>
                                    </tr>
                                    <tr class="details-row-<?php echo $center['center_id']; ?> details-row" style="display: none;">
                                        <td colspan="8" class="p-0">
                                            <div class="card-body bg-light p-3">
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <div class="btn-group" role="group">
                                                            <button id="editcenter" type="button" class="btn btn-sm btn-primary edit-center-dropdown" 
                                                                    data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"
                                                                    data-center-name="<?php echo htmlspecialchars($center['center_name']); ?>"
                                                                    data-category="<?php echo htmlspecialchars($center['category']); ?>"
                                                                    data-status="<?php echo htmlspecialchars($statusText); ?>"
                                                                    data-barangay="<?php echo htmlspecialchars($center['barangay']); ?>"
                                                                    data-city="<?php echo htmlspecialchars($center['city']); ?>"
                                                                    data-province="<?php echo htmlspecialchars($center['province']); ?>"
                                                                    data-address="<?php echo htmlspecialchars($fullCenter['address']); ?>"
                                                                    data-capacity="<?php echo $center['capacity']; ?>"
                                                                    data-current-occupants="<?php echo $actualOccupancy; ?>"
                                                                    data-contact-number="<?php echo htmlspecialchars($fullCenter['contact_number']); ?>"
                                                                    data-contact-person="<?php echo htmlspecialchars($fullCenter['contact_person']); ?>"
                                                                    data-latitude="<?php echo $fullCenter['latitude']; ?>"
                                                                    data-longitude="<?php echo $fullCenter['longitude']; ?>"
                                                                    data-estimated-capacity="<?php echo $fullCenter['estimated_capacity']; ?>"
                                                                    data-accessibility="<?php echo htmlspecialchars($fullCenter['accessibility']); ?>"
                                                                    data-available-facilities="<?php echo htmlspecialchars($fullCenter['available_facilities']); ?>"
                                                                    data-remarks="<?php echo htmlspecialchars($fullCenter['remarks']); ?>">
                                                                <i class="fa fa-edit"></i> Edit Center
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-success add-evacuee-dropdown" 
                                                                    data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"
                                                                    data-center-name="<?php echo htmlspecialchars($center['center_name']); ?>"
                                                                    data-current-occupants="<?php echo $actualOccupancy; ?>"
                                                                    data-capacity="<?php echo $center['capacity']; ?>">
                                                                <i class="fa fa-user-plus"></i> Add Evacuee
                                                            </button>
                                                            <?php if($statusText === 'Active'): ?>
                                                            <button type="button" class="btn btn-sm btn-danger inactivate-center-btn" 
                                                                    data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"
                                                                    data-center-name="<?php echo htmlspecialchars($center['center_name']); ?>"
                                                                    data-current-occupants="<?php echo $actualOccupancy; ?>">
                                                                <i class="fa fa-ban"></i> Inactivate Center
                                                            </button>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-sm btn-warning schedule-activation-btn" 
                                                                    data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"
                                                                    data-center-name="<?php echo htmlspecialchars($center['center_name']); ?>"
                                                                    data-current-capacity="<?php echo $center['capacity']; ?>">
                                                                <i class="fa fa-calendar"></i> Schedule Activation
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0">CENTER INFORMATION</h6>
                                                                <button type="button" class="btn btn-sm btn-warning view-history" 
                                                                    data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"
                                                                    data-center-name="<?php echo htmlspecialchars($center['center_name']); ?>">
                                                                <i class="fa fa-history"></i> History</button>
                                                            </div>
                                                            <div class="card-body">
                                                                <table class="table table-sm table-borderless mb-3">
                                                                    <tr><td width="40%"><strong>Center ID:</strong></td><td><?php echo htmlspecialchars($center['center_id']); ?></span></span>
                                                                    <tr><td><strong>Category:</strong></span><td><?php echo htmlspecialchars($center['category']); ?></span></span>
                                                                    <tr><td><strong>Location:</strong></span><td><?php echo htmlspecialchars(trim($center['barangay'] . ', ' . $center['city'])); ?></span></span>
                                                                    <tr><td><strong>Province:</strong></span><td><?php echo htmlspecialchars($center['province']); ?></span></span>
                                                                    <tr><td><strong>Capacity:</strong></span><td><?php echo number_format($center['capacity']); ?> persons</span></span>
                                                                    <tr><td><strong>Current Occupants:</strong></span><td><?php echo number_format($actualOccupancy); ?> / <?php echo number_format($center['capacity']); ?></span></span>
                                                                    <?php if($fullCenter['address']): ?>
                                                                    <tr><td><strong>Address:</strong></span><td><?php echo htmlspecialchars($fullCenter['address']); ?></span></span>
                                                                    <?php endif; ?>
                                                                </table>
                                                                
                                                                <hr>
                                                                <h6 class="fw-bold mb-2">FACILITY INFORMATION</h6>
                                                                <table class="table table-sm table-borderless">
                                                                    <tr><td><strong>Water Supply:</strong></span><td><?php echo htmlspecialchars($fullCenter['water_supply'] ?? 'N/A'); ?></span></span>
                                                                    <tr><td><strong>Electricity:</strong></span><td><?php echo htmlspecialchars($fullCenter['electricity'] ?? 'N/A'); ?></span></span>
                                                                    <tr><td><strong>Rooms:</strong></span><td><?php echo htmlspecialchars($fullCenter['num_rooms'] ?? 'N/A'); ?></span></span>
                                                                    <tr><td><strong>Restrooms:</strong></span><td><?php echo htmlspecialchars($fullCenter['restrooms_count'] ?? 'N/A'); ?></span></span>
                                                                    <tr><td><strong>WiFi:</strong></span><td><?php echo ($fullCenter['has_wifi'] ?? 0) ? '<i class="fa fa-check text-success"></i> Yes' : '<i class="fa fa-times text-danger"></i> No'; ?></span></span>
                                                                    <tr><td><strong>Canteen:</strong></span><td><?php echo ($fullCenter['has_canteen'] ?? 0) ? '<i class="fa fa-check text-success"></i> Yes' : '<i class="fa fa-times text-danger"></i> No'; ?></span></span>
                                                                    <tr><td><strong>Medical Station:</strong></span><td><?php echo ($fullCenter['has_medical'] ?? 0) ? '<i class="fa fa-check text-success"></i> Yes' : '<i class="fa fa-times text-danger"></i> No'; ?></span></span>
                                                                </table>
                                                                
                                                                <hr>
                                                                <h6 class="fw-bold mb-2">PERSON IN-CHARGE</h6>
                                                                <div id="person-incharge-<?php echo $center['center_id']; ?>">
                                                                    <p class="text-muted">No assigned personnel</p>
                                                                </div>
                                                                
                                                                <hr>
                                                                <h6 class="fw-bold mb-2">CONTACT INFORMATION</h6>
                                                                <p class="mb-0"><?php echo htmlspecialchars($fullCenter['contact_number'] ?: 'N/A'); ?></p>
                                                                <p><?php echo htmlspecialchars($fullCenter['contact_person'] ?: 'N/A'); ?></p>
                                                                
                                                                <?php if($fullCenter['remarks']): ?>
                                                                <hr>
                                                                <h6 class="fw-bold mb-2">REMARKS</h6>
                                                                <p class="mb-0 small text-muted"><?php echo nl2br(htmlspecialchars($fullCenter['remarks'])); ?></p>
                                                                <?php endif; ?>
                                                                
                                                                <hr>
                                                                <h6 class="fw-bold mb-2">STATUS HISTORY</h6>
                                                                <div id="status-history-<?php echo $center['center_id']; ?>" style="max-height: 150px; overflow-y: auto; font-size: 0.8rem;">
                                                                    <div class="text-muted">Loading...</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0">EVACUEES</h6>
                                                                <button class="btn btn-sm btn-light refresh-evacuees" data-center-id="<?php echo $center['center_id']; ?>">
                                                                    Refresh
                                                                </button>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="mb-2">
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="text" class="form-control evacuee-search-input" data-center-id="<?php echo $center['center_id']; ?>" placeholder="Search by name, contact, or status...">
                                                                        <button class="btn btn-outline-secondary clear-search" data-center-id="<?php echo $center['center_id']; ?>" type="button">Clear</button>
                                                                    </div>
                                                                </div>
                                                                <div class="evacuee-list-container" style="max-height: 400px; overflow-y: auto;">
                                                                    <div id="evacuee-list-<?php echo $center['center_id']; ?>">
                                                                        <div class="text-muted text-center p-3">Click to load evacuees...</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0">ACTIVITY HISTORY</h6>
                                                                <button class="btn btn-sm btn-light refresh-history" data-center-id="<?php echo $center['center_id']; ?>">
                                                                    Refresh
                                                                </button>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="history-container" style="max-height: 450px; overflow-y: auto;">
                                                                    <div id="history-list-<?php echo $center['center_id']; ?>">
                                                                        <div class="text-muted text-center p-3">Click to load history...</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </span>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            No evacuation centers found. 
                                            <a href="?route=centers" class="btn btn-primary btn-sm ms-2">
                                                <i class="fa fa-plus"></i> Add Your First Center
                                            </a>
                                        </span>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Center Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changeStatusForm">
                    <input type="hidden" id="change_status_center_id" name="center_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Evacuation Center:</label>
                        <input type="text" class="form-control" id="change_status_center_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Status:</label>
                        <input type="text" class="form-control" id="change_status_current" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="change_status_new" name="new_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="reactivate_message" style="display: none;">
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> 
                            <strong>Fresh Start!</strong> Reactivating this center will allow you to register new evacuees.
                        </div>
                    </div>
                    
                    <div class="mb-3" id="deactivate_warning" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> 
                            <strong>Warning!</strong> Setting this center to INACTIVE will clear all evacuees from this center.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmChangeStatus">Change Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Center Modal -->
<div class="modal fade" id="editCenterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-edit me-2"></i> Edit Evacuation Center
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCenterForm">
                    <input type="hidden" id="edit_center_id" name="center_id">
                    
                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fa fa-info-circle me-2"></i> Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Center Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_center_name" name="center_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_category" name="category" required>
                                        <option value="School">School</option>
                                        <option value="Gymnasium / Sports Complex">Gymnasium / Sports Complex</option>
                                        <option value="Church">Church</option>
                                        <option value="Community Center / Multipurpose Hall">Community Center / Multipurpose Hall</option>
                                        <option value="Covered Court">Covered Court</option>
                                        <option value="Private Facility / Commercial Building">Private Facility / Commercial Building</option>
                                        <option value="Open Field / Evacuation Ground">Open Field / Evacuation Ground</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Barangay</label>
                                    <input type="text" class="form-control" id="edit_barangay" name="barangay">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City/Municipality</label>
                                    <input type="text" class="form-control" id="edit_city" name="city">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Province</label>
                                    <input type="text" class="form-control" id="edit_province" name="province">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Complete Address</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Capacity & Status -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fa fa-users me-2"></i> Capacity & Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="edit_capacity" name="capacity" required>
                                    <small class="text-muted">Maximum number of evacuees</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Estimated Capacity</label>
                                    <input type="number" class="form-control" id="edit_estimated_capacity" name="estimated_capacity">
                                    <small class="text-muted">Optional overflow estimate</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Current Occupants</label>
                                    <input type="number" class="form-control" id="edit_current_occupants" name="current_occupants" readonly>
                                    <small class="text-muted">Auto-calculated from evacuees</small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" id="edit_status" name="status">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Facility Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fa fa-building me-2"></i> Facility Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Water Supply</label>
                                    <select class="form-control" id="edit_water_supply" name="water_supply">
                                        <option value="">Select</option>
                                        <option value="Available">Available</option>
                                        <option value="Limited">Limited</option>
                                        <option value="Not Available">Not Available</option>
                                        <option value="Tanker Provided">Tanker Provided</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Electricity</label>
                                    <select class="form-control" id="edit_electricity" name="electricity">
                                        <option value="">Select</option>
                                        <option value="Available">Available</option>
                                        <option value="Generator">Generator</option>
                                        <option value="Limited">Limited</option>
                                        <option value="Not Available">Not Available</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Number of Rooms</label>
                                    <input type="number" class="form-control" id="edit_num_rooms" name="num_rooms" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Number of Restrooms</label>
                                    <input type="number" class="form-control" id="edit_restrooms_count" name="restrooms_count" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Accessibility</label>
                                    <input type="text" class="form-control" id="edit_accessibility" name="accessibility" placeholder="e.g., Wheelchair accessible">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_has_wifi" name="has_wifi" value="1">
                                        <label class="form-check-label" for="edit_has_wifi">
                                            WiFi / Internet
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_has_canteen" name="has_canteen" value="1">
                                        <label class="form-check-label" for="edit_has_canteen">
                                            Canteen / Food
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_has_medical" name="has_medical" value="1">
                                        <label class="form-check-label" for="edit_has_medical">
                                            Medical Station
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Available Facilities</label>
                                <textarea class="form-control" id="edit_available_facilities" name="available_facilities" rows="2" placeholder="List other available facilities..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact & Location -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fa fa-phone me-2"></i> Contact Information & Location</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" id="edit_contact_person" name="contact_person">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="edit_contact_number" name="contact_number">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="edit_latitude" name="latitude" placeholder="e.g., 14.5995">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="edit_longitude" name="longitude" placeholder="e.g., 120.9842">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Remarks -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fa fa-comment me-2"></i> Additional Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="edit_remarks" name="remarks" rows="3" placeholder="Any additional notes or remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="cancelEditCenter" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmEditCenter">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Evacuee Modal -->
<div class="modal fade" id="addEvacueeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register New Evacuee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEvacueeForm">
                    <input type="hidden" id="evac_center_id" name="evacuation_center_id">
                    <input type="hidden" id="evac_encodedby" name="encodedby" value="<?php echo $_SESSION['userid']; ?>">
                    <input type="hidden" id="evac_evacuee_status" name="evacuee_status" value="Active">
                    
                    <div class="alert alert-info mb-3">
                        <strong>Evacuation Center:</strong> <span id="selectedCenterName"></span>
                        <br>
                        <strong>Current Occupancy:</strong> <span id="selectedCenterOccupancy"></span> / <span id="selectedCenterCapacity"></span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Registration Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="evac_registration_date" name="registration_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arrival Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="evac_arrival_date" name="arrival_date" required>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3">Personal Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="evac_last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="evac_first_name" name="first_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="evac_middle_name" name="middle_name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Extension Name</label>
                            <input type="text" class="form-control" id="evac_extension_name" name="extension_name" placeholder="Jr., Sr., III">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Relation to Head</label>
                            <input type="text" class="form-control" id="evac_relation_to_head" name="relation_to_head">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Sex <span class="text-danger">*</span></label>
                            <select class="form-control" id="evac_sex" name="sex" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="evac_birth_date" name="birth_date">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="number" class="form-control" id="evac_age" name="age" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Civil Status</label>
                            <select class="form-control" id="evac_civil_status" name="civil_status">
                                <option value="">Select</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" id="evac_occupation" name="occupation">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="evac_contact_number" name="contact_number">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Complete Address</label>
                            <input type="text" class="form-control" id="evac_complete_address" name="complete_address">
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3">Emergency Contact</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Person</label>
                            <input type="text" class="form-control" id="evac_emergency_contact_person" name="emergency_contact_person">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Number</label>
                            <input type="text" class="form-control" id="evac_emergency_contact_number" name="emergency_contact_number">
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3">Special Conditions</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="evac_condition_pregnant" name="condition_pregnant" value="1">
                                <label class="form-check-label" for="evac_condition_pregnant">Pregnant</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="evac_condition_lactating" name="condition_lactating" value="1">
                                <label class="form-check-label" for="evac_condition_lactating">Lactating</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="evac_condition_elderly" name="condition_elderly" value="1">
                                <label class="form-check-label" for="evac_condition_elderly">Elderly</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="evac_condition_pwd" name="condition_pwd" value="1">
                                <label class="form-check-label" for="evac_condition_pwd">PWD</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="evac_condition_4ps" name="condition_4ps" value="1">
                                <label class="form-check-label" for="evac_condition_4ps">4Ps Beneficiary</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">PWD Type</label>
                            <select class="form-control" id="evac_pwd_type" name="pwd_type">
                                <option value="">Select</option>
                                <option value="Mobility">Mobility</option>
                                <option value="Visual">Visual</option>
                                <option value="Hearing">Hearing</option>
                                <option value="Speech">Speech</option>
                                <option value="Cognitive">Cognitive</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3">Medical Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Health Status</label>
                            <select class="form-control" id="evac_health_status" name="health_status">
                                <option value="">Select</option>
                                <option value="Good">Good</option>
                                <option value="With illness">With illness</option>
                                <option value="Under medication">Under medication</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Medical Condition</label>
                            <select class="form-control" id="evac_emergency_medical_condition" name="emergency_medical_condition">
                                <option value="">Select</option>
                                <option value="None">None</option>
                                <option value="Hypertension">Hypertension</option>
                                <option value="Diabetes">Diabetes</option>
                                <option value="Asthma">Asthma</option>
                                <option value="Heart Disease">Heart Disease</option>
                                <option value="Kidney Disease">Kidney Disease</option>
                                <option value="Epilepsy">Epilepsy</option>
                                <option value="Allergy">Allergy</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Medications Taken</label>
                            <textarea class="form-control" id="evac_medications_taken" name="medications_taken" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Known Allergies</label>
                            <textarea class="form-control" id="evac_known_allergies" name="known_allergies" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmAddEvacuee">Register Evacuee</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Evacuee Modal -->
<div class="modal fade" id="editEvacueeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Evacuee Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEvacueeForm">
                    <input type="hidden" id="edit_evacuee_id" name="evacuee_id">
                    <input type="hidden" id="edit_center_id" name="center_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Evacuee Name</label>
                        <input type="text" class="form-control" id="edit_evacuee_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_evacuee_status" name="evacuee_status" required>
                            <option value="Active">Active</option>
                            <option value="Departed">Departed</option>
                            <option value="Transferred">Transferred</option>
                            <option value="Missing">Missing</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="departure_date_group" style="display: none;">
                        <label class="form-label">Departure Date</label>
                        <input type="date" class="form-control" id="edit_departure_date" name="departure_date">
                    </div>
                    
                    <div class="mb-3" id="transfer_center_group" style="display: none;">
                        <label class="form-label">Transfer To Center</label>
                        <select class="form-control" id="edit_transfer_center" name="transfer_center_id">
                            <option value="">Select Center</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" id="edit_remarks" name="remarks" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmUpdateEvacuee">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-history"></i> Center History - <span id="history_center_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" id="historyTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Action</th>
                                <th>Changes Made</th>
                                <th>Remarks</th>
                                <th>Changed By</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody">
                            <tr><td colspan="7" class="text-center">Loading...</span></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Activation Modal -->
<div class="modal fade" id="scheduleActivationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-calendar me-2"></i> Schedule Center Activation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleActivationForm">
                    <input type="hidden" id="schedule_center_id" name="center_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Evacuation Center:</label>
                        <input type="text" class="form-control" id="schedule_center_name" readonly>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Schedule Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="schedule_datetime" name="scheduled_datetime" value="<?php echo $default_datetime; ?>" required>
                            <small class="text-muted">Philippine Time (UTC+8)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="schedule_capacity" name="capacity" min="0" required>
                            <small class="text-muted">Maximum number of people this center can hold</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-2">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <strong>Note:</strong> The center's capacity will be updated immediately to the value you enter above.
                    </div>
                    
                    <hr class="my-3">
                    <h6 class="fw-bold mb-3"><i class="fa fa-building me-2"></i> Facility Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Water Supply</label>
                            <select class="form-control" id="schedule_water_supply" name="water_supply">
                                <option value="">Select</option>
                                <option value="Available">Available</option>
                                <option value="Limited">Limited</option>
                                <option value="Not Available">Not Available</option>
                                <option value="Tanker Provided">Tanker Provided</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Electricity</label>
                            <select class="form-control" id="schedule_electricity" name="electricity">
                                <option value="">Select</option>
                                <option value="Available">Available</option>
                                <option value="Generator">Generator</option>
                                <option value="Limited">Limited</option>
                                <option value="Not Available">Not Available</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Number of Rooms</label>
                            <input type="number" class="form-control" id="schedule_num_rooms" name="num_rooms" min="0">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="schedule_has_wifi" name="has_wifi" value="1">
                                <label class="form-check-label" for="schedule_has_wifi">WiFi / Internet</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="schedule_has_canteen" name="has_canteen" value="1">
                                <label class="form-check-label" for="schedule_has_canteen">Canteen / Food</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="schedule_has_medical" name="has_medical" value="1">
                                <label class="form-check-label" for="schedule_has_medical">Medical Station</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Restrooms</label>
                            <input type="number" class="form-control" id="schedule_restrooms_count" name="restrooms_count" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="schedule_notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fa fa-info-circle"></i> 
                        The center will automatically become ACTIVE at the scheduled date and time (Philippine Time) with the specified capacity and facilities.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmScheduleActivation">Schedule Activation</button>
            </div>
        </div>
    </div>
</div>

<!-- View Schedules Modal -->
<div class="modal fade" id="viewSchedulesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-calendar-alt me-2"></i> Scheduled Activations
                </h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fa fa-clock-o"></i> All times are in Philippine Time (UTC+8)
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="schedulesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Scheduled Date & Time</th>
                                <th>Center</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="schedules-tbody">
                            <tr><td colspan="5" class="text-center">Loading...</span></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Schedule Modal -->
<div class="modal fade" id="cancelScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Scheduled Activation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this scheduled activation?</p>
                <input type="hidden" id="cancel_schedule_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep</button>
                <button type="button" class="btn btn-danger" id="confirmCancelSchedule">Yes, Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
.center-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}
.center-row:hover {
    background-color: #f8f9fa;
}
.expand-icon i {
    transition: transform 0.2s ease;
    display: inline-block;
}
.details-row {
    background-color: #f8f9fa;
}
.status-clickable {
    cursor: pointer;
    transition: all 0.2s ease;
}
.status-clickable:hover {
    opacity: 0.8;
    transform: scale(1.02);
}
.btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var evacueeDataStore = {};
    
    // Set default dates
    var today = new Date().toISOString().split('T')[0];
    $('#evac_registration_date').val(today);
    $('#evac_arrival_date').val(today);
    
    // Auto-check for scheduled activations every minute
    function checkScheduledActivations(showLoading = false) {
        if (showLoading) {
            Swal.fire({
                title: 'Checking schedules...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }
        
        $.ajax({
            url: "ajax/check_activations.ajax.php",
            method: "POST",
            dataType: "json",
            success: function(response) {
                if (showLoading) Swal.close();
                if (response.activated > 0) {
                    Swal.fire({
                        title: 'Centers Activated!',
                        html: `${response.activated} center(s) have been automatically activated.<br><br>${response.centers.join('<br>')}`,
                        icon: 'success',
                        timer: 5000,
                        showConfirmButton: true
                    }).then(() => location.reload());
                } else if (showLoading && response.activated === 0) {
                    Swal.fire({ title: 'No Activations', text: 'No pending activations found.', icon: 'info', timer: 2000, showConfirmButton: false });
                }
            },
            error: function() { if (showLoading) Swal.close(); }
        });
    }
    
    // Check immediately and every minute
    checkScheduledActivations();
    setInterval(checkScheduledActivations, 60000);
    
    $('#checkActivationsNow').on('click', function() { checkScheduledActivations(true); });
    
    // Calculate age from birth date
    $('#evac_birth_date').on('change', function() {
        var birthDate = new Date($(this).val());
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        if (age >= 0 && age < 120) $('#evac_age').val(age);
        else $('#evac_age').val('');
    });
    
    // Close modal helper
    function closeModal(modalId) {
        $('#' + modalId).modal('hide');
        setTimeout(function() { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 150);
    }
    
    // Search and filter
    function applyCenterFilters() {
        var searchTerm = $('#searchCenterFilter').val().toLowerCase().trim();
        var statusFilter = $('#statusFilter').val().toLowerCase();
        var categoryFilter = $('#categoryFilter').val().toLowerCase();
        
        $('.center-row').each(function() {
            var row = $(this);
            var centerId = row.data('center-id');
            var detailsRow = $('.details-row-' + centerId);
            var centerName = row.find('td:nth-child(2)').text().toLowerCase();
            var category = row.find('td:nth-child(3)').text().toLowerCase();
            var location = row.find('td:nth-child(4)').text().toLowerCase();
            var status = row.find('td:nth-child(7) .badge').clone().children().remove().end().text().trim().toLowerCase();
            
            var matchesSearch = centerName.includes(searchTerm) || location.includes(searchTerm);
            var matchesStatus = (statusFilter === "") || (status === statusFilter);
            var matchesCategory = (categoryFilter === "") || (category === categoryFilter);
            
            if (matchesSearch && matchesStatus && matchesCategory) row.show();
            else {
                row.hide();
                if (detailsRow.is(':visible')) {
                    detailsRow.hide();
                    row.find('.expand-icon i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
                }
            }
        });
    }
    
    $('#searchCenterFilter').on('input', applyCenterFilters);
    $('#statusFilter, #categoryFilter').on('change', applyCenterFilters);
    $('#resetCenterFilters').on('click', function() {
        $('#searchCenterFilter').val('');
        $('#statusFilter').val('');
        $('#categoryFilter').val('');
        applyCenterFilters();
    });
    
    // Expand/collapse rows
    $('.center-row').on('click', function(e) {
        if ($(e.target).closest('button, .btn, a').length) return;
        var centerId = $(this).data('center-id');
        var detailsRow = $('.details-row-' + centerId);
        var expandIcon = $(this).find('.expand-icon i');
        
        if (detailsRow.is(':visible')) {
            detailsRow.slideUp(300);
            expandIcon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        } else {
            $('.details-row:visible').slideUp(300);
            $('.expand-icon i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
            detailsRow.slideDown(300);
            expandIcon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            if (detailsRow.find('.evacuee-list-container').data('loaded') !== true) {
                loadEvacuees(centerId);
                loadHistory(centerId);
                loadStatusHistory(centerId);
                detailsRow.find('.evacuee-list-container').data('loaded', true);
            }
        }
    });
    
    function loadEvacuees(centerId) {
        $('#evacuee-list-' + centerId).html('<div class="text-muted text-center p-3">Loading evacuees...</div>');
        $.ajax({
            url: "ajax/get_center_evacuees.ajax.php",
            method: "POST",
            data: { center_id: centerId },
            dataType: "json",
            success: function(response) {
                if (response.success && response.evacuees) {
                    evacueeDataStore[centerId] = response.evacuees;
                    displayEvacuees(centerId, response.evacuees);
                    var activeCount = response.evacuees.filter(function(e) { return e.evacuee_status === 'Active'; }).length;
                    $('.occupancy-display-' + centerId).text(activeCount);
                } else {
                    $('#evacuee-list-' + centerId).html('<div class="text-muted text-center p-3">No evacuees found</div>');
                }
            }
        });
    }
    
    function displayEvacuees(centerId, evacuees, searchTerm) {
        var html = '';
        var filteredEvacuees = evacuees;
        if (searchTerm && searchTerm.trim() !== '') {
            var term = searchTerm.toLowerCase();
            filteredEvacuees = evacuees.filter(function(e) {
                return (e.first_name && e.first_name.toLowerCase().includes(term)) ||
                       (e.last_name && e.last_name.toLowerCase().includes(term)) ||
                       (e.contact_number && e.contact_number.toLowerCase().includes(term)) ||
                       (e.evacuee_status && e.evacuee_status.toLowerCase().includes(term));
            });
        }
        
        if (filteredEvacuees.length === 0) {
            html = '<div class="text-muted text-center p-3">No evacuees found</div>';
        } else {
            filteredEvacuees.forEach(function(e) {
                var statusClass = e.evacuee_status === 'Active' ? 'bg-success' : 'bg-warning';
                html += '<div class="evacuee-item p-2 mb-2 border rounded">' +
                    '<strong>' + escapeHtml(e.last_name) + ', ' + escapeHtml(e.first_name) + '</strong>' +
                    '<span class="badge ' + statusClass + ' ms-2">' + e.evacuee_status + '</span>' +
                    '<div class="small text-muted">' + (e.sex || 'N/A') + ' | Age: ' + (e.age || 'N/A') + ' | Contact: ' + (e.contact_number || 'N/A') + '</div>' +
                    '<button class="btn btn-sm btn-outline-primary mt-1 edit-evic-status" data-evic-id="' + e.evacuee_id + '" data-name="' + escapeHtml(e.first_name) + ' ' + escapeHtml(e.last_name) + '" data-status="' + e.evacuee_status + '" data-center-id="' + centerId + '">Edit</button>' +
                    '</div>';
            });
        }
        $('#evacuee-list-' + centerId).html(html);
    }
    
    function loadHistory(centerId) {
        $('#history-list-' + centerId).html('<div class="text-muted text-center p-3">Loading history...</div>');
        $.ajax({
            url: "ajax/get_center_history.ajax.php",
            method: "POST",
            data: { center_id: centerId },
            dataType: "json",
            success: function(response) {
                if (response.success && response.history) {
                    var html = '';
                    response.history.forEach(function(record) {
                        html += '<div class="history-item border-bottom p-2 small">' +
                            '<strong>' + (record.action_type || 'Activity') + '</strong><br>' +
                            (record.description || '') + '<br>' +
                            '<small class="text-muted">' + formatDate(record.created_at) + ' by ' + (record.performed_by || 'System') + '</small>' +
                            '</div>';
                    });
                    $('#history-list-' + centerId).html(html);
                } else {
                    $('#history-list-' + centerId).html('<div class="text-muted text-center p-3">No history records</div>');
                }
            }
        });
    }
    
    function loadStatusHistory(centerId) {
        $('#status-history-' + centerId).html('<div class="text-muted">Loading...</div>');
        $.ajax({
            url: "ajax/get_center_status_history.ajax.php",
            method: "POST",
            data: { center_id: centerId },
            dataType: "json",
            success: function(response) {
                if (response.success && response.history) {
                    var html = '';
                    response.history.forEach(function(record) {
                        html += '<div class="status-history-item border-bottom p-1 small">' +
                            (record.old_status || 'Unknown') + ' → ' + record.new_status + '<br>' +
                            '<small>' + formatDate(record.changed_at) + ' by ' + (record.changed_by || 'System') + '</small>' +
                            '</div>';
                    });
                    $('#status-history-' + centerId).html(html);
                } else {
                    $('#status-history-' + centerId).html('<div class="text-muted">No status changes</div>');
                }
            }
        });
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString();
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Refresh buttons
    $('.refresh-evacuees').on('click', function() { loadEvacuees($(this).data('center-id')); });
    $('.refresh-history').on('click', function() { loadHistory($(this).data('center-id')); });
    
    // Print report
    $('.print-report').on('click', function(e) {
        e.stopPropagation();
        window.open('reports/center_report.php?center_id=' + $(this).data('center-id'), '_blank');
    });
    
    // Edit evacuee status
    $(document).on('click', '.edit-evic-status', function(e) {
        e.stopPropagation();
        $('#edit_evacuee_id').val($(this).data('evic-id'));
        $('#edit_evacuee_name').val($(this).data('name'));
        $('#edit_evacuee_status').val($(this).data('status'));
        $('#edit_center_id').val($(this).data('center-id'));
        $('#editEvacueeModal').modal('show');
    });
    
    $('#confirmUpdateEvacuee').on('click', function() {
        var evacueeId = $('#edit_evacuee_id').val();
        var newStatus = $('#edit_evacuee_status').val();
        var centerId = $('#edit_center_id').val();
        
        $.ajax({
            url: "ajax/update_evacuee_status.ajax.php",
            method: "POST",
            data: { evacuee_id: evacueeId, status: newStatus, center_id: centerId },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    closeModal('editEvacueeModal');
                    loadEvacuees(centerId);
                    loadHistory(centerId);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
    
    // Add evacuee
    $('.add-evacuee-dropdown').on('click', function(e) {
        e.stopPropagation();
        var centerId = $(this).data('center-id');
        var centerName = $(this).data('center-name');
        var currentOccupants = $(this).data('current-occupants');
        var capacity = $(this).data('capacity');
        
        var centerStatus = $('.status-badge-' + centerId).text().trim();
        if (centerStatus !== 'Active') {
            Swal.fire('Error', 'You can only add evacuees to Active centers.', 'warning');
            return;
        }
        
        if (currentOccupants >= capacity) {
            Swal.fire('Error', 'Center is at full capacity!', 'warning');
            return;
        }
        
        $('#evac_center_id').val(centerId);
        $('#selectedCenterName').text(centerName);
        $('#selectedCenterOccupancy').text(currentOccupants);
        $('#selectedCenterCapacity').text(capacity);
        
        $('#addEvacueeForm')[0].reset();
        $('#evac_registration_date').val(today);
        $('#evac_arrival_date').val(today);
        $('#evac_center_id').val(centerId);
        
        $('#addEvacueeModal').modal('show');
    });
    
    $('#confirmAddEvacuee').on('click', function() {
        var last_name = $('#evac_last_name').val();
        var first_name = $('#evac_first_name').val();
        var sex = $('#evac_sex').val();
        
        if (!last_name || !first_name || !sex) {
            Swal.fire('Error', 'Please fill in Last Name, First Name, and Sex', 'warning');
            return;
        }
        
        var currentOccupants = parseInt($('#selectedCenterOccupancy').text());
        var capacity = parseInt($('#selectedCenterCapacity').text());
        
        if (currentOccupants >= capacity) {
            Swal.fire('Error', 'Center is at full capacity!', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Register Evacuee?',
            text: 'Are you sure you want to register this evacuee?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, register',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Registering...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                
                var formData = new FormData();
                formData.append('trans_type', 'New');
                formData.append('encodedby', $('#evac_encodedby').val());
                formData.append('registration_date', $('#evac_registration_date').val());
                formData.append('last_name', $('#evac_last_name').val());
                formData.append('first_name', $('#evac_first_name').val());
                formData.append('middle_name', $('#evac_middle_name').val());
                formData.append('extension_name', $('#evac_extension_name').val());
                formData.append('relation_to_head', $('#evac_relation_to_head').val());
                formData.append('sex', $('#evac_sex').val());
                formData.append('birth_date', $('#evac_birth_date').val());
                formData.append('age', $('#evac_age').val());
                formData.append('civil_status', $('#evac_civil_status').val());
                formData.append('occupation', $('#evac_occupation').val());
                formData.append('contact_number', $('#evac_contact_number').val());
                formData.append('complete_address', $('#evac_complete_address').val());
                formData.append('emergency_contact_person', $('#evac_emergency_contact_person').val());
                formData.append('emergency_contact_number', $('#evac_emergency_contact_number').val());
                formData.append('condition_pregnant', $('#evac_condition_pregnant').is(':checked') ? 1 : 0);
                formData.append('condition_lactating', $('#evac_condition_lactating').is(':checked') ? 1 : 0);
                formData.append('condition_elderly', $('#evac_condition_elderly').is(':checked') ? 1 : 0);
                formData.append('condition_pwd', $('#evac_condition_pwd').is(':checked') ? 1 : 0);
                formData.append('condition_4ps', $('#evac_condition_4ps').is(':checked') ? 1 : 0);
                formData.append('pwd_type', $('#evac_pwd_type').val());
                formData.append('health_status', $('#evac_health_status').val());
                formData.append('emergency_medical_condition', $('#evac_emergency_medical_condition').val());
                formData.append('medications_taken', $('#evac_medications_taken').val());
                formData.append('known_allergies', $('#evac_known_allergies').val());
                formData.append('evacuation_center_id', $('#evac_center_id').val());
                formData.append('arrival_date', $('#evac_arrival_date').val());
                formData.append('evacuee_status', 'Active');
                
                $.ajax({
                    url: "ajax/evacuees_save.ajax.php",
                    method: "POST",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "text",
                    success: function(response) {
                        Swal.close();
                        if (response == 'success') {
                            Swal.fire('Success!', 'Evacuee registered successfully!', 'success').then(() => {
                                $('#addEvacueeModal').modal('hide');
                                var centerId = $('#evac_center_id').val();
                                loadEvacuees(centerId);
                                var newOccupancy = currentOccupants + 1;
                                $('.occupancy-display-' + centerId).text(newOccupancy);
                            });
                        } else {
                            Swal.fire('Error', 'Failed to register evacuee. Please try again.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error('AJAX Error:', error);
                        Swal.fire('Error', 'An error occurred. Please check the console.', 'error');
                    }
                });
            }
        });
    });
    
    // Inactivate Center
    $(document).on('click', '.inactivate-center-btn', function(e) {
    e.stopPropagation();
    var centerId = $(this).data('center-id');
    var centerName = $(this).data('center-name');
    var currentOccupants = $(this).data('current-occupants');
    
    var message = currentOccupants > 0 
        ? 'This center has <strong>' + currentOccupants + '</strong> active evacuee(s).<br><br>Inactivating will:<br>• Mark all active evacuees as DEPARTED<br>• Generate a complete inactivation report<br>• Set center status to INACTIVE'
        : 'Are you sure you want to inactivate this center?<br><br>A complete inactivation report will be generated.';
    
    Swal.fire({
        title: 'Inactivate Center?',
        html: '<strong>' + centerName + '</strong><br><br>' + message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, inactivate and generate report',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Inactivating center...',
                text: 'Generating inactivation report...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            $.ajax({
                url: "ajax/inactivate_center.ajax.php",
                method: "POST",
                data: { center_id: centerId },
                dataType: "json",
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: response.message + '<br><br>📄 Inactivation report has been generated.',
                            icon: 'success',
                            confirmButtonText: 'View Report'
                        }).then((result) => {
                            if (result.isConfirmed && response.report_url) {
                                window.open(response.report_url, '_blank');
                            }
                            // Reload the page to refresh all data
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Failed to inactivate center', 'error');
                }
            });
        }
    });
});
    
    // Schedule Activation
    $(document).on('click', '.schedule-activation-btn', function(e) {
        e.stopPropagation();
        $('#schedule_center_id').val($(this).data('center-id'));
        $('#schedule_center_name').val($(this).data('center-name'));
        $('#schedule_capacity').val($(this).data('current-capacity'));
        $('#scheduleActivationModal').modal('show');
    });
    
    $('#confirmScheduleActivation').on('click', function() {
        var scheduledDatetime = $('#schedule_datetime').val();
        var capacity = $('#schedule_capacity').val();
        
        if (!scheduledDatetime) {
            Swal.fire('Error', 'Please select a date and time', 'warning');
            return;
        }
        if (!capacity || capacity < 0) {
            Swal.fire('Error', 'Please enter a valid capacity', 'warning');
            return;
        }
        
        var selectedDate = new Date(scheduledDatetime);
        if (selectedDate <= new Date()) {
            Swal.fire('Error', 'Schedule must be in the future', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Schedule Activation?',
            html: 'Schedule <strong>' + $('#schedule_center_name').val() + '</strong> to activate on:<br><strong>' + selectedDate.toLocaleString() + '</strong><br><br>⚠️ The center capacity will be updated to <strong>' + Number(capacity).toLocaleString() + '</strong> immediately.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, schedule'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Scheduling...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: "ajax/schedule_activation.ajax.php",
                    method: "POST",
                    data: {
                        action: 'create_schedule',
                        center_id: $('#schedule_center_id').val(),
                        scheduled_datetime: scheduledDatetime,
                        capacity: capacity,
                        water_supply: $('#schedule_water_supply').val(),
                        electricity: $('#schedule_electricity').val(),
                        num_rooms: $('#schedule_num_rooms').val(),
                        has_wifi: $('#schedule_has_wifi').is(':checked') ? 1 : 0,
                        has_canteen: $('#schedule_has_canteen').is(':checked') ? 1 : 0,
                        has_medical: $('#schedule_has_medical').is(':checked') ? 1 : 0,
                        restrooms_count: $('#schedule_restrooms_count').val(),
                        notes: $('#schedule_notes').val()
                    },
                    dataType: "json",
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Success', response.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // View Schedules
    $('#viewSchedulesBtn').on('click', function() {
        $('#schedules-tbody').html('<tr><td colspan="5" class="text-center">Loading...</span></tr>');
        $('#viewSchedulesModal').modal('show');
        
        $.ajax({
            url: "ajax/schedule_activation.ajax.php",
            method: "POST",
            data: { action: 'get_schedules' },
            dataType: "json",
            success: function(response) {
                if (response.success && response.schedules && response.schedules.length > 0) {
                    var rows = '';
                    $.each(response.schedules, function(i, s) {
                        var scheduledDate = new Date(s.scheduled_datetime);
                        rows += '<tr>' +
                            '<td>' + scheduledDate.toLocaleString() + '</td>' +
                            '<td>' + escapeHtml(s.center_name) + '</td>' +
                            '<td>' + (s.scheduled_capacity || s.capacity) + '</td>' +
                            '<td><span class="badge bg-warning">' + s.status + '</span></td>' +
                            '<td><button class="btn btn-sm btn-danger cancel-schedule-btn" data-schedule-id="' + s.schedule_id + '">Cancel</button></td>' +
                            '</tr>';
                    });
                    $('#schedules-tbody').html(rows);
                } else {
                    $('#schedules-tbody').html('<tr><td colspan="5" class="text-center">No pending schedules</td></tr>');
                }
            }
        });
    });
    
    $(document).on('click', '.cancel-schedule-btn', function() {
        var scheduleId = $(this).data('schedule-id');
        Swal.fire({
            title: 'Cancel Schedule?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "ajax/schedule_activation.ajax.php",
                    method: "POST",
                    data: { action: 'cancel_schedule', schedule_id: scheduleId },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.message, 'success');
                            $('#viewSchedulesBtn').click();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Status change functions
    window.openChangeStatusModal = function(centerId, centerName, currentStatus) {
        $('#change_status_center_id').val(centerId);
        $('#change_status_center_name').val(centerName);
        $('#change_status_current').val(currentStatus);
        $('#change_status_new').val(currentStatus);
        $('#changeStatusModal').modal('show');
    };
    
    $('#confirmChangeStatus').on('click', function() {
        var centerId = $('#change_status_center_id').val();
        var newStatus = $('#change_status_new').val();
        $.ajax({
            url: "ajax/change_center_status.ajax.php",
            method: "POST",
            data: { center_id: centerId, new_status: newStatus },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
    
    window.showPendingInfo = function(centerName, scheduleTime) {
        Swal.fire({
            title: 'Pending for Activation',
            html: '<strong>' + centerName + '</strong><br>Scheduled: ' + scheduleTime,
            icon: 'info'
        });
    };
    
    // View History
    $(document).on('click', '.view-history', function(e) {
        e.stopPropagation();
        var centerId = $(this).data('center-id');
        var centerName = $(this).data('center-name');
        
        $('#history_center_name').text(centerName);
        $('#history-tbody').html('<tr><td colspan="7" class="text-center">Loading...</span></tr>');
        $('#historyModal').modal('show');
        
        $.ajax({
            url: "ajax/get_center_full_history.ajax.php",
            method: "POST",
            data: { center_id: centerId },
            dataType: "json",
            success: function(response) {
                if (response.success && response.history) {
                    var html = '';
                    response.history.forEach(function(record) {
                        html += '<tr>' +
                            '<td>' + formatDate(record.created_at) + '</td>' +
                            '<td>' + (record.action_type || 'Activity') + '</td>' +
                            '<td>' + (record.description || '-') + '</td>' +
                            '<td>' + (record.remarks || '-') + '</td>' +
                            '<td>' + (record.performed_by || 'System') + '</td>' +
                            '</tr>';
                    });
                    $('#history-tbody').html(html);
                } else {
                    $('#history-tbody').html('<td><td colspan="7" class="text-center">No history found</td></tr>');
                }
            }
        });
    });

    
});
</script>
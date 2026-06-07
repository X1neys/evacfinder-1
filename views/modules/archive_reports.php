<?php
// archive_reports.php
session_start();
require_once "config/connection.php";

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header('Location: index.php');
    exit;
}

$db = new Connection();
$pdo = $db->connect();

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$center_filter = isset($_GET['center_id']) ? $_GET['center_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$query = "SELECT sr.*, c.center_name, c.category, c.barangay, c.city 
          FROM saved_reports sr
          JOIN centers c ON sr.center_id = c.center_id
          WHERE sr.report_type = 'Inactivation'";

$params = [];

if (!empty($search)) {
    $query .= " AND (sr.report_id LIKE :search OR c.center_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($center_filter)) {
    $query .= " AND sr.center_id = :center_id";
    $params[':center_id'] = $center_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(sr.generated_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(sr.generated_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$query .= " ORDER BY sr.generated_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all centers for filter dropdown
$centersStmt = $pdo->query("SELECT center_id, center_name FROM centers ORDER BY center_name");
$allCenters = $centersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inactivation Reports Archive - EvacFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Main Content - Centered */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
            min-height: 100vh;
        }
        
        /* Stats Cards */
        .stats-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .bg-info-light {
            background-color: rgba(13, 202, 240, 0.1);
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .filter-section .form-label {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
            color: #495057;
        }
        
        /* Report Cards */
        .report-card {
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 20px;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .report-card .card-header {
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .report-card .card-body {
            padding: 20px;
        }
        
        .report-card .card-footer {
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .report-details {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .btn-view {
            background: #0d6efd;
            color: white;
            transition: all 0.2s;
        }
        
        .btn-view:hover {
            background: #0b5ed7;
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            transition: all 0.2s;
        }
        
        .btn-delete:hover {
            transform: translateY(-1px);
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Main Content - Centered -->
    <div class="main-content">
        
        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-0">
                    <i class="fas fa-archive text-danger me-2"></i>
                    Inactivation Reports Archive
                </h2>
                <p class="text-muted mt-2 mb-0">View and manage all center inactivation reports</p>
            </div>
            <a href="?route=active" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
        
        <!-- Statistics Cards Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-card d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Total Reports</span>
                        <h2 class="mb-0 mt-1"><?php echo count($reports); ?></h2>
                        <small class="text-muted">Generated inactivation reports</small>
                    </div>
                    <div class="stats-icon bg-danger-light text-danger">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Unique Centers</span>
                        <h2 class="mb-0 mt-1">
                            <?php 
                            $uniqueCenters = array_unique(array_column($reports, 'center_id'));
                            echo count($uniqueCenters);
                            ?>
                        </h2>
                        <small class="text-muted">Centers with inactivation reports</small>
                    </div>
                    <div class="stats-icon bg-info-light text-info">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-filter text-primary me-2"></i>
                <h6 class="mb-0 fw-bold">Filter Reports</h6>
            </div>
            <form method="GET" action="">
                <input type="hidden" name="route" value="archive_reports">
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-search me-1"></i> Search
                        </label>
                        <input type="text" class="form-control" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Report ID or Center Name...">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-building me-1"></i> Filter by Center
                        </label>
                        <select class="form-select" name="center_id">
                            <option value="">All Centers</option>
                            <?php foreach ($allCenters as $center): ?>
                                <option value="<?php echo $center['center_id']; ?>" 
                                    <?php echo $center_filter == $center['center_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($center['center_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-calendar me-1"></i> Date From
                        </label>
                        <input type="date" class="form-control" name="date_from" 
                               value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-calendar me-1"></i> Date To
                        </label>
                        <input type="date" class="form-control" name="date_to" 
                               value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="?route=archive_reports" class="btn btn-sm btn-secondary">
                            <i class="fas fa-redo me-1"></i> Reset Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Reports List -->
        <?php if (count($reports) > 0): ?>
            <div class="row">
                <?php foreach ($reports as $report): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card report-card">
                            <div class="card-header bg-danger text-white d-flex align-items-center">
                                <i class="fas fa-file-pdf me-2"></i>
                                <span>Inactivation Report</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-dark mb-3">
                                    <i class="fas fa-building me-1 text-muted"></i>
                                    <?php echo htmlspecialchars($report['center_name']); ?>
                                </h5>
                                <div class="report-details">
                                    <div class="mb-2">
                                        <i class="fas fa-id-card text-muted me-2" style="width: 20px;"></i>
                                        <small><strong class="text-muted">Report ID:</strong></small><br>
                                        <small class="text-muted ms-4"><?php echo htmlspecialchars($report['report_id']); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-calendar text-muted me-2" style="width: 20px;"></i>
                                        <small><strong class="text-muted">Generated:</strong></small><br>
                                        <small class="text-muted ms-4"><?php echo date('F d, Y g:i A', strtotime($report['generated_at'])); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-user text-muted me-2" style="width: 20px;"></i>
                                        <small><strong class="text-muted">Generated By:</strong></small><br>
                                        <small class="text-muted ms-4"><?php echo htmlspecialchars($report['generated_by']); ?></small>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-primary btn-sm btn-view" target="_blank">
                                        <i class="fas fa-eye me-1"></i> View Report
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-report" 
                                            data-report-id="<?php echo $report['report_id']; ?>"
                                            data-file-path="<?php echo htmlspecialchars($report['file_path']); ?>">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted">
                                    <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($report['category']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-folder-open fa-4x mb-3 opacity-50"></i>
                <h5 class="mb-2">No Inactivation Reports Found</h5>
                <p class="mb-0">When you inactivate a center, a report will be generated and saved here automatically.</p>
                <hr>
                <a href="?route=active" class="btn btn-primary mt-2">
                    <i class="fas fa-building me-1"></i> Go to Active Centers
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this inactivation report?</p>
                    <p class="text-danger mt-2 mb-0 small">
                        <i class="fas fa-ban me-1"></i> This action cannot be undone!
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        let reportToDelete = null;
        let filePathToDelete = null;
        
        $('.delete-report').on('click', function() {
            reportToDelete = $(this).data('report-id');
            filePathToDelete = $(this).data('file-path');
            $('#deleteModal').modal('show');
        });
        
        $('#confirmDelete').on('click', function() {
            if (!reportToDelete) return;
            
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            $.ajax({
                url: 'ajax/delete_inactivation_report.ajax.php',
                method: 'POST',
                data: {
                    report_id: reportToDelete,
                    file_path: filePathToDelete
                },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error!', 'Failed to delete report', 'error');
                }
            });
            
            $('#deleteModal').modal('hide');
        });
    });
    </script>
</body>
</html>
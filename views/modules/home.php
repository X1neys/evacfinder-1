<!-- modules/home.php -->
<?php
// Check if user is logged in and get user type
$isLoggedIn = isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok";
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : '';
$isLGU = ($userType === 'lgu');
?>

<div class="home-dashboard">
  <div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Welcome Section - Clean and Simple (Visible to ALL logged in users) -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div>
                <?php
                  $welcomeName = $_SESSION['username'] ?? '';
                  $firstName = $_SESSION['firstname'] ?? '';
                  $lastName = $_SESSION['lastname'] ?? '';
                  if ($welcomeName === '' && $firstName) {
                      $welcomeName = trim($firstName . ' ' . $lastName);
                  }
                  $currentDate = date('l, F d, Y');
                ?>
                <h4 class="mb-1 text-dark">Welcome back, <?php echo htmlspecialchars($welcomeName ?: 'User'); ?>!</h4>
                <p class="text-muted mb-0"><i class="fa fa-calendar-o me-1"></i> <?php echo $currentDate; ?></p>
              </div>
              <div class="mt-3 mt-sm-0">
                <span class="badge bg-light text-dark px-3 py-2">
                  <i class="fa fa-shield me-1"></i> EvacFinder System
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-12">
        <!-- Main Statistics Cards (Visible to ALL logged in users) -->
        <div class="row g-4 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="mb-1 text-uppercase text-muted fw-bold small">Total Centers</p>
                  <h2 class="mb-0" id="totalCenters">0</h2>
                </div>
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                  <i class="fa fa-building fa-2x text-primary"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="mb-1 text-uppercase text-muted fw-bold small">Active Centers</p>
                  <h2 class="mb-0" id="activeCenters">0</h2>
                </div>
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                  <i class="fa fa-check-circle fa-2x text-success"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="mb-1 text-uppercase text-muted fw-bold small">Total Evacuees</p>
                  <h2 class="mb-0" id="totalEvacuees">0</h2>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                  <i class="fa fa-users fa-2x text-warning"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <p class="mb-1 text-uppercase text-muted fw-bold small">Total Capacity</p>
                  <h2 class="mb-0" id="totalCapacity">0</h2>
                </div>
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                  <i class="fa fa-bed fa-2x text-info"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php if ($isLGU): ?>
        <!-- LGU-ONLY CONTENT STARTS HERE -->
        
        <!-- Charts Row -->
        <div class="row g-4 mb-4">
          <div class="col-xl-6">
            <div class="card shadow-sm">
              <div class="card-header bg-white">
                <h5 class="card-title mb-0"><i class="fa fa-chart-line me-2 text-primary"></i>Occupancy Trends</h5>
                <small class="text-muted">Last 7 days</small>
              </div>
              <div class="card-body">
                <canvas id="occupancyChart" height="250"></canvas>
              </div>
            </div>
          </div>
          <div class="col-xl-6">
            <div class="card shadow-sm">
              <div class="card-header bg-white">
                <h5 class="card-title mb-0"><i class="fa fa-chart-pie me-2 text-primary"></i>Center Status Distribution</h5>
              </div>
              <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Centers at a Glance -->
        <div class="row g-4 mb-4">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-white">
                <h5 class="card-title mb-0"><i class="fa fa-map-marker me-2 text-primary"></i>Centers at a Glance</h5>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Center Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Occupancy</th>
                        <th>Progress</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="centersTableBody">
                      <tr>
                        <td colspan="6" class="text-center py-4">
                          <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity & Enhanced Quick Actions -->
        <div class="row g-4">
          <div class="col-xl-6">
            <div class="card shadow-sm">
              <div class="card-header bg-white">
                <h5 class="card-title mb-0"><i class="fa fa-history me-2 text-primary"></i>Recent Activity</h5>
              </div>
              <div class="card-body p-0">
                <div class="list-group list-group-flush" id="recentActivityList">
                  <div class="list-group-item text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- ENHANCED Quick Actions Panel -->
          <div class="col-xl-6">
            <div class="card shadow-sm">
              <div class="card-header bg-white">
                <h5 class="card-title mb-0"><i class="fa fa-bolt me-2 text-primary"></i>Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <!-- Register Evacuee -->
                  <div class="col-6">
                    <a href="?route=evacuees" class="text-decoration-none">
                      <div class="evac-quick-action-card text-center p-3 rounded-3 border bg-light h-100">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background: rgba(40, 167, 69, 0.1);">
                          <i class="fa fa-user-plus fa-2x text-success"></i>
                        </div>
                        <h6 class="mb-1 fw-bold">Register Evacuee</h6>
                        <small class="text-muted d-block">Add new evacuee record</small>
                      </div>
                    </a>
                  </div>
                  <!-- Add Center -->
                  <div class="col-6">
                    <a href="?route=centers" class="text-decoration-none">
                      <div class="evac-quick-action-card text-center p-3 rounded-3 border bg-light h-100">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background: rgba(0, 123, 255, 0.1);">
                          <i class="fa fa-plus-circle fa-2x text-primary"></i>
                        </div>
                        <h6 class="mb-1 fw-bold">Add Center</h6>
                        <small class="text-muted d-block">Create new evacuation center</small>
                      </div>
                    </a>
                  </div>
                  <!-- Post Announcement -->
                  <div class="col-6">
                    <div onclick="showAnnouncementModal()" style="cursor: pointer;">
                      <div class="evac-quick-action-card text-center p-3 rounded-3 border bg-light h-100">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background: rgba(255, 193, 7, 0.1);">
                          <i class="fa fa-bullhorn fa-2x text-warning"></i>
                        </div>
                        <h6 class="mb-1 fw-bold">Post Announcement</h6>
                        <small class="text-muted d-block">Send alert or update</small>
                      </div>
                    </div>
                  </div>
                  <!-- Generate Report -->
                  <div class="col-6">
                    <div onclick="refreshDashboard()" style="cursor: pointer;">
                      <div class="evac-quick-action-card text-center p-3 rounded-3 border bg-light h-100">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background: rgba(23, 162, 184, 0.1);">
                          <i class="fa fa-file-text-o fa-2x text-info"></i>
                        </div>
                        <h6 class="mb-1 fw-bold">Generate Report</h6>
                        <small class="text-muted d-block">Export occupancy summary</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php endif; // End of LGU-only content ?>
        <!-- LGU-ONLY CONTENT ENDS HERE -->

      </div>
    </div>
  </div>
</div>

<!-- Announcement Modal (Only needed for LGU) -->
<?php if ($isLGU): ?>
<div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-bullhorn me-2"></i>Post Announcement</h5>
        <button type="button" class="close-modal-btn" onclick="closeModal('announcementModal')">
          <i class="fa fa-times-circle"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="quickAnnouncementForm">
          <div class="mb-3">
            <label class="form-label">Announcement Type</label>
            <select class="form-control" id="quick_ann_type" required>
              <option value="General">General Announcement</option>
              <option value="Advisory">Advisory</option>
              <option value="Event">Event</option>
              <option value="Memo">Memo</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" id="quick_ann_title" placeholder="Enter title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="quick_ann_desc" rows="3" placeholder="Enter announcement details..." required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('announcementModal')">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveQuickAnnouncement()">Post Announcement</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
.close-modal-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #dc3545;
    cursor: pointer;
    padding: 0;
    margin: 0;
    line-height: 1;
    transition: all 0.2s ease;
}

.close-modal-btn:hover {
    color: #bb2d3b;
    transform: scale(1.1);
}

.close-modal-btn:focus {
    outline: none;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Table styling */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

/* Progress bar styling */
.progress {
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
}

/* Chart containers */
canvas {
    max-width: 100%;
}

/* Card headers */
.card-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
}

/* Enhanced Quick Action Cards - No conflict with existing styles */
.evac-quick-action-card {
  transition: all 0.25s ease;
  background: #ffffff;
  border-color: #e9ecef !important;
  cursor: pointer;
}

.evac-quick-action-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  border-color: transparent !important;
}

.evac-quick-action-card:active {
  transform: translateY(-2px);
}
</style>

<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables
let occupancyChart = null;
let statusChart = null;
<?php echo $isLGU ? 'let isLGUUser = true;' : 'let isLGUUser = false;'; ?>

// Global close modal function
function closeModal(modalId) {
    $('#' + modalId).modal('hide');
    setTimeout(function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('overflow', '');
    }, 150);
}

// Global show announcement modal
function showAnnouncementModal() {
    if (!isLGUUser) return;
    $('#quick_ann_type').val('General');
    $('#quick_ann_title').val('');
    $('#quick_ann_desc').val('');
    $('#announcementModal').modal('show');
}

// Global refresh dashboard
function refreshDashboard() {
    if (!isLGUUser) return;
    Swal.fire({
        title: 'Refreshing...',
        text: 'Updating dashboard data',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            loadDashboardData();
            setTimeout(() => {
                Swal.close();
                Swal.fire('Updated!', 'Dashboard data has been refreshed', 'success');
            }, 1000);
        }
    });
}

// Global save announcement
function saveQuickAnnouncement() {
    if (!isLGUUser) return;
    
    var ann_type = $('#quick_ann_type').val();
    var ann_title = $('#quick_ann_title').val();
    var ann_desc = $('#quick_ann_desc').val();
    
    if (!ann_title || !ann_desc) {
        Swal.fire('Error', 'Please fill in all fields', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Post Announcement?',
        text: 'Are you sure you want to post this announcement?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, post',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "ajax/quick_announcement.ajax.php",
                method: "POST",
                data: {
                    ann_type: ann_type,
                    ann_title: ann_title,
                    ann_desc: ann_desc
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', 'Announcement posted successfully', 'success');
                        closeModal('announcementModal');
                        loadDashboardData();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to post announcement', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to post announcement', 'error');
                }
            });
        }
    });
}

// Dashboard functions
function loadDashboardData() {
    fetchDashboardStats();
    <?php if ($isLGU): ?>
    fetchCentersList();
    fetchRecentActivity();
    <?php endif; ?>
}

function fetchDashboardStats() {
    $.ajax({
        url: "ajax/dashboard_stats.ajax.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            if (data.success) {
                $('#totalCenters').text(data.total_centers || 0);
                $('#activeCenters').text(data.active_centers || 0);
                $('#totalEvacuees').text(data.total_evacuees || 0);
                $('#totalCapacity').text(data.total_capacity || 0);
                
                <?php if ($isLGU): ?>
                if (data.occupancy_trend) {
                    updateOccupancyChart(data.occupancy_trend);
                }
                if (data.status_distribution) {
                    updateStatusChart(data.status_distribution);
                }
                <?php endif; ?>
            }
        },
        error: function(xhr, status, error) {
            console.log('Failed to fetch dashboard stats:', error);
        }
    });
}

<?php if ($isLGU): ?>
function fetchCentersList() {
    $.ajax({
        url: "ajax/dashboard_centers.ajax.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            if (data.success && data.centers) {
                displayCentersTable(data.centers);
            }
        },
        error: function(xhr, status, error) {
            console.log('Failed to fetch centers:', error);
            $('#centersTableBody').html('<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load centers</td></tr>');
        }
    });
}

function displayCentersTable(centers) {
    let html = '';
    if (centers.length === 0) {
        html = '<tr><td colspan="6" class="text-center py-4">No evacuation centers found</td></tr>';
    } else {
        centers.forEach(function(center) {
            let statusClass = '';
            let statusText = center.status;
            if (statusText === 'Active') statusClass = 'bg-success';
            else if (statusText === 'Inactive') statusClass = 'bg-warning';
            else if (statusText === 'Full') statusClass = 'bg-warning text-dark';
            else statusClass = 'bg-danger';
            
            let occupancy = center.current_occupants || 0;
            let capacity = center.capacity || 1;
            let percent = Math.round((occupancy / capacity) * 100);
            
            let progressClass = 'bg-success';
            if (percent >= 90) progressClass = 'bg-danger';
            else if (percent >= 70) progressClass = 'bg-warning';
            
            html += `
                <tr>
                    <td><strong>${escapeHtml(center.center_name)}</strong>${center.category ? '<br><small class="text-muted">' + escapeHtml(center.category) + '</small>' : ''}</td>
                    <td>${escapeHtml(center.barangay || '')}${center.barangay ? ', ' : ''}${escapeHtml(center.city || '')}</td>
                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                    <td>${occupancy} / ${capacity}</td>
                    <td>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar ${progressClass}" style="width: ${percent}%"></div>
                        </div>
                        <small class="text-muted">${percent}% full</small>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-center" data-center-id="${center.center_id}">
                            <i class="fa fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    $('#centersTableBody').html(html);
    
    $('.view-center').off('click').on('click', function() {
        var centerId = $(this).data('center-id');
        window.location.href = '?route=active&highlight=' + centerId;
    });
}

function fetchRecentActivity() {
    $.ajax({
        url: "ajax/dashboard_activity.ajax.php",
        method: "GET",
        dataType: "json",
        success: function(data) {
            if (data.success && data.activities) {
                displayRecentActivity(data.activities);
            }
        },
        error: function(xhr, status, error) {
            console.log('Failed to fetch activity:', error);
            $('#recentActivityList').html('<div class="list-group-item text-center py-4 text-danger">Failed to load activity</div>');
        }
    });
}

function displayRecentActivity(activities) {
    let html = '';
    if (activities.length === 0) {
        html = '<div class="list-group-item text-center py-4 text-muted">No recent activity</div>';
    } else {
        activities.forEach(function(activity) {
            let icon = 'fa-info-circle';
            let iconColor = 'text-primary';
            
            if (activity.action_type === 'EVACUEE_ADDED') {
                icon = 'fa-user-plus';
                iconColor = 'text-success';
            } else if (activity.action_type === 'EVACUEE_STATUS_CHANGE') {
                icon = 'fa-exchange';
                iconColor = 'text-warning';
            } else if (activity.action_type === 'CENTER_UPDATED') {
                icon = 'fa-edit';
                iconColor = 'text-info';
            }
            
            html += `
                <div class="list-group-item">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fa ${icon} ${iconColor} fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small text-muted">${formatDate(activity.created_at)}</div>
                            <div class="mt-1">${escapeHtml(activity.description || 'No description')}</div>
                            <small class="text-muted">By: ${escapeHtml(activity.performed_by || 'System')}</small>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#recentActivityList').html(html);
}

function updateOccupancyChart(trendData) {
    const ctx = document.getElementById('occupancyChart').getContext('2d');
    
    if (occupancyChart) {
        occupancyChart.destroy();
    }
    
    occupancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.dates || [],
            datasets: [{
                label: 'Total Evacuees',
                data: trendData.values || [],
                borderColor: '#1e3c72',
                backgroundColor: 'rgba(30, 60, 114, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Evacuees'
                    }
                }
            }
        }
    });
}

function updateStatusChart(statusData) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    if (statusChart) {
        statusChart.destroy();
    }
    
    const colors = {
        'Active': '#28a745',
        'Inactive': '#6c757d',
        'Full': '#ffc107',
        'Under Maintenance': '#dc3545'
    };
    
    const labels = Object.keys(statusData);
    const values = Object.values(statusData);
    const backgroundColors = labels.map(label => colors[label] || '#6c757d');
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgroundColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
<?php endif; // End of LGU-only JavaScript functions ?>

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    var date = new Date(dateString);
    return date.toLocaleString();
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Initialize on document ready
$(document).ready(function() {
    loadDashboardData();
    
    // Auto-refresh every 30 seconds (only for LGU)
    <?php if ($isLGU): ?>
    setInterval(loadDashboardData, 30000);
    <?php endif; ?>
});
</script>
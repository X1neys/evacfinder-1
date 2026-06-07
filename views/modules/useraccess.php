<?php
// User Access management UI
$users = ModelUserRights::mdlListUsers();
$panels = ['home' => 'Dashboard', 'map' => 'Map', 'centers' => 'Centers', 'evacuees' => 'Evacuees', 'active' => 'Active Centers', 'announcement' => 'Announcement', 'useraccess' => 'User Access', 'assigned' => 'Assigned'];
// permission levels
$levels = ['full' => 'Full', 'view' => 'View', 'restricted' => 'Restricted'];
$currentUserPermissions = ModelUserRights::mdlGetPermissions($_SESSION['userid'] ?? '');
$isUserAccessViewOnly = isset($currentUserPermissions['useraccess']) && strtolower($currentUserPermissions['useraccess']) === 'view';
$centersWithLGU = ModelCenters::mdlGetCentersWithLGU();
$availableLguUsers = ModelCenters::mdlGetAllLGUUsers();
$lguAssignments = [];
foreach ($centersWithLGU as $center) {
    if (!empty($center['assigned_lgu_user_id'])) {
        $lguAssignments[$center['assigned_lgu_user_id']] = [
            'center_id' => $center['center_id'],
            'center_name' => $center['center_name'],
        ];
    }
}
?>
<style>
  /* Apply readable select text only when dark theme is active */
  body.theme-dark .user-access-section .form-select,
  body.theme-dark .user-access-section .form-select option {
    color: #ffffff !important;
    background-color: transparent !important;
  }

  /* Support bootstrap-select styled dropdowns (if used) in dark theme */
  body.theme-dark .user-access-section .bootstrap-select .dropdown-toggle,
  body.theme-dark .user-access-section .bootstrap-select .btn,
  body.theme-dark .user-access-section .bootstrap-select .dropdown-toggle .filter-option {
    color: #ffffff !important;
  }

  body.theme-dark .user-access-section .bootstrap-select .dropdown-menu .inner li a,
  body.theme-dark .user-access-section .bootstrap-select .dropdown-menu .inner li a span {
    color: #ffffff !important;
    background-color: transparent !important;
  }

  body.theme-dark .user-access-section .bootstrap-select .dropdown-menu .inner li a:hover,
  body.theme-dark .user-access-section .bootstrap-select .dropdown-menu .inner li.selected a {
    background-color: rgba(255,255,255,0.08) !important;
    color: #ffffff !important;
  }

  /* Fallback for other custom dropdowns in dark theme */
  body.theme-dark .user-access-section .dropdown-menu a,
  body.theme-dark .user-access-section .dropdown-menu .dropdown-item {
    color: #ffffff !important;
  }

  /* Assignment panel has a blue background for better visibility */
  .assignment-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 420px;
    height: 100%;
    background: #1f4d9d;
    color: #fff;
    box-shadow: -4px 0 16px rgba(0, 0, 0, 0.3);
    transform: translateX(100%);
    transition: transform 0.25s ease;
    z-index: 1100;
    overflow-y: auto;
    padding: 20px;
  }

  .assignment-panel.open {
    transform: translateX(0);
  }

  .assignment-panel-header {
    border-bottom: 1px solid rgba(255,255,255,0.08);
    margin-bottom: 16px;
  }

  /* Keep the assignment panel controls readable (panel is dark regardless of site theme) */
  .assignment-panel .form-control,
  .assignment-panel .form-select {
    background: rgba(255,255,255,0.06);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.12);
  }

  .assignment-panel .form-select option {
    background: #21212f;
    color: #fff;
  }

  /* Bootstrap-select inner option text; only force when dark theme is active */
  body.theme-dark .bootstrap-select .dropdown-toggle .filter-option-inner-inner {
    color: #ffffff !important;
  }

  body.theme-dark .bootstrap-select .dropdown-menu .dropdown-item {
    color: #ffffff !important;
    background-color: #1f1f2b !important;
  }

  body.theme-dark .bootstrap-select .dropdown-menu .dropdown-item.active,
  body.theme-dark .bootstrap-select .dropdown-menu .show .dropdown-item.active {
    background-color: rgba(255,255,255,0.08) !important;
    color: #ffffff !important;
  }

  .assignment-panel .table {
    color: #fff;
  }

  .assignment-panel .table th,
  .assignment-panel .table td {
    border-color: rgba(255,255,255,0.12);
  }
</style>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">User Access</h4>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
              <input id="userAccessSearch" type="text" class="form-control" placeholder="Search by email or name">
            </div>
          </div>
        </div>

        <div class="user-access-section mb-4">
          <div class="card border-primary">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0">LGU Users</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table id="userAccessTable" class="table table-sm table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Email</th>
                      <?php foreach ($panels as $key => $label): ?>
                        <th><?php echo htmlspecialchars($label); ?></th>
                      <?php endforeach; ?>
                      <th>Save</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($users as $u): ?>
                      <?php if (strtolower($u['Type'] ?? $u['type'] ?? '') !== 'lgu') continue; ?>
                      <?php $perms = ModelUserRights::mdlGetPermissions($u['userid']); ?>
                      <tr data-userid="<?php echo htmlspecialchars($u['userid']); ?>" data-type="lgu" data-name="<?php echo htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name'])); ?>" data-email="<?php echo htmlspecialchars($u['email']); ?>">
                        <td><?php echo htmlspecialchars(($u['first_name'] ? $u['first_name'] . ' ' . $u['last_name'] : $u['userid'])); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <?php foreach ($panels as $key => $label): ?>
                          <td style="min-width:140px;">
                            <select class="form-select form-select-sm perm-select" data-panel="<?php echo htmlspecialchars($key); ?>"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?> >
                              <?php foreach ($levels as $lvKey => $lvLabel):
                                $sel = (isset($perms[$key]) && strtolower($perms[$key]) === $lvKey) ? 'selected' : '';
                              ?>
                                <option value="<?php echo $lvKey; ?>" <?php echo $sel; ?>><?php echo $lvLabel; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </td>
                        <?php endforeach; ?>
                        <td>
                          <button class="btn btn-sm btn-primary save-perms"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?>>Save</button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
          <button id="openAssignmentPanel" class="btn btn-primary"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?>>Assign LGU Officer</button>
        </div>

        <div id="assignmentPanel" class="assignment-panel">
          <div class="assignment-panel-header d-flex justify-content-between align-items-center">
            <div></div>
            <button id="closeAssignmentPanel" type="button" class="btn btn-sm btn-light">Close</button>
          </div>
          <div class="assignment-panel-body pt-3">
            <div class="mb-3">
              <label for="assignmentLguSelect" class="form-label">LGU Officers (<?php echo count($availableLguUsers); ?>)</label>
              <select id="assignmentLguSelect" class="form-select">
                <?php if (count($availableLguUsers)): ?>
                  <?php foreach ($availableLguUsers as $lgu): ?>
                    <?php $assignedCenterId = $lguAssignments[$lgu['userid'] ?? $lgu['lgu_id']]['center_id'] ?? ''; ?>
                    <?php $assignedCenterName = $lguAssignments[$lgu['userid'] ?? $lgu['lgu_id']]['center_name'] ?? ''; ?>
                    <option value="<?php echo htmlspecialchars($lgu['userid'] ?: $lgu['lgu_id']); ?>" data-assigned-center-id="<?php echo htmlspecialchars($assignedCenterId); ?>" data-assigned-center-name="<?php echo htmlspecialchars($assignedCenterName); ?>" data-userid="<?php echo htmlspecialchars($lgu['userid'] ?? ''); ?>" data-email="<?php echo htmlspecialchars($lgu['user_email'] ?? $lgu['office_email_address'] ?? ''); ?>"><?php echo htmlspecialchars(trim($lgu['lgu_office_name'] . ' — ' . $lgu['first_name'] . ' ' . $lgu['last_name'])); ?></option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value="">No available LGU officers</option>
                <?php endif; ?>
              </select>
              <!-- assignment status message removed per request -->
            </div>
            <div class="mb-3">
              <label for="assignmentCenterSelect" class="form-label">Evacuation Center</label>
              <select id="assignmentCenterSelect" class="form-select">
                <?php foreach ($centersWithLGU as $center): ?>
                  <?php
                    $assignedNames = [];
                    $assignedContacts = [];
                    if (!empty($center['assigned_lgus_concat'])) {
                      $parts = explode(';;', $center['assigned_lgus_concat']);
                      foreach ($parts as $p) {
                        $pair = explode('|', $p, 2);
                        $name = trim($pair[0] ?? '');
                        $contact = trim($pair[1] ?? '');
                        if ($name !== '') $assignedNames[] = $name;
                        if ($contact !== '') $assignedContacts[] = $contact;
                      }
                    }
                    $assignedCount = count($assignedNames) ?: intval($center['assigned_lgu_count'] ?? 0);
                    $assignedLabel = '';
                    if ($assignedCount > 1) {
                      $assignedLabel = ' — Currently: ' . htmlspecialchars($assignedNames[0] ?? ($center['assigned_lgu_name'] ?: 'LGU officers')) . ' +' . ($assignedCount - 1);
                    } elseif ($assignedCount === 1) {
                      $assignedLabel = ' — Currently: ' . htmlspecialchars($assignedNames[0] ?? ($center['assigned_lgu_name'] ?: 'Assigned'));
                    }
                    $contactDisplay = $assignedContacts ? htmlspecialchars(implode(' / ', $assignedContacts)) : ($center['assigned_lgu_phone'] ?: 'N/A');
                  ?>
                  <option value="<?php echo htmlspecialchars($center['center_id']); ?>"><?php echo htmlspecialchars($center['center_name']); ?><?php echo $assignedLabel; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3 text-end">
              <button id="assignLguButton" class="btn btn-success"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?>>Assign</button>
            </div>
            <div class="mb-3">
              <h6 class="text-white">Current Center Assignments</h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>Center</th>
                      <th>Assigned LGU</th>
                      <th>Contact</th>
                        <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                      <?php foreach ($centersWithLGU as $center): ?>
                        <?php
                          $assignedNames = [];
                          $assignedContacts = [];
                          if (!empty($center['assigned_lgus_concat'])) {
                            $parts = explode(';;', $center['assigned_lgus_concat']);
                            foreach ($parts as $p) {
                              $pair = explode('|', $p, 2);
                              $name = trim($pair[0] ?? '');
                              $contact = trim($pair[1] ?? '');
                              if ($name !== '') $assignedNames[] = $name;
                              if ($contact !== '') $assignedContacts[] = $contact;
                            }
                          }
                          $assignedCount = count($assignedNames) ?: intval($center['assigned_lgu_count'] ?? 0);
                          if ($assignedCount > 1) {
                            $assignedText = htmlspecialchars(($assignedNames[0] ?? $center['assigned_lgu_name'] ?? '') . ' +' . ($assignedCount - 1));
                          } elseif ($assignedCount === 1) {
                            $assignedText = htmlspecialchars($assignedNames[0] ?? ($center['assigned_lgu_name'] ?? ''));
                          } else {
                            $assignedText = 'None';
                          }
                          $contactDisplay = $assignedContacts ? htmlspecialchars(implode(' / ', $assignedContacts)) : ($center['assigned_lgu_phone'] ?: 'N/A');
                        ?>
                        <tr>
                          <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                          <td><?php echo $assignedText; ?></td>
                          <td><?php echo $contactDisplay; ?></td>
                          <td>
                            <?php if ($assignedCount > 0): ?>
                              <button class="btn btn-sm btn-danger remove-assignment" data-center-id="<?php echo htmlspecialchars($center['center_id']); ?>"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?>>Remove</button>
                            <?php else: ?>
                              <span class="text-muted">—</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="user-access-section">
          <div class="card border-primary">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0">Public Users</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Email</th>
                      <?php foreach ($panels as $key => $label): ?>
                        <th><?php echo htmlspecialchars($label); ?></th>
                      <?php endforeach; ?>
                      <th>Save</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($users as $u): ?>
                      <?php if (strtolower($u['Type'] ?? $u['type'] ?? '') === 'lgu') continue; ?>
                      <?php $perms = ModelUserRights::mdlGetPermissions($u['userid']); ?>
                      <tr data-userid="<?php echo htmlspecialchars($u['userid']); ?>" data-type="public" data-name="<?php echo htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name'])); ?>" data-email="<?php echo htmlspecialchars($u['email']); ?>">
                        <td><?php echo htmlspecialchars(($u['first_name'] ? $u['first_name'] . ' ' . $u['last_name'] : $u['userid'])); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <?php foreach ($panels as $key => $label): ?>
                          <td style="min-width:140px;">
                            <select class="form-select form-select-sm perm-select" data-panel="<?php echo htmlspecialchars($key); ?>"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?> >
                              <?php foreach ($levels as $lvKey => $lvLabel):
                                $sel = (isset($perms[$key]) && strtolower($perms[$key]) === $lvKey) ? 'selected' : '';
                              ?>
                                <option value="<?php echo $lvKey; ?>" <?php echo $sel; ?>><?php echo $lvLabel; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </td>
                        <?php endforeach; ?>
                        <td>
                          <button class="btn btn-sm btn-primary save-perms"<?php echo $isUserAccessViewOnly ? ' disabled' : ''; ?>>Save</button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var searchInput = document.getElementById('userAccessSearch');

  function filterUsers() {
    var term = searchInput.value.trim().toLowerCase();
    document.querySelectorAll('tr[data-userid]').forEach(function(row){
      var name = (row.getAttribute('data-name') || '').toLowerCase();
      var email = (row.getAttribute('data-email') || '').toLowerCase();
      var userid = (row.getAttribute('data-userid') || '').toLowerCase();
      var visible = !term || name.indexOf(term) !== -1 || email.indexOf(term) !== -1 || userid.indexOf(term) !== -1;
      row.style.display = visible ? '' : 'none';
    });
    document.querySelectorAll('.user-access-section').forEach(function(section){
      var visibleRow = section.querySelectorAll('tbody tr:not([style*="display: none"])');
      section.style.display = visibleRow.length ? '' : 'none';
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterUsers);
  }

  document.querySelectorAll('.save-perms').forEach(function(btn){
    btn.addEventListener('click', function(e){
      var row = e.currentTarget.closest('tr');
      var userid = row.getAttribute('data-userid');
      var selects = row.querySelectorAll('.perm-select');
      var perms = {};
      selects.forEach(function(s){ perms[s.getAttribute('data-panel')] = s.value; });

      var fd = new FormData();
      fd.append('userid', userid);
      fd.append('permissions', JSON.stringify(perms));

      fetch('ajax/save_user_permissions.ajax.php', { method: 'POST', credentials: 'same-origin', body: fd })
        .then(function(res){ return res.json(); })
        .then(function(json){
          if (json && json.success) {
            Swal.fire({ icon: 'success', title: 'Saved', text: json.message, timer: 1200, showConfirmButton: false });
          } else {
            Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to save' });
          }
        }).catch(function(){
          Swal.fire({ icon: 'error', title: 'Error', text: 'Server error' });
        });
    });
  });

    var assignmentPanel = document.getElementById('assignmentPanel');
    var openAssignmentPanel = document.getElementById('openAssignmentPanel');
    var closeAssignmentPanel = document.getElementById('closeAssignmentPanel');
    var assignLguButton = document.getElementById('assignLguButton');

    if (openAssignmentPanel && assignmentPanel) {
      openAssignmentPanel.addEventListener('click', function() {
        assignmentPanel.classList.add('open');
      });
    }

    var assignmentCenterSelect = document.getElementById('assignmentCenterSelect');
    var assignmentLguSelect = document.getElementById('assignmentLguSelect');
    // assignmentLguStatus element intentionally removed; no status text shown here

    function updateSelectedLguAssignment() {
      if (!assignmentLguSelect) return;
      var selectedOption = assignmentLguSelect.selectedOptions[0];
      var assignedCenterId = selectedOption ? selectedOption.dataset.assignedCenterId : '';
      if (assignedCenterId && assignmentCenterSelect) {
        var target = assignmentCenterSelect.querySelector('option[value="' + assignedCenterId + '"]');
        if (target) {
          assignmentCenterSelect.value = assignedCenterId;
        }
      }
    }

    if (assignmentLguSelect) {
      assignmentLguSelect.addEventListener('change', updateSelectedLguAssignment);
      updateSelectedLguAssignment();
    }

    if (closeAssignmentPanel && assignmentPanel) {
      closeAssignmentPanel.addEventListener('click', function() {
        assignmentPanel.classList.remove('open');
      });
    }

    if (assignLguButton) {
      assignLguButton.addEventListener('click', function() {
        var lguSelect = document.getElementById('assignmentLguSelect');
        var centerSelect = document.getElementById('assignmentCenterSelect');
        var lguId = lguSelect ? lguSelect.value : '';
        var centerId = centerSelect ? centerSelect.value : '';

        if (!lguId || !centerId) {
          Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Select an LGU officer and center first.' });
          return;
        }

        var fd = new FormData();
        fd.append('lgu_user_id', lguId);
        fd.append('center_id', centerId);

        fetch('ajax/assign_lgu_to_center.ajax.php', { method: 'POST', credentials: 'same-origin', body: fd })
          .then(function(res){ return res.json(); })
          .then(function(json){
            if (json && json.success) {
              Swal.fire({ icon: 'success', title: 'Assigned', text: json.message, timer: 1200, showConfirmButton: false });
              setTimeout(function(){ location.reload(); }, 1300);
            } else {
              Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to assign' });
            }
          }).catch(function(){
            Swal.fire({ icon: 'error', title: 'Error', text: 'Server error' });
          });
      });
    }

    // Remove assignment handlers
    document.querySelectorAll('.remove-assignment').forEach(function(btn){
      btn.addEventListener('click', function(e){
        var centerId = e.currentTarget.getAttribute('data-center-id');
        if (!centerId) return;

        Swal.fire({
          title: 'Remove assignment?',
          text: 'This will unassign the LGU from the center.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, remove',
        }).then(function(result){
          if (!result.isConfirmed) return;

          var fd = new FormData();
          fd.append('center_id', centerId);

          fetch('ajax/remove_lgu_assignment.ajax.php', { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function(res){ return res.json(); })
            .then(function(json){
              if (json && json.success) {
                Swal.fire({ icon: 'success', title: 'Removed', text: json.message, timer: 1100, showConfirmButton: false });
                setTimeout(function(){ location.reload(); }, 1200);
              } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'Failed to remove assignment' });
              }
            }).catch(function(){
              Swal.fire({ icon: 'error', title: 'Error', text: 'Server error' });
            });
        });
      });
    });
    
    // Assignment preview removed — no preview fetches or rendering.
  });
</script>

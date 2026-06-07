<!-- Nav Header (Logo) -->
<div class="nav-header">
  <a href="?route=map" class="brand-logo">
    <img class="logo-centered" src="views/assets/images/evaclogo.png" alt="EvacFinder Logo">
    <!-- <img class="logo-abbr"    src="views/assets/images/logo-white.png"      alt="Logo">
    <img class="logo-compact" src="views/assets/images/logo-text-white.png" alt="Logo Text">
    <img class="brand-title"  src="views/assets/images/logo-text-white.png" alt="Brand"> -->
  </a>
  <?php if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok"): ?>
    <div class="nav-control">
      <div class="hamburger">
        <span class="line"></span>
        <span class="line"></span>
        <span class="line"></span>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php
$profileInfo = null;
$lguInfo = null;
$publicInfo = null;
$profileUserName = 'User';
$profileEmail = '';
$profileUserId = $_SESSION['userid'] ?? '';
$profileRole = 'USER';
if (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok") {
    $userEmail = $_SESSION["email"] ?? null;
    if ($userEmail) {
        $profileInfo = ModelUserRights::mdlGetUserCredentials('userrights', 'email', $userEmail);
        $profileEmail = $userEmail;
        if (!empty($profileInfo)) {
            $profileRole = strtoupper($profileInfo['Type'] ?? 'user');
        }
        $profileType = strtolower($profileInfo['Type'] ?? $profileInfo['type'] ?? '');
        if (!empty($profileInfo) && $profileType === 'lgu') {
            $currentUserId = $_SESSION['userid'] ?? null;
            if ($currentUserId) {
                $lguInfo = ModelUserRights::mdlGetUserCredentials('lgu_users', 'lgu_id', $currentUserId);
                if (empty($lguInfo) && preg_match('/^\d+$/', $currentUserId)) {
                    $lguInfo = ModelUserRights::mdlGetUserCredentials('lgu_users', 'lgu_id', 'LGU' . $currentUserId);
                }
            }
            if (empty($lguInfo)) {
                $lguInfo = ModelUserRights::mdlGetUserCredentials('lgu_users', 'office_email_address', $userEmail);
            }
        } elseif (!empty($profileInfo) && $profileType === 'public') {
            $publicInfo = ModelUserRights::mdlGetUserCredentials('personal_users', 'email_address', $userEmail);
        }
        if (!empty($lguInfo)) {
            $profileUserName = trim(($lguInfo['first_name'] ?? '') . ' ' . ($lguInfo['last_name'] ?? '')) ?: $profileUserName;
        } elseif (!empty($publicInfo)) {
            $profileUserName = trim(($publicInfo['first_name'] ?? '') . ' ' . ($publicInfo['last_name'] ?? '')) ?: $profileUserName;
        } elseif (!empty($_SESSION['username'])) {
            $profileUserName = $_SESSION['username'];
        }
    }
}

$profileEditMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profileEditSubmit']) && isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === 'ok') {
  $firstName = trim($_POST['firstName'] ?? '');
  $lastName = trim($_POST['lastName'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $officeEmail = trim($_POST['officeEmail'] ?? '');
  $contact = trim($_POST['contact'] ?? '');
  $newPassword = trim($_POST['newPassword'] ?? '');
  $confirmPassword = trim($_POST['confirmPassword'] ?? '');

  if ($firstName === '' || $lastName === '' || $email === '') {
    $profileEditMessage = 'Please enter your full name and email.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $profileEditMessage = 'Please enter a valid account email address.';
  } elseif (!empty($officeEmail) && !filter_var($officeEmail, FILTER_VALIDATE_EMAIL)) {
    $profileEditMessage = 'Please enter a valid office email address.';
  } elseif (!empty($officeEmail) && strtolower($officeEmail) === strtolower($email)) {
    $profileEditMessage = 'Office email must be different from account email.';
  } elseif (isset($profileInfo['Type']) && $profileInfo['Type'] === 'lgu' && empty($officeEmail)) {
    $profileEditMessage = 'Please provide an office email address.';
  } elseif (($newPassword !== '' || $confirmPassword !== '') && ($newPassword === '' || $confirmPassword === '')) {
    $profileEditMessage = 'Please fill both password fields to change your password.';
  } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
    $profileEditMessage = 'Passwords do not match.';
  } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
    $profileEditMessage = 'Password must be at least 8 characters.';
  } elseif ($newPassword !== '' && !preg_match('/[0-9]/', $newPassword)) {
    $profileEditMessage = 'Password must include at least one number.';
  } elseif ($newPassword !== '' && !preg_match('/[A-Z]/', $newPassword)) {
    $profileEditMessage = 'Password must include at least one uppercase letter.';
  } elseif ($newPassword !== '' && !preg_match('/[\W_]/', $newPassword)) {
    $profileEditMessage = 'Password must include at least one symbol.';
  } else {
    $currentUserId = $_SESSION['userid'] ?? '';
    if ($currentUserId) {
      if (!empty($email)) {
        $existing = ModelUserRights::mdlGetUserCredentials('userrights', 'email', $email);
        if (!empty($existing) && isset($existing['userid']) && $existing['userid'] !== $currentUserId) {
          $profileEditMessage = 'That email address is already in use by another account.';
        } else {
          $passwordToSave = ($newPassword !== '') ? $newPassword : null;
          $officeEmailToSave = (!empty($officeEmail) && isset($profileInfo['Type']) && $profileInfo['Type'] === 'lgu') ? $officeEmail : null;
          $updated = ModelUserRights::mdlUpdateUserProfile($currentUserId, $email, $firstName, $lastName, $contact, $officeEmailToSave, $passwordToSave);
          if ($updated) {
            $profileEditMessage = 'Profile updated successfully.';
            $_SESSION['email'] = $email;
            $_SESSION['username'] = trim($firstName . ' ' . $lastName);
          } else {
            $debug = ModelUserRights::$lastError ?? '';
            $profileEditMessage = 'Unable to update profile. Please try again.' . (!empty($debug) ? ' Error: ' . htmlspecialchars($debug, ENT_QUOTES, 'UTF-8') : '');
          }
        }
      } else {
        $profileEditMessage = 'Please provide an email address.';
      }
    } else {
      $profileEditMessage = 'Unable to identify your account.';
    }
  }
}

function profileDisplayValue($value, $default = 'N/A') {
    return $value ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $default;
}
?>
<style>
#profileEditModal .modal-body {
  padding: 1.25rem 1.25rem 0.9rem 1.25rem !important;
}
#profileEditModal .text-center {
  margin-bottom: 1rem !important;
}
#profileEditModal .text-center h5 {
  margin-top: 0.75rem !important;
  margin-bottom: 0.5rem !important;
  font-size: 1.25rem;
}
#profileEditModal .text-center p {
  margin-bottom: 0 !important;
  font-size: 0.9rem;
}
#profileEditModal .form-label {
  margin-bottom: 0.4rem !important;
  font-size: 0.9rem;
  font-weight: 500;
}
#profileEditModal .form-control {
  padding: 0.5rem 0.875rem !important;
  font-size: 0.95rem;
}
#profileEditModal .row > div {
  margin-bottom: 0.75rem !important;
}
#profileEditModal .mb-3 {
  margin-bottom: 0.75rem !important;
}
#profileEditModal hr {
  margin: 0.75rem 0 !important;
}
#profileEditModal .alert {
  margin-bottom: 1rem !important;
  padding: 0.5rem 0.75rem !important;
  font-size: 0.9rem;
}
#profileEditModal .modal-footer {
  padding: 0.75rem 1.5rem !important;
  gap: 0.5rem;
}

/* Constrain dialog size and enable internal scrolling to shorten overall modal */
#profileEditModal .modal-dialog {
  max-width: 540px; /* narrower dialog */
  margin: 1.5rem auto;
}
#profileEditModal .modal-content {
  max-height: 80vh; /* limit total modal height */
  overflow: hidden;
}
#profileEditModal .modal-body {
  /* leave room for header/footer height (approx 120px) */
  max-height: calc(80vh - 120px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

body.theme-dark {
  background-color: #0f172a;
  color: #e2e8f0;
}
body.theme-dark .header,
body.theme-dark .header .header-content {
  background-color: #102040 !important;
  color: #e2e8f0 !important;
}
body.theme-dark .nav-header {
  background-color: #102040 !important;
  box-shadow: none;
}
body.theme-dark .deznav {
  background-color: #102040 !important;
  box-shadow: 0px 0px 40px 0px rgba(0, 0, 0, 0.24) !important;
}
body.theme-dark .deznav .metismenu a {
  color: #e2e8f0 !important;
}
body.theme-dark .nav-header .brand-logo img {
  opacity: 1;
}
body.theme-dark .navbar-nav .nav-link,
body.theme-dark .navbar-nav .dropdown-item {
  color: #e2e8f0 !important;
}
body.theme-dark .card,
body.theme-dark .modal-content,
body.theme-dark .dropdown-menu,
body.theme-dark .content-body,
body.theme-dark .table,
body.theme-dark .table thead th,
body.theme-dark .table tbody td {
  background-color: #0b1220 !important;
  color: #e2e8f0 !important;
}
body.theme-dark .card {
  border-color: #0a1624 !important;
}
body.theme-dark .form-control {
  background-color: #12243b !important;
  border-color: #203651 !important;
}
body.theme-dark .form-control::placeholder {
  color: #94a3b8 !important;
}
body.theme-dark select.form-select,
body.theme-dark select,
body.theme-dark .bootstrap-select .dropdown-toggle,
body.theme-dark .bootstrap-select .btn,
body.theme-dark .bootstrap-select .dropdown-toggle .filter-option {
  color: #fff !important;
}
body.theme-dark select.form-select option,
body.theme-dark select option,
body.theme-dark .bootstrap-select .dropdown-menu .dropdown-item,
body.theme-dark .bootstrap-select .dropdown-menu .inner li a,
body.theme-dark .bootstrap-select .dropdown-menu .inner li a span {
  color: #fff !important;
  background-color: #12243b !important;
}
body.theme-dark .text-muted,
body.theme-dark .text-white-75 {
  color: #94a3b8 !important;
}

.dashboard-card {
  border: none;
  overflow: hidden;
}
.dashboard-card .card-body {
  min-height: 170px;
}
.dashboard-card.dashboard-card-primary {
  background: linear-gradient(135deg, #2163d9 0%, #3b8bff 100%) !important;
}
.dashboard-card.dashboard-card-success {
  background: linear-gradient(135deg, #6fc42f 0%, #47a11a 100%) !important;
}
.dashboard-card.dashboard-card-info {
  background: linear-gradient(135deg, #38d9d4 0%, #1aa6a4 100%) !important;
}
.stats-card {
  background-color: #f8fafc;
  border-color: #e2e8f0;
}
.stats-card-icon {
  width: 60px;
  height: 60px;
  min-width: 60px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
}
.stats-card-icon-primary { background: #1c5dbf; }
.stats-card-icon-success { background: #2d8f30; }
.stats-card-icon-warning { background: #d18f17; }
.stats-card-icon-info { background: #15a5b0; }

body.theme-dark .dashboard-card.dashboard-card-primary {
  background: linear-gradient(135deg, #0f315f 0%, #2369b5 100%) !important;
}
body.theme-dark .dashboard-card.dashboard-card-success {
  background: linear-gradient(135deg, #1f471f 0%, #2d7730 100%) !important;
}
body.theme-dark .dashboard-card.dashboard-card-info {
  background: linear-gradient(135deg, #0f6360 0%, #18888a 100%) !important;
}
body.theme-dark .stats-card {
  background-color: #071018 !important;
  border-color: #071722 !important;
}
body.theme-dark .stats-card-icon-primary { background: #0f3a67; }
body.theme-dark .stats-card-icon-success { background: #216a25; }
body.theme-dark .stats-card-icon-warning { background: #a26c10; }
body.theme-dark .stats-card-icon-info { background: #0c7c7b; }
body.theme-dark .btn-outline-secondary {
  color: #e2e8f0;
  background-color: rgba(255,255,255,0.06);
  border-color: rgba(226,232,240,.18);
}
body.theme-dark .btn-light {
  background: #144a8d !important;
  color: #e2e8f0 !important;
  border-color: #1f4f8f !important;
}
body.theme-dark .btn-primary {
  background-color: #1976d2 !important;
  border-color: #1976d2 !important;
}
body.theme-dark .btn-primary:hover {
  background-color: #165ea9 !important;
  border-color: #165ea9 !important;
}
body.theme-dark .badge.bg-light {
  background: #144a8d !important;
  color: #e2e8f0 !important;
}
body.theme-dark .bg-light,
body.theme-dark .bg-white,
body.theme-dark .card-body.bg-light,
body.theme-dark .card-body.bg-white {
  background-color: #12243b !important;
  color: #e2e8f0 !important;
  border-color: #1f2a44 !important;
}
body.theme-dark .modal-body {
  color: #e2e8f0;
}
body.theme-dark .alert {
  background-color: #12263b;
  border-color: #1f2a44;
  color: #e2e8f0;
}
body.theme-dark .dropdown-menu {
  background-color: #102040 !important;
}
body.theme-dark .sidebar {
  background-color: #12243b !important;
}
body.theme-dark .navbar-expand .navbar-nav .nav-link {
  color: #e2e8f0 !important;
}

.dark-mode-btn {
  background-color: #f0f4f8 !important;
  color: #1976d2 !important;
  border-radius: 8px !important;
  transition: all 0.3s ease;
  font-weight: 600;
}
.dark-mode-btn:hover {
  background-color: #e0e8f0 !important;
  transform: scale(1.08);
  box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3) !important;
}
.dark-mode-btn:active {
  transform: scale(0.95);
}

body.theme-dark .dark-mode-btn {
  background-color: #1f3f5f !important;
  color: #ffd700 !important;
  border-color: #ffd700 !important;
}
body.theme-dark .dark-mode-btn:hover {
  background-color: #2a5480 !important;
  box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4) !important;
}
</style>

<!-- Top Navbar -->
<div class="header">
  <div class="header-content">
    <nav class="navbar navbar-expand">
      <div class="collapse navbar-collapse justify-content-end">



        <!-- Right: Show Login or Profile based on session -->
        <ul class="navbar-nav header-right align-items-center">
          <li class="nav-item me-3">
            <button type="button" class="btn btn-outline-secondary dark-mode-btn" id="darkModeToggle" title="Toggle dark mode" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.3s ease; border: 2px solid #007bff;">
              <i class="fa fa-lightbulb-o"></i>
            </button>
          </li>
          <?php 
            $isLoggedIn = isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok";
          ?>
          
          <?php if(!$isLoggedIn): ?>
            <!-- Show Login Button when not logged in -->
            <li class="nav-item">
              <a href="?route=login" class="btn btn-primary btn-sm">
                <i class="mdi mdi-login"></i> Login
              </a>
            </li>
          <?php else: ?>
            <!-- Show Profile Dropdown when logged in -->
            <li class="nav-item dropdown header-profile">
              <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                <i class="mdi mdi-account-circle font-size-h3"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <a href="#" class="dropdown-item ai-icon" data-toggle="modal" data-target="#profileModal">
                  <i class="ti-user text-primary"></i> Profile
                </a>
                <a href="?route=logout" class="dropdown-item ai-icon">
                  <i class="ti-close text-danger"></i> Logout
                </a>
              </div>
            </li>
          <?php endif; ?>
        </ul>

      </div>
    </nav>
  </div>
</div>

<!-- Profile Popup Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header p-0 border-0 bg-primary text-white rounded-top">
          <div class="d-flex align-items-center p-4 w-100" style="position:relative;">
          <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width:64px; height:64px; font-size:24px; font-weight:700;">
            <?php echo strtoupper(substr($profileUserName, 0, 1)); ?>
          </div>
          <div class="ms-3" style="margin-left: 8px;">
            <h5 class="mb-1 text-white"><?php echo htmlspecialchars($profileUserName, ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="mb-0 text-white-75"><?php echo profileDisplayValue($profileEmail, 'No email'); ?></p>
          </div>
          <span class="badge bg-light text-primary py-2 px-3" style="font-size:.85rem; letter-spacing:.04em; position:absolute; right:24px; top:20px;"><?php echo htmlspecialchars($profileRole, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      </div>
        <div class="modal-body p-3">
        <div class="row gy-3">
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="mb-4 text-uppercase text-muted">Account Information</h6>
                <div class="mb-3 d-flex justify-content-between">
                  <span class="text-muted">Full name</span>
                  <strong id="profileDisplayName"><?php echo profileDisplayValue($profileUserName); ?></strong>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                  <span class="text-muted">Email address</span>
                  <strong id="profileDisplayEmail"><?php echo profileDisplayValue($profileEmail); ?></strong>
                </div>
                <?php
                  $contactNum = '';
                  $officeNum = '';
                  if (is_array($publicInfo)) {
                    $contactNum = $publicInfo['phone_number'] ?? '';
                  } elseif (is_array($lguInfo)) {
                    $contactNum = $lguInfo['contact_number'] ?? '';
                    $officeNum = $lguInfo['office_number'] ?? '';
                  }

                  $areaAssigned = '';
                  if (is_array($lguInfo)) {
                    $province = trim($lguInfo['province'] ?? '');
                    $region = trim($lguInfo['region'] ?? '');
                    if ($province === '' && $region === '') {
                      $areaAssigned = '';
                    } else {
                      $areaAssigned = $province . ($region !== '' ? ' / ' . $region : '');
                    }
                  }
                ?>
                <div class="mb-3 d-flex justify-content-between">
                  <span class="text-muted">Number</span>
                  <strong id="profileDisplayContact"><?php echo profileDisplayValue($contactNum ?: ($officeNum ?: 'N/A')); ?></strong>
                </div>
                <?php if (!empty($lguInfo) && is_array($lguInfo)): ?>
                <div id="lguDetailsSection" class="d-none">
                  <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">LGU email address</span>
                    <strong id="profileDisplayOfficeEmail"><?php echo profileDisplayValue($lguInfo['office_email_address'] ?? '', 'N/A'); ?></strong>
                  </div>
                  <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Office number</span>
                    <strong id="profileDisplayLguOfficeNumber"><?php echo profileDisplayValue($officeNum, 'N/A'); ?></strong>
                  </div>
                  <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Office / agency</span>
                    <strong><?php echo profileDisplayValue($lguInfo['lgu_office_name'] ?? '', 'N/A'); ?></strong>
                  </div>
                  <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">Area assigned</span>
                    <strong><?php echo profileDisplayValue($areaAssigned, 'N/A'); ?></strong>
                  </div>
                  <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">LGU office type</span>
                    <strong><?php echo profileDisplayValue($lguInfo['office_type'] ?? '', 'N/A'); ?></strong>
                  </div>
                </div>
                <?php endif; ?>
                <div class="mb-3 d-flex justify-content-between">
                  <span class="text-muted">Role</span>
                  <strong><?php echo profileDisplayValue($lguInfo['position_role'] ?? '', strtoupper($profileRole)); ?></strong>
                </div>
                <?php if (!empty($lguInfo) && is_array($lguInfo)): ?>
                <div class="mb-3">
                  <button type="button" class="btn btn-sm btn-outline-primary" id="toggleLguDetailsBtn">Show LGU details</button>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h6 class="mb-4 text-uppercase text-muted">Security</h6>
                <div class="mb-3 d-flex justify-content-between">
                  <span class="text-muted">User ID</span>
                  <strong><?php echo profileDisplayValue($profileUserId, 'N/A'); ?></strong>
                </div>
                <div class="mb-3 d-flex justify-content-between align-items-start">
                  <span class="text-muted">Password</span>
                  <div style="width: 56%; text-align: right;">
                    <div id="passwordView" class="d-inline-flex align-items-center">
                      <strong id="maskedPassword">••••••••</strong>
                    </div>
                  </div>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                  <span class="text-muted">Last login</span>
                  <?php
                    $lastLoginRaw = $_SESSION['last_login'] ?? $profileInfo['last_login'] ?? null;
                    $lastLoginValue = '';
                    if ($lastLoginRaw) {
                        try {
                            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $lastLoginRaw, new DateTimeZone('UTC'));
                            if ($dt !== false) {
                                $lastLoginValue = $dt->format(DATE_ATOM);
                            }
                        } catch (Exception $e) {
                            $lastLoginValue = '';
                        }
                    }
                  ?>
                  <strong id="lastLoginValue" data-last-login="<?php echo htmlspecialchars($lastLoginValue, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo $lastLoginValue ? 'Loading...' : 'N/A'; ?>
                  </strong>
                </div>
                <!-- Role change notice removed per user request -->
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <div class="d-flex justify-content-between align-items-center w-100">
          <button type="button" class="btn btn-outline-secondary" id="editProfileBtn">Edit</button>
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Popup -->
<div class="modal fade" id="profileEditModal" tabindex="-1" role="dialog" aria-labelledby="profileEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content bg-white border-0 rounded-4 shadow-lg">
      <div class="modal-body p-3">
        <div class="text-center" style="padding: 0.25rem 0; margin-bottom: 0.5rem;">
          <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" style="width:35px; height:35px;">
            <i class="fa fa-user-edit text-white" style="font-size:0.8rem;"></i>
          </div>
          <h6 class="mt-0 mb-0 text-dark" style="font-size:0.95rem;">Edit Profile</h6>
        </div>
        <form id="profileEditForm" method="post" action="">
          <input type="hidden" name="profileEditSubmit" value="1">
          <?php if (!empty($profileEditMessage)): ?>
            <div class="alert alert-<?php echo strpos($profileEditMessage, 'successfully') !== false ? 'success' : 'danger'; ?> py-2">
              <?php echo htmlspecialchars($profileEditMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
          <div class="row" style="margin-bottom:0.4rem;">
            <div class="col-6" style="margin-bottom:0.35rem;">
              <label class="form-label text-dark" for="firstName" style="font-size:0.8rem; margin-bottom:0.15rem;">First name</label>
              <input type="text" id="firstName" name="firstName" class="form-control rounded-pill border bg-light text-dark" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="First name" value="<?php echo htmlspecialchars($lguInfo['first_name'] ?? $publicInfo['first_name'] ?? ($_SESSION['firstname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-6" style="margin-bottom:0.35rem;">
              <label class="form-label text-dark" for="lastName" style="font-size:0.8rem; margin-bottom:0.15rem;">Last name</label>
              <input type="text" id="lastName" name="lastName" class="form-control rounded-pill border bg-light text-dark" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Last name" value="<?php echo htmlspecialchars($lguInfo['last_name'] ?? $publicInfo['last_name'] ?? ($_SESSION['lastname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>
          <div style="margin-bottom:0.35rem;">
            <label class="form-label text-dark" for="email" style="font-size:0.8rem; margin-bottom:0.15rem;">Account email</label>
            <input type="email" id="email" name="email" class="form-control rounded-pill border bg-light text-dark" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Account email" value="<?php echo htmlspecialchars($profileEmail, ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>
          <div style="margin-bottom:0.35rem;">
            <label class="form-label text-dark" for="contact" style="font-size:0.8rem; margin-bottom:0.15rem;"><?php echo (!empty($lguInfo) && is_array($lguInfo)) ? 'Public contact #' : 'Contact #'; ?></label>
            <input type="text" id="contact" name="contact" class="form-control rounded-pill border bg-light text-dark" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Contact number" value="<?php echo htmlspecialchars($lguInfo['contact_number'] ?? $publicInfo['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <?php if (!empty($lguInfo) && is_array($lguInfo)): ?>
          <div style="margin-bottom:0.35rem;">
            <label class="form-label text-dark" for="officeEmail" style="font-size:0.8rem; margin-bottom:0.15rem;">Office email</label>
            <input type="email" id="officeEmail" name="officeEmail" class="form-control rounded-pill border bg-light text-dark" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Office email" value="<?php echo htmlspecialchars($lguInfo['office_email_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <small class="form-text text-muted" style="font-size:0.65rem; display:block; margin-top:0.1rem;">Must differ from account email</small>
          </div>
          <div style="margin-bottom:0.35rem;">
            <label class="form-label text-dark" for="officeNumber" style="font-size:0.8rem; margin-bottom:0.15rem;">Office #</label>
            <input type="text" id="officeNumber" name="officeNumber" class="form-control rounded-pill border bg-light text-dark" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Office number" value="<?php echo htmlspecialchars($lguInfo['office_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <?php endif; ?>
          <hr style="margin: 0.3rem 0;">
          <div style="margin-bottom:0.35rem; position:relative;">
            <label class="form-label text-dark" for="newPassword" style="font-size:0.8rem; margin-bottom:0.15rem;">New password</label>
            <input type="password" id="newPassword" name="newPassword" class="form-control rounded-pill border bg-light text-dark pe-5" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Leave blank to keep current">
            <span class="position-absolute" style="right: 10px; top: 38%; transform: translateY(-50%); cursor: pointer;">
              <i class="fa fa-eye text-secondary" id="toggleNewPwd" style="font-size:0.8rem;"></i>
            </span>
          </div>
          <div style="margin-bottom:0.2rem; position:relative;">
            <label class="form-label text-dark" for="confirmPassword" style="font-size:0.8rem; margin-bottom:0.15rem;">Confirm password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" class="form-control rounded-pill border bg-light text-dark pe-5" style="font-size:0.85rem; padding:0.35rem 0.75rem;" placeholder="Re-enter password">
            <span class="position-absolute" style="right: 10px; top: 38%; transform: translateY(-50%); cursor: pointer;">
              <i class="fa fa-eye text-secondary" id="toggleConfirmPwd" style="font-size:0.8rem;"></i>
            </span>
          </div>
        </form>
      </div>
      <div class="modal-footer border-0" style="justify-content:center; padding:10px 15px; gap: 8px;">
        <button type="button" id="profileEditSaveBtn" class="btn btn-primary" style="min-width:140px; padding: 0.4rem 1rem; font-size: 0.95rem;">Save changes</button>
        <button type="button" class="btn btn-link text-primary" id="backToProfileBtn" style="padding: 0.4rem 0.75rem; font-size: 0.95rem;">&larr; Back to profile</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // If page somehow left body overflow hidden but no modal is open, restore scrolling
  try {
    if (document.body && document.body.style && document.body.style.overflow === 'hidden') {
      if (typeof jQuery !== 'undefined') {
        if (jQuery('.modal.show').length === 0) {
          document.body.style.overflow = '';
        }
      } else {
        // no jQuery: check for any element with class 'modal show'
        if (document.querySelectorAll('.modal.show').length === 0) {
          document.body.style.overflow = '';
        }
      }
    }
  } catch (e) {}
  var el = document.getElementById('lastLoginValue');
  if (el) {
    var raw = el.getAttribute('data-last-login');
    if (!raw) {
      el.textContent = 'N/A';
    } else {
      var date = new Date(raw);
      if (!isNaN(date.getTime())) {
        el.textContent = date.toLocaleString(undefined, {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: 'numeric',
          minute: '2-digit'
        });
      } else {
        el.textContent = 'N/A';
      }
    }
  }

  var resetButton = document.getElementById('editProfileBtn');
  if (resetButton) {
    resetButton.addEventListener('click', function () {
      $('#profileModal').modal('hide');
      $('#profileEditModal').modal('show');
    });
  }

  function togglePasswordField(toggleId, fieldId) {
    var toggle = document.getElementById(toggleId);
    var field = document.getElementById(fieldId);
    if (!toggle || !field) return;
    toggle.addEventListener('click', function () {
      if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
      }
    });
  }

  togglePasswordField('toggleNewPwd', 'newPassword');
  togglePasswordField('toggleConfirmPwd', 'confirmPassword');

  var resetForm = document.getElementById('profileEditForm');
  var saveBtn = document.getElementById('profileEditSaveBtn');
  if (saveBtn && resetForm) {
    saveBtn.addEventListener('click', function () {
      if (typeof resetForm.requestSubmit === 'function') {
        resetForm.requestSubmit();
      } else {
        resetForm.submit();
      }
    });
  }
  if (resetForm) {
    resetForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var form = event.currentTarget;
      // prepare message container
      var msgDiv = form.querySelector('.alert');
      if (!msgDiv) {
        msgDiv = document.createElement('div');
        msgDiv.className = 'alert py-2';
        form.insertBefore(msgDiv, form.firstChild);
      }
      msgDiv.classList.remove('alert-success', 'alert-danger');
      msgDiv.textContent = '';

      var submitBtn = form.querySelector('button[type="submit"]');
      var passwordChanged = form.querySelector('#newPassword') && form.querySelector('#newPassword').value.trim() !== '';
      if (submitBtn) {
        submitBtn.disabled = true;
        var origText = submitBtn.innerHTML;
        submitBtn.innerHTML = 'Saving...';
      }

      var data = new FormData(form);

      fetch('ajax/profile_edit.ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: data
      }).then(function (res) { return res.json(); }).then(function (json) {
        if (json && json.success) {
          msgDiv.classList.add('alert-success');
          msgDiv.textContent = (json.message || 'Saved') + (passwordChanged ? ' Your entered password was saved securely.' : '');
          // Auto-hide success message after 2.5 seconds
          setTimeout(function () {
            msgDiv.classList.remove('alert-success', 'alert-danger');
            msgDiv.textContent = '';
          }, 2500);
          // update displayed profile values
          if (json.data) {
            if (json.data.name) {
              var nameEl = document.getElementById('profileDisplayName');
              if (nameEl) nameEl.textContent = json.data.name;
              // also update header name if present
              var headerNameEls = document.querySelectorAll('.header .brand-logo + div h5, .nav-header .brand-logo');
              // best-effort: update username displays that exist
            }
            if (json.data.email) {
              var emailEl = document.getElementById('profileDisplayEmail');
              if (emailEl) emailEl.textContent = json.data.email;
            }
            if (json.data.officeEmail) {
              var officeEmailEl = document.getElementById('profileDisplayOfficeEmail');
              if (officeEmailEl) officeEmailEl.textContent = json.data.officeEmail;
            }
            if (json.data.officeNumber) {
              var officeNumberEl = document.getElementById('profileDisplayLguOfficeNumber');
              if (officeNumberEl) officeNumberEl.textContent = json.data.officeNumber || 'N/A';
            }
            if (json.data.contact) {
              var contactEl = document.getElementById('profileDisplayContact');
              if (contactEl) contactEl.textContent = json.data.contact || 'N/A';
            }
          }
          // clear password fields
          var np = document.getElementById('newPassword'); if (np) np.value = '';
          var cp = document.getElementById('confirmPassword'); if (cp) cp.value = '';
          setTimeout(function () {
            $('#profileEditModal').modal('hide');
            // show profile modal to reflect changes
            $('#profileModal').modal('show');
          }, 700);
        } else {
          msgDiv.classList.add('alert-danger');
          msgDiv.textContent = json && json.message ? json.message : 'Error saving profile';
        }
      }).catch(function (err) {
        msgDiv.classList.add('alert-danger');
        msgDiv.textContent = 'Server error';
      }).finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = origText || 'Save changes';
        }
      });
    });
  }

  var backToProfileBtn = document.getElementById('backToProfileBtn');
  if (backToProfileBtn) {
    backToProfileBtn.addEventListener('click', function () {
      $('#profileEditModal').modal('hide');
      $('#profileModal').modal('show');
    });
  }

  var lguToggleBtn = document.getElementById('toggleLguDetailsBtn');
  var lguDetails = document.getElementById('lguDetailsSection');
  if (lguToggleBtn && lguDetails) {
    lguToggleBtn.addEventListener('click', function () {
      if (lguDetails.classList.contains('d-none')) {
        lguDetails.classList.remove('d-none');
        lguToggleBtn.textContent = 'Hide LGU details';
      } else {
        lguDetails.classList.add('d-none');
        lguToggleBtn.textContent = 'Show LGU details';
      }
    });
  }

  var darkModeToggle = document.getElementById('darkModeToggle');
  function applyTheme(theme) {
    if (theme === 'dark') {
      document.body.classList.add('theme-dark');
    } else {
      document.body.classList.remove('theme-dark');
    }
  }

  var savedTheme = localStorage.getItem('evacfinderTheme') || 'light';
  applyTheme(savedTheme);

  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', function () {
      var nextTheme = document.body.classList.contains('theme-dark') ? 'light' : 'dark';
      localStorage.setItem('evacfinderTheme', nextTheme);
      applyTheme(nextTheme);
    });
  }

  // Inline password edit handlers
  var editBtn = document.getElementById('editPasswordInlineBtn');
  var passwordView = document.getElementById('passwordView');
  var passwordEditForm = document.getElementById('passwordEditForm');
  var cancelBtn = document.getElementById('cancelInlinePassword');
  var saveBtn = document.getElementById('saveInlinePassword');
  var inlineMsg = document.getElementById('inlinePasswordMessage');

  if (editBtn) {
    editBtn.addEventListener('click', function () {
      passwordView.style.display = 'none';
      passwordEditForm.style.display = 'block';
      inlineMsg.innerText = '';
      document.getElementById('inlineNewPassword').focus();
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', function (e) {
      e.preventDefault();
      passwordEditForm.style.display = 'none';
      passwordView.style.display = 'inline-flex';
      inlineMsg.innerText = '';
      document.getElementById('inlineNewPassword').value = '';
      document.getElementById('inlineConfirmPassword').value = '';
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', function (e) {
      e.preventDefault();
      inlineMsg.innerText = '';
      var newPw = document.getElementById('inlineNewPassword').value.trim();
      var confPw = document.getElementById('inlineConfirmPassword').value.trim();
      if (!newPw || !confPw) {
        inlineMsg.innerText = 'Please enter both password fields.';
        return;
      }

      var formData = new FormData();
      formData.append('newPassword', newPw);
      formData.append('confirmPassword', confPw);

      fetch('ajax/password_reset.ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (res) { return res.json(); }).then(function (json) {
        if (json.success) {
          inlineMsg.style.color = '#198754';
          inlineMsg.innerText = json.message;
          setTimeout(function () {
            passwordEditForm.style.display = 'none';
            passwordView.style.display = 'inline-flex';
            document.getElementById('inlineNewPassword').value = '';
            document.getElementById('inlineConfirmPassword').value = '';
            inlineMsg.innerText = '';
          }, 1200);
        } else {
          inlineMsg.style.color = '#dc3545';
          inlineMsg.innerText = json.message || 'Error updating password';
        }
      }).catch(function (err) {
        inlineMsg.style.color = '#dc3545';
        inlineMsg.innerText = 'Server error';
      });
    });
  }

  <?php if (!empty($profileEditMessage)): ?>
    $('#profileEditModal').modal('show');
  <?php endif; ?>
});
</script>
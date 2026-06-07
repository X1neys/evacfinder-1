<?php
$base_url = '/evacfinder/';
?>

<!-- Sidebar -->
<div class="deznav">
  <div class="deznav-scroll">
    <ul class="metismenu" id="menu">

      <li class="nav-label first">Main Menu</li>

      <?php
        $currentUserPermissions = [];
        if (isset($_SESSION['userid']) && isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === 'ok') {
          $currentUserPermissions = ModelUserRights::mdlGetPermissions($_SESSION['userid']);
        }

        function canAccessRoute($route, $permissions) {
          return !isset($permissions[$route]) || strtolower($permissions[$route]) !== 'restricted';
        }
      ?>

      <!-- Map - Always visible -->
      <?php if (canAccessRoute('map', $currentUserPermissions)): ?>
      <li>
        <a class="ai-icon" href="<?php echo $base_url; ?>?route=map" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
              viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" class="feather feather-map">
            <polygon points="1 6 1 22 8 18 15 22 23 18 23 2 15 6 8 2 1 6"></polygon>
            <line x1="8" y1="2" x2="8" y2="18"></line>
            <line x1="15" y1="6" x2="15" y2="22"></line>
          </svg>
          <span class="nav-text">Map</span>
        </a>
      </li>

      <?php if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok"): ?>
        <!-- Protected menu items - Only for logged in users -->
        
        <!-- Dashboard (Home) -->
        <?php if (canAccessRoute('home', $currentUserPermissions)): ?>
        <li>
          <a class="ai-icon" href="<?php echo $base_url; ?>?route=home" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="feather feather-home">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="nav-text">Dashboard</span>
          </a>
        </li>
        <?php endif; ?>

        <!-- Active Centers (This shows the list of centers with Add buttons) -->
        <li>
          <a class="ai-icon" href="<?php echo $base_url; ?>?route=active" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="feather feather-activity">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            <span class="nav-text">Active Centers</span>
          </a>
        </li>
        <?php endif; ?>

        <!-- Announcement -->
        <?php if (canAccessRoute('announcement', $currentUserPermissions)): ?>
        <li>
          <a class="ai-icon" href="<?php echo $base_url; ?>?route=announcement" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="feather feather-bell">
              <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
              <line x1="9" y1="6" x2="15" y2="6"></line>
              <line x1="9" y1="10" x2="15" y2="10"></line>
              <line x1="9" y1="14" x2="15" y2="14"></line>
              <line x1="9" y1="18" x2="11" y2="18"></line>
            </svg>
            <span class="nav-text">Announcement</span>
          </a>
        </li>

        <!-- User Access (hidden if restricted) -->
        <?php
          $showUserAccess = true;
          if (isset($_SESSION['userid'])) {
            $perms = ModelUserRights::mdlGetPermissions($_SESSION['userid']);
            if (isset($perms['useraccess']) && $perms['useraccess'] === 'restricted') {
              $showUserAccess = false;
            }
          }
        ?>
        <?php if ($showUserAccess): ?>
        <li>
          <a class="ai-icon" href="<?php echo $base_url; ?>?route=useraccess" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="feather feather-lock">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span class="nav-text">User Access</span>
          </a>
        </li>
        <?php endif; ?>
      <?php endif; ?>

    </ul>
  </div>
</div>
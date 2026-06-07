<?php
  session_start();
  ob_start();
  $isLoggedIn = isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok";
  $route = isset($_GET["route"]) ? basename($_GET["route"]) : "map";
  $pageRoute = $route;
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Evac</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="views/assets/images/evaclogo.png">
  <link rel="icon" type="image/png" sizes="16x16" href="views/assets/images/evaclogo.png">
  <link rel="shortcut icon" href="views/assets/images/evaclogo.png">

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <!-- CSS -->
  <link rel="stylesheet" href="views/assets/vendor/jqvmap/css/jqvmap.min.css">
  <link rel="stylesheet" href="views/assets/vendor/chartist/css/chartist.min.css">
  <link rel="stylesheet" href="views/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css">
  <link rel="stylesheet" href="views/assets/css/perfect-scrollbar.css">
  <link rel="stylesheet" href="views/assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  
  <style>
    /* Ensure map displays properly */
    #homeMap, #centerMap {
      width: 100%;
      z-index: 1;
    }
    .map-loading {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 10;
      background: rgba(0,0,0,0.7);
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
    }
    .nav-header .brand-logo {
      justify-content: center !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    .nav-header .logo-centered {
      max-width: 180px;
      width: auto;
      height: auto;
      display: block;
      margin: 0 auto;
    }
  </style>
</head>
<body>

  <!-- Preloader -->
  <div id="preloader">
    <div class="sk-three-bounce">
      <div class="sk-child sk-bounce1"></div>
      <div class="sk-child sk-bounce2"></div>
      <div class="sk-child sk-bounce3"></div>
    </div>
  </div>

  <?php if($isLoggedIn): ?>
    <!-- LOGGED IN: Show full app with navbar and sidebar -->
    <div id="main-wrapper">
      <?php include "modules/navbar.php"; ?>
      <?php include "modules/sidebar.php"; ?>
      
      <div class="content-body">
        <div class="container-fluid">
          <?php
            $allowedRoutes = ['home', 'map', 'logout', 'centers', 'evacuees', 'active', 'announcement', 'useraccess', 'assigned', 'forgot-password'];
            if(in_array($route, $allowedRoutes)){
              include "modules/" . $route . ".php";
            } else {
              include "modules/map.php";
            }
          ?>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- NOT LOGGED IN -->
    <?php if($route == "login"): ?>
      <!-- Login page - NO navbar, just the form -->
      <div class="content-body" style="margin-left: 0; padding: 0;">
        <div class="container-fluid" style="padding: 0;">
          <?php include "modules/login.php"; ?>
        </div>
      </div>
    <?php elseif($route == "registration_lgu"): ?>
        <!-- LGU Registration Page -->
        <div class="content-body" style="margin-left: 0; padding: 0;">
            <div class="container-fluid" style="padding: 0;">
                <?php include "modules/registration_lgu.php"; ?>
            </div>
        </div>
    <?php elseif($route == "registration"): ?>
        <!-- Regular Registration Page -->
        <div class="content-body" style="margin-left: 0; padding: 0;">
            <div class="container-fluid" style="padding: 0;">
                <?php include "modules/registration.php"; ?>
            </div>
        </div>
    <?php elseif($route == "forgot-password"): ?>
        <!-- Forgot Password Page -->
        <div class="content-body" style="margin-left: 0; padding: 0;">
            <div class="container-fluid" style="padding: 0;">
                <?php include "modules/forgot-password.php"; ?>
            </div>
        </div>
    <?php else: ?>
      <!-- Map page - Show navbar (with login button) but NO sidebar -->
      <div id="main-wrapper" class="no-sidebar">
        <?php include "modules/navbar.php"; ?>
        <!-- Sidebar is NOT included -->
        
        <div class="content-body">
          <div class="container-fluid" style="padding: 0;">
            <?php $pageRoute = 'map'; include "modules/map.php"; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Scripts -->
  <script src="views/assets/vendor/global/global.min.js"></script>
  <script src="views/assets/js/deznav-init.js"></script>
  <script src="views/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
  <script src="views/assets/js/custom.min.js"></script>

  <!-- Charts -->
  <script src="views/assets/vendor/chart.js/Chart.bundle.min.js"></script>
  <script src="views/assets/vendor/gaugeJS/dist/gauge.min.js"></script>

  <!-- Counter Up -->
  <script src="views/assets/vendor/waypoints/jquery.waypoints.min.js"></script>
  <script src="views/assets/vendor/jquery.counterup/jquery.counterup.min.js"></script>

  <!-- SVG Animation -->
  <script src="views/assets/vendor/svganimation/vivus.min.js"></script>
  <script src="views/assets/vendor/svganimation/svg.animation.js"></script>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>
  <!-- Leaflet Routing Machine — after Leaflet & MarkerCluster -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet-routing-machine/3.2.12/leaflet-routing-machine.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-routing-machine/3.2.12/leaflet-routing-machine.min.js"></script>


  <!-- Page-specific JS -->
  <?php if(isset($pageRoute)): ?>
    <?php
      $routeScripts = [
        'registration' => ['registration.js'],
            'registration_lgu' => ['registration.js'],
            'map'     => ['map.js'],
        'home'    => ['home.js'],
        'login'   => ['login.js'],
        'centers' => ['centers.js'],
        'evacuees' => ['evacuees.js'],
        'announcement' => ['announcement.js'],
        'active' => ['active.js']
        
        
      ];
      if(array_key_exists($pageRoute, $routeScripts)){
        foreach($routeScripts[$pageRoute] as $script){
          $scriptPath = "views/js/" . $script;
          if(file_exists($scriptPath)){
            echo '<script src="' . $scriptPath . '"></script>';
          } else {
            echo '<script src="/views/js/' . $script . '"></script>';
          }
        }
      }
    ?>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



  
</body>
</html>
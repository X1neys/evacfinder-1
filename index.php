<?php
// Hide PHP errors from public output; log them instead in production
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_STRICT);

require_once "controllers/template.controller.php";

require_once "controllers/userrights.controller.php";
require_once "models/userrights.model.php";

require_once "controllers/centers.controller.php";
require_once "models/centers.model.php";

require_once "controllers/evacuees.controller.php";
require_once "models/evacuees.model.php";

require_once "controllers/announcement.controller.php";
require_once "models/announcement.model.php";



$template = new ControllerTemplate();
$template->ctrTemplate();


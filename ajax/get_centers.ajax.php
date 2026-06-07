<?php
require_once "../controllers/centers.controller.php";
require_once "../models/centers.model.php";

if(isset($_POST["action"]) && $_POST["action"] == "get_centers") {
    $centers = ModelCenters::mdlGetActiveCenters();
    header('Content-Type: application/json');
    echo json_encode($centers);
}
?>
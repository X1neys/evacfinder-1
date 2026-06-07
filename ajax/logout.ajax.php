<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Return success response
echo json_encode(['success' => true]);

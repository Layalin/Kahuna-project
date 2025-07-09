<?php
// First, run the standard authentication middleware to check for a valid token
require_once 'auth-middleware.php';

// Next, check if the authenticated user is an admin
if ($GLOBALS['current_user']['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Access denied. Administrator privileges required.']);
    exit();
}
?>
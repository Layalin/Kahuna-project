<?php
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    http_response_code(401); echo json_encode(['error' => 'Authorization token not found.']); exit();
}

$token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
require_once 'database.php';
$stmt = $pdo->prepare("SELECT id, username, role FROM USERS WHERE session_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(403); echo json_encode(['error' => 'Invalid authentication token.']); exit();
}

// Make user info available to the script that included this file
$GLOBALS['current_user'] = $user;
?>
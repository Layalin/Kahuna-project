<?php
header('Content-Type: application/json');
require_once 'database.php';
require_once 'models/User.php'; // Include our User model

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400); 
    echo json_encode(['error' => 'Username and password are required.']); 
    exit();
}

// Find the user by their username using our User model
$user = User::findByUsername($pdo, $data['username']);

// --- SECURITY RESTORED ---
// Check that the user exists AND that the provided password matches the stored hash.
if ($user && password_verify($data['password'], $user['password_hash'])) {
    
    // If successful, generate and save a new token
    $token = bin2hex(random_bytes(32));
    User::saveToken($pdo, $user['id'], $token);
    
    // Return the token and user info
    http_response_code(200);
    echo json_encode([
        'token' => $token,
        'user' => [
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);
} else {
    // If the user doesn't exist or the password is wrong, deny access.
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password.']);
}
?>
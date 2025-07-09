<?php
header('Content-Type: application/json');
require_once 'database.php';
require_once 'models/User.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400); 
    echo json_encode(['error' => 'Username and password are required.']); 
    exit();
}

$user = User::findByUsername($pdo, $data['username']);

// Securely check if the user exists AND the password is correct
if ($user && password_verify($data['password'], $user['password_hash'])) {
    
    $token = bin2hex(random_bytes(32));
    User::saveToken($pdo, $user['id'], $token);
    
    http_response_code(200);
    echo json_encode([
        'token' => $token,
        'user' => [
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password.']);
}
?>
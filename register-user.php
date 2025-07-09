<?php
header('Content-Type: application/json');
require_once 'database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password']) || !isset($data['role'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Username, password, and role are required.']);
    exit();
}

if ($data['role'] !== 'client' && $data['role'] !== 'admin') {
    http_response_code(400);
    echo json_encode(['error' => 'Role must be either "client" or "admin".']);
    exit();
}

$password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO USERS (username, password_hash, role) VALUES (?, ?, ?)");

try {
    $stmt->execute([$data['username'], $password_hash, $data['role']]);
    http_response_code(201);
    echo json_encode(['success' => 'User created successfully.']);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode(['error' => 'This username is already taken.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
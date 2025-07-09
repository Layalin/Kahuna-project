<?php
header('Content-Type: application/json');
require_once 'admin-auth-middleware.php'; // Use the admin-only middleware

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['serial_number']) || !isset($data['product_name']) || !isset($data['warranty_years'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Serial number, product name, and warranty years are required.']);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO PRODUCTS (serial_number, product_name, warranty_years) VALUES (?, ?, ?)");

try {
    $stmt->execute([$data['serial_number'], $data['product_name'], $data['warranty_years']]);
    http_response_code(201);
    echo json_encode(['success' => 'Product added successfully.']);
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode(['error' => 'A product with this serial number already exists.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
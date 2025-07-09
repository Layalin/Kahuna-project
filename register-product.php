<?php
header('Content-Type: application/json');
require_once 'auth-middleware.php';

$data = json_decode(file_get_contents('php://input'), true);

// NEW: Check for purchase_date
if (!isset($data['serial_number']) || !isset($data['purchase_date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Serial number and purchase date are required.']);
    exit();
}

$user_id = $GLOBALS['current_user']['id'];
$serial_number = $data['serial_number'];
$purchase_date = $data['purchase_date']; // Get the date from the request

// Check if the product serial number exists
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM PRODUCTS WHERE serial_number = ?");
$stmtCheck->execute([$serial_number]);
if ($stmtCheck->fetchColumn() == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Product with this serial number does not exist.']);
    exit();
}

// Insert into registered products with the purchase date
$stmt = $pdo->prepare(
    "INSERT INTO REGISTERED_PRODUCTS (user_id, product_serial_number, purchase_date) VALUES (?, ?, ?)"
);
try {
    $stmt->execute([$user_id, $serial_number, $purchase_date]);
    http_response_code(201);
    echo json_encode(['success' => 'Product registered successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
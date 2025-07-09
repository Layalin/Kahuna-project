<?php
header('Content-Type: application/json');
require_once 'auth-middleware.php';

if (!isset($_GET['serial_number'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Serial number is required.']);
    exit();
}

$serial_number = $_GET['serial_number'];
$user_id = $GLOBALS['current_user']['id'];

$stmt = $pdo->prepare(
    "SELECT p.product_name, p.serial_number, p.warranty_years, rp.purchase_date,
            DATE_ADD(rp.purchase_date, INTERVAL p.warranty_years YEAR) AS warranty_expiration_date,
            DATEDIFF(DATE_ADD(rp.purchase_date, INTERVAL p.warranty_years YEAR), CURDATE()) AS warranty_days_left
    FROM REGISTERED_PRODUCTS rp
    JOIN PRODUCTS p ON rp.product_serial_number = p.serial_number
    WHERE rp.user_id = ? AND rp.product_serial_number = ?"
);

$stmt->execute([$user_id, $serial_number]);
$product = $stmt->fetch();

if ($product) {
    $product['warranty_days_left'] = max(0, (int)$product['warranty_days_left']);
    echo json_encode($product);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found or not registered to this user.']);
}
?>
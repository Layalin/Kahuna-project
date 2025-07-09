<?php
header('Content-Type: application/json');
require_once 'auth-middleware.php';

$user_role = $GLOBALS['current_user']['role'];
$user_id = $GLOBALS['current_user']['id'];

$sql = "";
$params = [];

if ($user_role === 'admin') {
    // NEW: Added warranty calculation for the admin view
    $sql = "SELECT 
                p.product_name, 
                p.serial_number, 
                rp.purchase_date, 
                u.username AS registered_by_user,
                DATEDIFF(DATE_ADD(rp.purchase_date, INTERVAL p.warranty_years YEAR), CURDATE()) AS warranty_days_left
            FROM REGISTERED_PRODUCTS rp
            JOIN PRODUCTS p ON rp.product_serial_number = p.serial_number
            JOIN USERS u ON rp.user_id = u.id";

    if (!empty($_GET['username'])) {
        $sql .= " WHERE u.username LIKE ?";
        $params[] = '%' . $_GET['username'] . '%';
    }
    $sql .= " ORDER BY rp.purchase_date DESC";

} else { // Client query
    $sql = "SELECT 
                p.product_name, 
                p.serial_number, 
                rp.purchase_date,
                DATEDIFF(DATE_ADD(rp.purchase_date, INTERVAL p.warranty_years YEAR), CURDATE()) AS warranty_days_left
            FROM REGISTERED_PRODUCTS rp
            JOIN PRODUCTS p ON rp.product_serial_number = p.serial_number
            WHERE rp.user_id = ?";
    $params[] = $user_id;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Ensure warranty days left is not negative
    foreach ($products as &$product) {
        if (isset($product['warranty_days_left'])) {
            $product['warranty_days_left'] = max(0, (int)$product['warranty_days_left']);
        }
    }

    http_response_code(200);
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
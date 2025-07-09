<?php
header('Content-Type: application/json');
require_once 'database.php';

// This is a public endpoint, it does not require a login.
// It fetches all products from the master list.
$stmt = $pdo->prepare("SELECT serial_number, product_name FROM PRODUCTS");
$stmt->execute();
$products = $stmt->fetchAll();

echo json_encode($products);
?>
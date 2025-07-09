<?php
// Create a new database named kahuna_db first
$host = 'localhost';
$username = 'root';
$password = '';
try {
    $pdo_setup = new PDO("mysql:host=$host", $username, $password);
    $pdo_setup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_setup->exec("CREATE DATABASE IF NOT EXISTS kahuna_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo_setup->exec("USE kahuna_db;");
    echo "Database 'kahuna_db' created or already exists.<br>";
} catch(PDOException $e) {
    die("DB ERROR: ". $e->getMessage());
}

require_once 'database.php';

try {
    $sql_users = "CREATE TABLE USERS (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('client', 'admin') NOT NULL,
        session_token VARCHAR(255) NULL DEFAULT NULL
    );";
    $pdo->exec($sql_users);
    echo "Table 'USERS' created.<br>";

    $sql_products = "CREATE TABLE PRODUCTS (
        serial_number VARCHAR(255) PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL,
        warranty_years INT NOT NULL
    );";
    $pdo->exec($sql_products);
    echo "Table 'PRODUCTS' created.<br>";

    $sql_registered = "CREATE TABLE REGISTERED_PRODUCTS (
        registration_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_serial_number VARCHAR(255) NOT NULL,
        purchase_date DATE NOT NULL,
        FOREIGN KEY (user_id) REFERENCES USERS(id),
        FOREIGN KEY (product_serial_number) REFERENCES PRODUCTS(serial_number)
    );";
    $pdo->exec($sql_registered);
    echo "Table 'REGISTERED_PRODUCTS' created.<br>";

    $products_to_add = [
        ['KHWM8199911', 'CombiSpin Washing Machine', 2], ['KHWM8199912', 'CombiSpin + Dry Washing Machine', 2],
        ['KHMW789991', 'CombiGrill Microwave', 1], ['KHWP890001', 'K5 Water Pump', 5],
        ['KHWP890002', 'K5 Heated Water Pump', 5], ['KHSS988881', 'Smart Switch Lite', 2],
        ['KHSS988882', 'Smart Switch Pro', 2], ['KHSS988883', 'Smart Switch Pro V2', 2],
        ['KHHM89762', 'Smart Heated Mug', 1], ['KHSB0001', 'Smart Bulb 001', 1]
    ];

    $stmt = $pdo->prepare("INSERT INTO PRODUCTS (serial_number, product_name, warranty_years) VALUES (?, ?, ?)");
    foreach ($products_to_add as $product) {
        $stmt->execute($product);
    }
    echo "Table 'PRODUCTS' populated.<br>";
    echo "<hr><strong>Setup complete!</strong>";

} catch (PDOException $e) {
    die("TABLE ERROR: " . $e->getMessage());
}
?>
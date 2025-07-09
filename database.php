<?php
// --- FINAL CORS & PREFLIGHT HANDLING ---
// This header allows your front-end (even if on a different domain) to access the API.
header("Access-Control-Allow-Origin: *");
// This tells the browser which headers are allowed in the actual request.
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// This tells the browser which methods are allowed.
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// This block specifically handles the preflight 'OPTIONS' request.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // A preflight request just needs a 200 OK response to succeed.
    http_response_code(200);
    exit();
}
// --- END ---


// --- Database Connection Code ---
$host = 'localhost';
$dbname = 'kahuna_db';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
?>
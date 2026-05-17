<?php
// Database connection setup (Dito kumokonekta sa MySQL gamit ang custom port 3307)
$conn = new mysqli("127.0.0.1", "root", "", "syncdesk", 3307);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");

// Helper: safe output
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper: log action
function logAction($conn, $product_id, $action, $details = '') {
    $stmt = $conn->prepare("INSERT INTO logs (product_id, action, details, user) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("iss", $product_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}
?>

<?php include 'config.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header("Location: inventory.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: inventory.php");
    exit;
}

// Log before deleting so product_id reference still makes sense
logAction($conn, $id, 'Deleted product', "SKU: {$product['sku']}, Name: {$product['product_name']}, Stock: {$product['stock']}");

$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: inventory.php");
exit;

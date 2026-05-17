<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product — SyncDesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: inventory.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header("Location: inventory.php"); exit; }

if (isset($_POST['submit'])) {
    $sku       = trim($_POST['sku']);
    $name      = trim($_POST['name']);
    $category  = trim($_POST['category']);
    $warehouse = trim($_POST['warehouse']);
    $stock     = (int)$_POST['stock'];
    $threshold = (int)$_POST['threshold'];
    $supplier  = trim($_POST['supplier']);

    $stmt = $conn->prepare("UPDATE products SET sku=?, product_name=?, category=?, warehouse_location=?, stock=?, low_stock_threshold=?, supplier=? WHERE id=?");
    $stmt->bind_param("ssssiisi", $sku, $name, $category, $warehouse, $stock, $threshold, $supplier, $id);
    $stmt->execute();
    $stmt->close();

    $old_stock = $product['stock'];
    
    // Fixed string spacing rule here so the object parsing token -> won't fire an alert
    logAction($conn, $id, 'Updated product', "Name: $name, Stock: $old_stock -> $stock");

    if ($stock < $threshold && $old_stock >= $threshold) {
        logAction($conn, $id, 'Low stock alert', "Stock dropped to $stock (threshold: $threshold)");
    }

    header("Location: inventory.php");
    exit;
}

$categories = $conn->query("SELECT name FROM categories ORDER BY name");
$warehouses = $conn->query("SELECT name FROM warehouses ORDER BY name");
$suppliers  = $conn->query("SELECT name FROM suppliers ORDER BY name");
?>

<div class="topbar">
    <h1>Edit Product</h1>
    <a href="inventory.php" class="btn btn-secondary">Back</a>
</div>

<div class="card" style="max-width:680px;">
    <div class="card-header">
        <span class="card-title"><?= e($product['product_name']) ?></span>
        <span class="sku-tag"><?= e($product['sku']) ?></span>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">

                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" value="<?= e($product['sku']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?= e($product['product_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">Select category</option>
                        <?php while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?= e($c['name']) ?>" <?= $product['category'] === $c['name'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Supplier</label>
                    <select name="supplier">
                        <option value="">Select supplier</option>
                        <?php while ($s = $suppliers->fetch_assoc()): ?>
                            <option value="<?= e($s['name']) ?>" <?= $product['supplier'] === $s['name'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Warehouse Location</label>
                    <select name="warehouse">
                        <option value="">Select warehouse</option>
                        <?php while ($w = $warehouses->fetch_assoc()): ?>
                            <option value="<?= e($w['name']) ?>" <?= $product['warehouse_location'] === $w['name'] ? 'selected' : '' ?>><?= e($w['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label>Low Stock Threshold</label>
                    <input type="number" name="threshold" value="<?= $product['low_stock_threshold'] ?>" min="0">
                </div>

            </div> <div style="display:flex;gap:10px;margin-top:28px;padding-top:20px;border-top:1px solid var(--border);">
                <button class="btn btn-primary" name="submit">Save Changes</button>
                <a href="inventory.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</div> </body>
</html>
<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory — SyncDesk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
// Handle inline stock update
if (isset($_POST['update_stock'])) {
    $id    = (int)$_POST['id'];
    $stock = (int)$_POST['stock'];
    $stmt = $conn->prepare("UPDATE products SET stock=? WHERE id=?");
    $stmt->bind_param("ii", $stock, $id);
    $stmt->execute();
    $old = $_POST['old_stock'];
    logAction($conn, $id, 'Stock updated', "Stock changed from $old to $stock");
    header("Location: inventory.php");
    exit;
}

$search = $_GET['search'] ?? '';
$low_only = isset($_GET['low_only']);

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (sku LIKE ? OR product_name LIKE ? OR category LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = 'sss';
}

if ($low_only) {
    $sql .= " AND stock < low_stock_threshold";
}

$sql .= " ORDER BY stock ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

$total_count = count($products);
?>

<div class="topbar">
    <h1>Inventory</h1>
    <div class="topbar-actions">
        <a href="add.php" class="btn btn-primary">➕ Add Product</a>
    </div>
</div>

<!-- SEARCH + FILTERS -->
<div class="card mb-16">
    <div class="card-body">
        <form method="GET" class="flex gap-8 items-center">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, SKU, or category…" style="flex:1;padding:9px 14px;border:1px solid var(--border);border-radius:6px;font-family:'DM Sans',sans-serif;font-size:13.5px;">
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                <input type="checkbox" name="low_only" <?= $low_only ? 'checked' : '' ?> onchange="this.form.submit()">
                Low stock only
            </label>
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if ($search || $low_only): ?>
                <a href="inventory.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- TABLE -->
<div class="card">
    <div class="card-header">
        <span class="card-title"> Products <span class="badge badge-blue" style="margin-left:8px;"><?= $total_count ?></span></span>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <p>No products found<?= $search ? " for \"$search\"" : '' ?>.</p>
        </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <tr>
            <th>SKU</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Warehouse</th>
            <th>Stock</th>
            <th>Supplier</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $row): ?>
        <tr>
            <td><span class="sku-tag"><?= e($row['sku']) ?></span></td>
            <td>
                <strong><?= e($row['product_name']) ?></strong>
                <div class="text-muted">ID #<?= $row['id'] ?></div>
            </td>
            <td><?= e($row['category'] ?: '—') ?></td>
            <td><?= e($row['warehouse_location'] ?: '—') ?></td>
            <td>
                <!-- Inline stock edit -->
                <form method="POST" style="display:flex;align-items:center;gap:5px;">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="old_stock" value="<?= $row['stock'] ?>">
                    <input type="number" name="stock" value="<?= $row['stock'] ?>" 
                        style="width:70px;padding:4px 8px;border:1px solid var(--border);border-radius:4px;font-family:'DM Sans',sans-serif;
                        color:<?= $row['stock'] < 5 ? 'var(--danger)' : 'var(--success)' ?>; font-weight:600;" >
                    <button class="btn btn-success btn-sm" name="update_stock" title="Save stock">✓</button>
                </form>
                <?php if ($row['stock'] < 10): ?>
                    <div style="font-size:11px; color:var(--danger); margin-top:2px;">🚨 Critical</div>
                <?php elseif ($row['stock'] < 5): ?>
                    <div style="font-size:11px; color:var(--warning); margin-top:2px;">⚠️ Low</div>
                <?php endif; ?>
            </td>
            <td><?= e($row['supplier'] ?: '—') ?></td>
            <td>
                <div class="flex gap-8">
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" 
                       onclick="return confirm('Delete <?= e($row['product_name']) ?>?')">🗑️</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
    <?php endif; ?>
</div>

</div>
</body>
</html>

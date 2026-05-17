<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — SyncDesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
// Isang query lang para don sa apat (Total, Low Stock, Critical, Total Units)
$stats_query = $conn->query("
    SELECT 
        COUNT(*) AS total_products,
        SUM(CASE WHEN stock < low_stock_threshold THEN 1 ELSE 0 END) AS low_stock,
        SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) AS critical,
        SUM(stock) AS total_stock
    FROM products
")->fetch_assoc();

// ipnasa lang natin yung mga nakuuhang numero sa mga variable
$total_products  = $stats_query['total_products'] ?? 0;
$low_stock_count = $stats_query['low_stock'] ?? 0;
$critical_count  = $stats_query['critical'] ?? 0;
$total_stock     = $stats_query['total_stock'] ?? 0;

// Kinuha lang yung 8 produkto na pinakamababang stock para ma-check agad kung alin yung paubos na
$low_stock = $conn->query("SELECT * FROM products WHERE stock < low_stock_threshold ORDER BY stock ASC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Kinuha yung huling 8 activity kasama product name at SKU gamit ang LEFT JOIN para kumpleto details
$recent_logs = $conn->query("SELECT l.*, p.product_name, p.sku FROM logs l LEFT JOIN products p ON l.product_id = p.id ORDER BY l.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
?>

<div class="topbar">
    <h1>Dashboard</h1>
    <div class="topbar-actions">
        <span style="font-size:13px;color:var(--text-secondary);">
            <?= date('l, F j, Y') ?>
        </span>
    </div>
</div>

<?php if ($critical_count > 0): ?>
<div class="alert-banner danger">
    <span class="alert-icon">🚨</span>
    <strong><?= $critical_count ?> product(s)</strong> have critically low stock (below 10 units). Immediate restocking needed.
    <a href="inventory.php?low_only=1" style="margin-left:auto;color:inherit;font-weight:700;text-decoration:underline;">View →</a>
</div>
<?php elseif ($low_stock_count > 0): ?>
<div class="alert-banner">
    <span class="alert-icon">⚠️</span>
    <strong><?= $low_stock_count ?> product(s)</strong> are below their low stock threshold.
    <a href="inventory.php?low_only=1" style="margin-left:auto;color:inherit;font-weight:700;text-decoration:underline;">View →</a>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"></div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_products ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🔢</div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format($total_stock) ?></div>
            <div class="stat-label">Units in Stock</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">⚠️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $low_stock_count ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">🚨</div>
        <div class="stat-info">
            <div class="stat-value"><?= $critical_count ?></div>
            <div class="stat-label">Critical Stock</div>
        </div>
    </div>
</div>

<div class="two-col">

    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Low Stock Products</span>
            <a href="inventory.php?low_only=1" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (empty($low_stock)): ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <p>All products are well stocked!</p>
            </div>
        <?php else: ?>
        <table>
            <tr><th>SKU</th><th>Product</th><th>Stock</th><th>Threshold</th></tr>
            <?php foreach ($low_stock as $p): ?>
            <tr>
                <td><span class="sku-tag"><?= e($p['sku']) ?></span></td>
                <td><?= e($p['product_name']) ?></td>
                <td>
                    <?php if ($p['stock'] < 10): ?>
                        <span class="badge badge-red"><?= $p['stock'] ?></span>
                    <?php else: ?>
                        <span class="badge badge-orange"><?= $p['stock'] ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-muted"><?= $p['low_stock_threshold'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">📜 Recent Activity</span>
            <a href="logs.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <?php if (empty($recent_logs)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No activity yet.</p>
            </div>
        <?php else: ?>
        <table>
            <tr><th>Action</th><th>Product</th><th>Time</th></tr>
            <?php foreach ($recent_logs as $log): ?>
            <tr>
                <td>
                    <?php
                    // Pinapalitan ang kulay ng badge base sa kung anong klaseng action ang nangyari ( design lang to)
                    $badge = 'badge-gray';
                    if (str_contains($log['action'], 'alert')) $badge = 'badge-red';
                    elseif (str_contains($log['action'], 'Added')) $badge = 'badge-green';
                    elseif (str_contains($log['action'], 'Updated')) $badge = 'badge-blue';
                    elseif (str_contains($log['action'], 'Deleted')) $badge = 'badge-orange';
                    ?>
                    <span class="badge <?= $badge ?>"><?= e($log['action']) ?></span>
                </td>
                <td class="text-muted"><?= e($log['product_name'] ?? '—') ?></td>
                <td class="text-muted" style="white-space:nowrap;"><?= date('M j, g:ia', strtotime($log['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

</div>

</div>
</body>
</html>
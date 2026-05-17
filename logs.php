<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Logs — SyncDesk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
// Set ng pagination variables (20 items kada pahina)
$search   = trim($_GET['search'] ?? '');
$filter   = $_GET['filter'] ?? '';
$per_page = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

// FIX: Always explicitly initialize tracking arrays/strings
$where  = "WHERE 1=1";
$params = [];
$types  = '';

// Check kung may sine-search si user sa input box
if ($search !== '') {
    $where   .= " AND (l.action LIKE ? OR l.details LIKE ? OR p.product_name LIKE ? OR p.sku LIKE ?)";
    $like     = "%$search%";
    // FIX: Merge or push safely instead of hard-overwriting blindly
    $params   = array_merge($params, [$like, $like, $like, $like]);
    $types   .= 'ssss';
}

// Check kung may piniling specific action type sa dropdown filter
if ($filter !== '') {
    $where  .= " AND l.action LIKE ?";
    $params[] = "%$filter%";
    $types   .= 's';
}

// Query para mabilang lahat ng entries para sa pagination counters
$count_sql = "SELECT COUNT(*) AS c FROM logs l LEFT JOIN products p ON l.product_id = p.id $where";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$total_pages = ceil($total / $per_page);

// Query para makuha ang mismong data ng logs base sa limit at offset ng page ngayon
$sql = "SELECT l.*, p.product_name, p.sku FROM logs l LEFT JOIN products p ON l.product_id = p.id $where ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$all_params = array_merge($params, [$per_page, $offset]);
$all_types  = $types . 'ii';
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Kukuha ng mga unique action names para awtomatikong magka-laman ang dropdown filter
$action_types = $conn->query("SELECT DISTINCT action FROM logs ORDER BY action")->fetch_all(MYSQLI_ASSOC);
?>

<div class="topbar">
    <h1>Audit Logs</h1>
    <div class="topbar-actions">
        <span class="badge badge-blue"><?= number_format($total) ?> entries</span>
    </div>
</div>

<div class="card mb-16">
    <div class="card-body">
        <form method="GET" class="flex gap-8 items-center" style="flex-wrap:wrap;">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search logs…"
                style="flex:1;min-width:200px;padding:9px 14px;border:1px solid var(--border);border-radius:6px;font-family:'DM Sans',sans-serif;font-size:13.5px;">
            
            <select name="filter" onchange="this.form.submit()"
                style="padding:9px 14px;border:1px solid var(--border);border-radius:6px;font-family:'DM Sans',sans-serif;font-size:13.5px;color:var(--text);">
                <option value="">All Actions</option>
                <?php foreach ($action_types as $a): ?>
                    <option value="<?= e($a['action']) ?>" <?= $filter === $a['action'] ? 'selected' : '' ?>><?= e($a['action']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if ($search || $filter): ?>
                <a href="logs.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📜 Activity Log</span>
    </div>

    <?php if (empty($logs)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No log entries found.</p>
        </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
        <tr>
            <th>#</th>
            <th>Action</th>
            <th>Product</th>
            <th>Details</th>
            <th>User</th>
            <th>Date & Time</th>
        </tr>
        <?php foreach ($logs as $log): ?>
        <tr>
            <!-- FIX: Secure escaping added to ID -->
            <td class="text-muted"><?= e($log['id']) ?></td>
            <td>
                <?php
                // Taga-salpak ng tamang kulay ng badge depende sa action type
                $badge = 'badge-gray';
                if (str_contains($log['action'], 'alert'))       $badge = 'badge-red';
                elseif (str_contains($log['action'], 'Added'))   $badge = 'badge-green';
                elseif (str_contains($log['action'], 'Updated')) $badge = 'badge-blue';
                elseif (str_contains($log['action'], 'Deleted')) $badge = 'badge-orange';
                ?>
                <span class="badge <?= $badge ?>"><?= e($log['action']) ?></span>
            </td>
            <td>
                <?php if ($log['product_name']): ?>
                    <strong><?= e($log['product_name']) ?></strong>
                    <div class="text-muted"><span class="sku-tag"><?= e($log['sku']) ?></span></div>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td style="max-width:260px;"><?= e($log['details'] ?? '') ?></td>
            <td><span class="badge badge-gray"><?= e($log['user'] ?? 'System') ?></span></td>
            <td class="text-muted" style="white-space:nowrap;"><?= date('M j, Y g:i a', strtotime($log['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php
        // FIX: Cleaner URL building handling page seamlessly without structural trailing bugs
        $query_params = array_filter(['search' => $search, 'filter' => $filter]);
        for ($i = 1; $i <= $total_pages; $i++):
            $query_params['page'] = $i;
            $url = "logs.php?" . http_build_query($query_params);
        ?>
            <a href="<?= $url ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div> 
    <?php endif; ?> 

    <?php endif; ?> 
</div>  
</body>
</html>
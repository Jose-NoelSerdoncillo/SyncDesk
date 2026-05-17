<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Warehouses — SyncDesk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
$msg = '';

// ADD
if (isset($_POST['add'])) {
    $name     = trim($_POST['name']);
    $location = trim($_POST['location']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO warehouses (name, location) VALUES (?,?)");
        $stmt->bind_param("ss", $name, $location);
        $stmt->execute();
        $stmt->close();
        $msg = "Warehouse <strong>" . e($name) . "</strong> added.";
    }
}

// EDIT
if (isset($_POST['edit'])) {
    $id       = (int)$_POST['id'];
    $name     = trim($_POST['name']);
    $location = trim($_POST['location']);
    $stmt     = $conn->prepare("UPDATE warehouses SET name=?, location=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $location, $id);
    $stmt->execute();
    $stmt->close();
    $msg = "Warehouse updated.";
}

// DELETE
if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM warehouses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: warehouse.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) $msg = "Warehouse deleted.";

$edit_id   = (int)($_GET['edit'] ?? 0);
$edit_item = null;
if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$warehouses = $conn->query("SELECT w.*, COUNT(p.id) AS product_count FROM warehouses w LEFT JOIN products p ON p.warehouse_location = w.name GROUP BY w.id ORDER BY w.name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="topbar">
    <h1>Warehouses</h1>
    <span class="badge badge-blue"><?= count($warehouses) ?> total</span>
</div>

<?php if ($msg): ?>
<div class="alert-banner" style="background:#f0fdf4;border-color:#bbf7d0;border-left-color:var(--success);color:#166534;">
    <span class="alert-icon">✅</span> <?= $msg ?>
</div>
<?php endif; ?>

<div class="two-col" style="align-items:start;">

    <!-- FORM -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><?= $edit_item ? '✏️ Edit Warehouse' : '➕ Add Warehouse' ?></span>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Warehouse Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Warehouse A"
                        value="<?= e($edit_item['name'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Location / Address</label>
                    <input type="text" name="location" placeholder="e.g. Building 1, Zone A"
                        value="<?= e($edit_item['location'] ?? '') ?>">
                </div>
                <div class="flex gap-8">
                    <button class="btn btn-primary" name="<?= $edit_item ? 'edit' : 'add' ?>">
                        💾 <?= $edit_item ? 'Save Changes' : 'Add Warehouse' ?>
                    </button>
                    <?php if ($edit_item): ?>
                        <a href="warehouse.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- LIST -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"> All Warehouses</span>
        </div>
        <?php if (empty($warehouses)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏭</div>
                <p>No warehouses yet.</p>
            </div>
        <?php else: ?>
        <table>
            <tr><th>Name</th><th>Location</th><th>Products</th><th>Actions</th></tr>
            <?php foreach ($warehouses as $w): ?>
            <tr>
                <td><strong><?= e($w['name']) ?></strong></td>
                <td class="text-muted"><?= e($w['location'] ?: '—') ?></td>
                <td><span class="badge badge-blue"><?= $w['product_count'] ?></span></td>
                <td>
                    <div class="flex gap-8">
                        <a href="warehouse.php?edit=<?= $w['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                        <a href="warehouse.php?delete=<?= $w['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete warehouse <?= e($w['name']) ?>?')">🗑️</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

</div>

</div>
</body>
</html>

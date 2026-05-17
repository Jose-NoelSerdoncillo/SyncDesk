<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Suppliers — SyncDesk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
$msg = '';

// ADD
if (isset($_POST['add'])) {
    $name    = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email   = trim($_POST['email']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, contact, email) VALUES (?,?,?)");
        $stmt->bind_param("sss", $name, $contact, $email);
        $stmt->execute();
        $stmt->close();
        $msg = "Supplier <strong>" . e($name) . "</strong> added.";
    }
}

// EDIT
if (isset($_POST['edit'])) {
    $id      = (int)$_POST['id'];
    $name    = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email   = trim($_POST['email']);
    $stmt    = $conn->prepare("UPDATE suppliers SET name=?, contact=?, email=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $contact, $email, $id);
    $stmt->execute();
    $stmt->close();
    $msg = "Supplier updated.";
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: suppliers.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) $msg = "Supplier deleted.";

$edit_id   = (int)($_GET['edit'] ?? 0);
$edit_item = null;
if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$suppliers = $conn->query("SELECT s.*, COUNT(p.id) AS product_count FROM suppliers s LEFT JOIN products p ON p.supplier = s.name GROUP BY s.id ORDER BY s.name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="topbar">
    <h1>Suppliers</h1>
    <span class="badge badge-blue"><?= count($suppliers) ?> total</span>
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
            <span class="card-title"><?= $edit_item ? '✏️ Edit Supplier' : '➕ Add Supplier' ?></span>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Supplier Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Supplier X"
                        value="<?= e($edit_item['name'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Contact Number</label>
                    <input type="text" name="contact" placeholder="+63 912 000 0000"
                        value="<?= e($edit_item['contact'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="supplier@email.com"
                        value="<?= e($edit_item['email'] ?? '') ?>">
                </div>
                <div class="flex gap-8">
                    <button class="btn btn-primary" name="<?= $edit_item ? 'edit' : 'add' ?>">
                        💾 <?= $edit_item ? 'Save Changes' : 'Add Supplier' ?>
                    </button>
                    <?php if ($edit_item): ?>
                        <a href="suppliers.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- LIST -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🤝 All Suppliers</span>
        </div>
        <?php if (empty($suppliers)): ?>
            <div class="empty-state">
                <div class="empty-icon">🤝</div>
                <p>No suppliers yet.</p>
            </div>
        <?php else: ?>
        <table>
            <tr><th>Name</th><th>Contact</th><th>Email</th><th>Products</th><th>Actions</th></tr>
            <?php foreach ($suppliers as $s): ?>
            <tr>
                <td><strong><?= e($s['name']) ?></strong></td>
                <td class="text-muted"><?= e($s['contact'] ?: '—') ?></td>
                <td class="text-muted"><?= e($s['email'] ?: '—') ?></td>
                <td><span class="badge badge-blue"><?= $s['product_count'] ?></span></td>
                <td>
                    <div class="flex gap-8">
                        <a href="suppliers.php?edit=<?= $s['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
                        <a href="suppliers.php?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete supplier <?= e($s['name']) ?>?')">🗑️</a>
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

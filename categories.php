<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Categories — SyncDesk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">

<?php
$msg = '';

if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?,?)");
        $stmt->bind_param("ss", $name, $desc);
        $stmt->execute();
        $stmt->close();
        $msg = "Category <strong>" . e($name) . "</strong> added.";
    }
}

if (isset($_POST['edit'])) {
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $desc, $id);
    $stmt->execute();
    $stmt->close();
    $msg = "Category updated.";
}

if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: categories.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) $msg = "Category deleted.";

$edit_id   = (int)($_GET['edit'] ?? 0);
$edit_item = null;
if ($edit_id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category = c.name GROUP BY c.id ORDER BY c.name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="topbar">
    <h1>Categories</h1>
    <span class="badge badge-gray"><?= count($categories) ?> total</span>
</div>

<?php if ($msg): ?>
<div class="alert-banner" style="background:var(--success-bg);border-color:#86efac;border-left-color:var(--success);color:var(--success);">
    <?= $msg ?>
</div>
<?php endif; ?>

<div class="two-col" style="align-items:start;">

    <div class="card">
        <div class="card-header">
            <span class="card-title"><?= $edit_item ? 'Edit Category' : 'Add Category' ?></span>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <?php endif; ?>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Electronics"
                        value="<?= e($edit_item['name'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Optional description"
                        value="<?= e($edit_item['description'] ?? '') ?>">
                </div>
                <div class="flex gap-8">
                    <button class="btn btn-primary" name="<?= $edit_item ? 'edit' : 'add' ?>">
                        <?= $edit_item ? 'Save Changes' : 'Add Category' ?>
                    </button>
                    <?php if ($edit_item): ?>
                        <a href="categories.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">All Categories</span>
        </div>
        <?php if (empty($categories)): ?>
            <div class="empty-state"><p>No categories yet.</p></div>
        <?php else: ?>
        <table>
            <tr><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><strong><?= e($cat['name']) ?></strong></td>
                <td class="text-muted"><?= e($cat['description'] ?: '—') ?></td>
                <td><span class="badge badge-blue"><?= $cat['product_count'] ?></span></td>
                <td>
                    <div class="flex gap-8">
                        <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                        <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete category <?= e($cat['name']) ?>?')">Delete</a>
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

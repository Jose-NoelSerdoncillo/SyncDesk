<?php
$current = basename($_SERVER['PHP_SELF']);
function navLink($file, $label, $current) {
    $active = ($current === $file) ? 'active' : '';
    return "<a href='{$file}' class='{$active}'>{$label}</a>";
}
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">SyncDesk</div>
        <div class="tagline">Inventory Management</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <?= navLink('index.php',     'Dashboard',   $current) ?>
        <?= navLink('inventory.php', 'Inventory',   $current) ?>
        <?= navLink('warehouse.php', 'Warehouses',  $current) ?>

        <div class="nav-section-label">Management</div>
        <?= navLink('categories.php', 'Categories', $current) ?>
        <?= navLink('suppliers.php',  'Suppliers',  $current) ?>
        <?= navLink('logs.php',       'Audit Logs', $current) ?>

        <div class="nav-section-label">System</div>
        <?= navLink('settings.php', 'Settings', $current) ?>
    </nav>
    <div class="sidebar-footer">
        SyncDesk &nbsp;v2.0
    </div>
</div>

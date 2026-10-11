<?php
/**
 * Administrator Executive Dashboard & Analytics
 * Full System Privileges, Financial Valuations, and Administrative Oversight
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';
$extraJs = 'dashboard.js';
require_once __DIR__ . '/../includes/header.php';

// Fetch Executive Metrics
try {
    // 1. Total Products
    $prodCountStmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = (int)$prodCountStmt->fetchColumn();

    // 2. Financial Valuation (Retail Value & Cost Value)
    $valStmt = $pdo->query("SELECT SUM(quantity * unit_price) AS total_val, SUM(quantity * cost_price) AS total_cost FROM products");
    $valData = $valStmt->fetch();
    $totalValuation = (float)($valData['total_val'] ?? 0);
    $totalCostVal   = (float)($valData['total_cost'] ?? 0);
    $grossMargin    = $totalValuation - $totalCostVal;
    $marginPercent  = $totalCostVal > 0 ? (($grossMargin / $totalCostVal) * 100) : 0;

    // 3. Stock Health
    $lowStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity > 0 AND quantity <= min_threshold");
    $lowStockCount = (int)$lowStockStmt->fetchColumn();

    $outOfStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity = 0");
    $outOfStockCount = (int)$outOfStockStmt->fetchColumn();

    // 4. User Counts
    $userCountStmt = $pdo->query("SELECT 
        COUNT(*) AS total_users,
        SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) AS staff_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin_users
        FROM users");
    $userStats = $userCountStmt->fetch();
    $totalUsers = (int)($userStats['total_users'] ?? 0);
    $staffCount = (int)($userStats['staff_users'] ?? 0);

    // 5. Recent 8 Stock Transactions with User Attribution
    $txStmt = $pdo->query("SELECT st.*, p.name AS product_name, p.sku, u.full_name AS user_name, u.role AS user_role 
        FROM stock_transactions st
        JOIN products p ON st.product_id = p.id
        LEFT JOIN users u ON st.user_id = u.id
        ORDER BY st.created_at DESC LIMIT 8");
    $recentTransactions = $txStmt->fetchAll();

    // 6. Urgent Replenishment
    $urgentStmt = $pdo->query("SELECT p.*, c.name AS category_name, s.name AS supplier_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.quantity <= p.min_threshold 
        ORDER BY p.quantity ASC LIMIT 5");
    $urgentItems = $urgentStmt->fetchAll();

    // 7. Recent Users
    $recentUsersStmt = $pdo->query("SELECT id, full_name, username, role, status FROM users ORDER BY created_at DESC LIMIT 4");
    $recentUsers = $recentUsersStmt->fetchAll();

} catch (PDOException $e) {
    error_log("Admin Dashboard query error: " . $e->getMessage());
    $totalProducts = $totalValuation = $totalCostVal = $grossMargin = $marginPercent = 0;
    $lowStockCount = $outOfStockCount = $totalUsers = $staffCount = 0;
    $recentTransactions = [];
    $urgentItems = [];
    $recentUsers = [];
}
?>

<!-- Admin Welcome & Role Privilege Banner -->
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; border: none; box-shadow: var(--shadow-md);">
    <div class="card-body" style="padding: 1.5rem 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-weight: 700; letter-spacing: 0.05em;">ADMINISTRATOR PORTAL</span>
                <span style="font-size: 0.85rem; color: #cbd5e1;">Logged in as <strong><?= htmlspecialchars($user['full_name'] ?? 'Admin') ?></strong></span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 700; color: #ffffff; margin-bottom: 0.25rem;">Executive Overview & Inventory Controls</h2>
            <p style="font-size: 0.875rem; color: #c7d2fe; margin: 0;">Full authority over product pricing, catalog structure, staff accounts, financial valuations, and warehouse movements.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= url('pages/users.php') ?>" class="btn btn-sm" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
                👥 Manage Users (<?= $totalUsers ?>)
            </a>
            <a href="<?= url('pages/reports.php') ?>" class="btn btn-sm" style="background: #ffffff; color: #312e81; font-weight: 600;">
                📊 Financial Reports
            </a>
        </div>
    </div>
</div>

<!-- Admin Quick Action Bar -->
<div class="quick-actions-bar">
    <a href="<?= url('pages/product-add.php') ?>" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="16"></line>
            <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
        <span>Add New Product</span>
    </a>

    <a href="<?= url('pages/stock-in.php') ?>" class="btn btn-success">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <polyline points="19 12 12 19 5 12"></polyline>
        </svg>
        <span>Stock In (Intake)</span>
    </a>

    <a href="<?= url('pages/stock-out.php') ?>" class="btn btn-danger">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="19" x2="12" y2="5"></line>
            <polyline points="5 12 12 5 19 12"></polyline>
        </svg>
        <span>Stock Out (Dispatch)</span>
    </a>

    <a href="<?= url('pages/categories.php') ?>" class="btn btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
            <line x1="7" y1="7" x2="7.01" y2="7"></line>
        </svg>
        <span>Categories</span>
    </a>

    <a href="<?= url('pages/suppliers.php') ?>" class="btn btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <span>Suppliers</span>
    </a>

    <a href="<?= url('pages/staff-dashboard.php') ?>" class="btn btn-outline" style="margin-left: auto;" title="Preview the interface seen by staff members">
        <span>👁️ View Staff Portal</span>
    </a>
</div>

<!-- Admin Financial & Operational Metrics Grid -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Total Catalog Items</span>
            <span class="metric-value"><?= number_format($totalProducts) ?></span>
        </div>
        <div class="metric-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Retail Valuation</span>
            <span class="metric-value"><?= formatCurrency($totalValuation) ?></span>
        </div>
        <div class="metric-icon success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="6" y1="5" x2="18" y2="5"></line>
                <line x1="6" y1="9" x2="16" y2="9"></line>
                <path d="M9 5c3 0 6 1.5 6 4s-3 4-6 4h-2"></path>
                <path d="M9 13l7 7"></path>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Wholesale Cost Value</span>
            <span class="metric-value"><?= formatCurrency($totalCostVal) ?></span>
        </div>
        <div class="metric-icon" style="background: #e0f2fe; color: #0284c7;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Estimated Gross Profit</span>
            <span class="metric-value" style="color: #047857;"><?= formatCurrency($grossMargin) ?></span>
            <span style="font-size: 0.725rem; color: var(--text-muted); font-weight: 500;">+<?= number_format($marginPercent, 1) ?>% markup</span>
        </div>
        <div class="metric-icon" style="background: #ecfdf5; color: #059669;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                <polyline points="17 6 23 6 23 12"></polyline>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Low Stock Alerts</span>
            <span class="metric-value"><?= number_format($lowStockCount) ?></span>
        </div>
        <div class="metric-icon warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Out of Stock</span>
            <span class="metric-value"><?= number_format($outOfStockCount) ?></span>
        </div>
        <div class="metric-icon danger">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            </svg>
        </div>
    </div>
</div>

<!-- Dashboard 2-column Content -->
<div class="dashboard-grid">
    <!-- Left: Recent Movement Ledger with User Tracking -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Audit Trail & Recent Activity</h2>
                <small style="color: var(--text-muted);">Real-time log of movements with staff attribution</small>
            </div>
            <a href="<?= url('pages/stock-history.php') ?>" class="btn btn-sm btn-outline">View Full History &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Balance</th>
                            <th>Logged By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    No inventory movements recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $tx): ?>
                                <tr>
                                    <td><small><?= date('M d, H:i', strtotime($tx['created_at'])) ?></small></td>
                                    <td>
                                        <strong><?= htmlspecialchars($tx['product_name']) ?></strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($tx['sku']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $tx['type'] === 'IN' ? 'badge-movement-in' : 'badge-movement-out' ?>">
                                            <?= $tx['type'] === 'IN' ? '+ IN' : '- OUT' ?>
                                        </span>
                                    </td>
                                    <td><strong><?= number_format($tx['quantity']) ?></strong></td>
                                    <td><small><?= number_format($tx['balance_after']) ?></small></td>
                                    <td>
                                        <div><strong><?= htmlspecialchars($tx['user_name'] ?? 'System') ?></strong></div>
                                        <span class="badge <?= ($tx['user_role'] ?? '') === 'admin' ? 'badge-primary' : 'badge-secondary' ?>" style="font-size: 0.65rem;">
                                            <?= ucfirst($tx['user_role'] ?? 'staff') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Operational Warnings & Staff Widget -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Reorder Warnings -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h2 class="card-title">Reorder Attention</h2>
                <span class="badge badge-warning"><?= count($urgentItems) ?> Items</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Supplier</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($urgentItems)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 1.5rem; color: var(--text-muted);">
                                        All inventory levels are healthy!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($urgentItems as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($item['sku']) ?></div>
                                        </td>
                                        <td>
                                            <span class="stock-status-pill <?= $item['quantity'] == 0 ? 'outofstock' : 'lowstock' ?>">
                                                <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit_of_measure']) ?>
                                            </span>
                                        </td>
                                        <td><small><?= htmlspecialchars($item['supplier_name'] ?? '-') ?></small></td>
                                        <td>
                                            <a href="<?= url('pages/stock-in.php?product_id=' . $item['id']) ?>" class="btn btn-sm btn-success" title="Restock">
                                                + In
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Staff & Accounts Widget -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h2 class="card-title">User Accounts Overview</h2>
                    <small style="color: var(--text-muted);"><?= $staffCount ?> Staff, <?= $totalUsers - $staffCount ?> Admins</small>
                </div>
                <a href="<?= url('pages/users.php') ?>" class="btn btn-sm btn-outline">Manage &rarr;</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $ru): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($ru['full_name']) ?></strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">@<?= htmlspecialchars($ru['username']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $ru['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>">
                                            <?= ucfirst($ru['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="stock-status-pill <?= $ru['status'] === 'active' ? 'instock' : 'outofstock' ?>">
                                            <?= ucfirst($ru['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

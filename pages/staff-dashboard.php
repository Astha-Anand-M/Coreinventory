<?php
/**
 * Warehouse Staff Operations Dashboard
 * Focused on Physical Stock In, Stock Out, Inventory Verification, and Replenishment
 * Financial valuations and administrative controls are omitted for operational staff
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Staff Operations Dashboard';
$extraJs = 'dashboard.js';
require_once __DIR__ . '/../includes/header.php';

$currentUserId = currentUser()['id'] ?? 0;

// Fetch Staff Operational Metrics
try {
    // 1. Total Products Count
    $prodCountStmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = (int)$prodCountStmt->fetchColumn();

    // 2. Physical Inventory Count (Total physical units in facility)
    $unitsStmt = $pdo->query("SELECT SUM(quantity) FROM products");
    $totalPhysicalUnits = (int)($unitsStmt->fetchColumn() ?? 0);

    // 3. Stock Health (Alerts)
    $lowStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity > 0 AND quantity <= min_threshold");
    $lowStockCount = (int)$lowStockStmt->fetchColumn();

    $outOfStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity = 0");
    $outOfStockCount = (int)$outOfStockStmt->fetchColumn();

    // 4. Staff Personal Activity
    $myTxStmt = $pdo->prepare("SELECT COUNT(*) FROM stock_transactions WHERE user_id = :uid");
    $myTxStmt->execute([':uid' => $currentUserId]);
    $myTransactionsCount = (int)$myTxStmt->fetchColumn();

    // 5. Activity Today (Units In & Out)
    $todayStmt = $pdo->query("SELECT 
        COALESCE(SUM(CASE WHEN type = 'IN' THEN quantity ELSE 0 END), 0) AS units_in_today,
        COALESCE(SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END), 0) AS units_out_today,
        COUNT(*) AS total_tx_today
        FROM stock_transactions 
        WHERE DATE(created_at) = CURDATE()");
    $todayStats = $todayStmt->fetch();
    $unitsInToday  = (int)($todayStats['units_in_today'] ?? 0);
    $unitsOutToday = (int)($todayStats['units_out_today'] ?? 0);

    // 6. Recent Warehouse Movements (Recent 8)
    $txStmt = $pdo->query("SELECT st.*, p.name AS product_name, p.sku, u.full_name AS user_name 
        FROM stock_transactions st
        JOIN products p ON st.product_id = p.id
        LEFT JOIN users u ON st.user_id = u.id
        ORDER BY st.created_at DESC LIMIT 8");
    $recentTransactions = $txStmt->fetchAll();

    // 7. Reorder Priority Queue (Urgent replenishment needs)
    $urgentStmt = $pdo->query("SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.quantity <= p.min_threshold 
        ORDER BY p.quantity ASC LIMIT 6");
    $urgentItems = $urgentStmt->fetchAll();

} catch (PDOException $e) {
    error_log("Staff Dashboard query error: " . $e->getMessage());
    $totalProducts = $totalPhysicalUnits = $lowStockCount = $outOfStockCount = $myTransactionsCount = 0;
    $unitsInToday = $unitsOutToday = 0;
    $recentTransactions = [];
    $urgentItems = [];
}
?>

<!-- Staff Welcome & Operational Status Banner -->
<div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); color: #ffffff; border: none; box-shadow: var(--shadow-md);">
    <div class="card-body" style="padding: 1.5rem 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-weight: 700; letter-spacing: 0.05em;">STAFF OPERATIONS PORTAL</span>
                <span style="font-size: 0.85rem; color: #ccfbf1;">Operator: <strong><?= htmlspecialchars($user['full_name'] ?? 'Staff') ?></strong></span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 700; color: #ffffff; margin-bottom: 0.25rem;">Warehouse Stock Floor & Fulfillment</h2>
            <p style="font-size: 0.875rem; color: #99f6e4; margin: 0;">Authorized for physical receiving (Stock In), order picking & dispatch (Stock Out), and inventory level monitoring.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= url('pages/stock-in.php') ?>" class="btn btn-sm" style="background: #ffffff; color: #115e59; font-weight: 600;">
                📥 Stock In Delivery
            </a>
            <a href="<?= url('pages/stock-out.php') ?>" class="btn btn-sm" style="background: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3);">
                📤 Dispatch Items
            </a>
        </div>
    </div>
</div>

<!-- Operational Quick Action Buttons -->
<div class="quick-actions-bar">
    <a href="<?= url('pages/stock-in.php') ?>" class="btn btn-success" style="padding: 0.75rem 1.25rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <polyline points="19 12 12 19 5 12"></polyline>
        </svg>
        <span style="font-size: 0.95rem; font-weight: 600;">Record Stock In (Intake)</span>
    </a>

    <a href="<?= url('pages/stock-out.php') ?>" class="btn btn-danger" style="padding: 0.75rem 1.25rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="19" x2="12" y2="5"></line>
            <polyline points="5 12 12 5 19 12"></polyline>
        </svg>
        <span style="font-size: 0.95rem; font-weight: 600;">Record Stock Out (Dispatch)</span>
    </a>

    <a href="<?= url('pages/products.php') ?>" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        <span>Search Catalog</span>
    </a>

    <a href="<?= url('pages/stock-history.php') ?>" class="btn btn-outline" style="margin-left: auto;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span>My Movement Logs</span>
    </a>
</div>

<!-- Operational Metrics Grid (No sensitive wholesale costs or valuation) -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Products in Catalog</span>
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
            <span class="metric-label">Units on Hand</span>
            <span class="metric-value"><?= number_format($totalPhysicalUnits) ?></span>
        </div>
        <div class="metric-icon success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
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
            <span class="metric-label">Out of Stock Items</span>
            <span class="metric-value"><?= number_format($outOfStockCount) ?></span>
        </div>
        <div class="metric-icon danger">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">My Operations Logged</span>
            <span class="metric-value"><?= number_format($myTransactionsCount) ?></span>
        </div>
        <div class="metric-icon" style="background: #f0fdf4; color: #16a34a;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
    </div>
</div>

<!-- Dashboard 2-column Content -->
<div class="dashboard-grid">
    <!-- Left: Restock Priority List -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Urgent Replenishment Queue</h2>
                <small style="color: var(--text-muted);">Stock items requiring immediate intake receipt</small>
            </div>
            <a href="<?= url('pages/reports.php') ?>" class="btn btn-sm btn-outline">All Alerts &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Details</th>
                            <th>Category</th>
                            <th>On Hand</th>
                            <th>Min Level</th>
                            <th>Restock Deficit</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($urgentItems)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2.5rem; color: #059669; background: #ecfdf5;">
                                    ✅ All warehouse inventory items are currently above safety thresholds.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($urgentItems as $item): 
                                $deficit = $item['min_threshold'] - $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($item['sku']) ?></div>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($item['category_name'] ?? 'General') ?></span></td>
                                    <td>
                                        <span class="stock-status-pill <?= $item['quantity'] == 0 ? 'outofstock' : 'lowstock' ?>">
                                            <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit_of_measure']) ?>
                                        </span>
                                    </td>
                                    <td><?= $item['min_threshold'] ?></td>
                                    <td>
                                        <strong style="color: var(--danger);">+<?= max(0, $deficit) ?> units</strong>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="<?= url('pages/stock-in.php?product_id=' . $item['id']) ?>" class="btn btn-sm btn-success" title="Record Intake">
                                            + Stock In
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

    <!-- Right: Today's Floor Activity & Guidelines -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- Today's Floor Summary -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h2 class="card-title">Today's Warehouse Movements</h2>
                <span class="badge badge-primary"><?= date('M d, Y') ?></span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div style="background: var(--success-light); border: 1px solid #a7f3d0; border-radius: var(--radius-sm); padding: 1rem; text-align: center;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #065f46; text-transform: uppercase;">Intake Received</span>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #047857;">+<?= number_format($unitsInToday) ?></div>
                        <small style="color: #065f46;">units today</small>
                    </div>

                    <div style="background: var(--danger-light); border: 1px solid #fecaca; border-radius: var(--radius-sm); padding: 1rem; text-align: center;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #991b1b; text-transform: uppercase;">Dispatched</span>
                        <div style="font-size: 1.5rem; font-weight: 700; color: #b91c1c;">-<?= number_format($unitsOutToday) ?></div>
                        <small style="color: #991b1b;">units today</small>
                    </div>
                </div>

                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0; text-align: center;">
                    Always ensure bill of lading or dispatch slips match reference numbers entered.
                </p>
            </div>
        </div>

        <!-- Staff Role & Guidelines Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <h2 class="card-title">Staff Privileges Guide</h2>
                <span class="badge badge-secondary">Policy</span>
            </div>
            <div class="card-body" style="font-size: 0.85rem; line-height: 1.6;">
                <ul style="padding-left: 1.25rem; color: var(--text-main); margin-bottom: 1rem;">
                    <li><strong>Stock In:</strong> Allowed for all incoming vendor restocks and order returns.</li>
                    <li><strong>Stock Out:</strong> Allowed for order shipments and damaged item removals.</li>
                    <li><strong>Catalog:</strong> View-only permissions. Contact an Administrator to create new products or modify pricing.</li>
                    <li><strong>Pricing Data:</strong> Wholesale supplier costs are confidential and managed by Admins.</li>
                </ul>

                <div style="background: #f8fafc; border-left: 3px solid var(--primary); padding: 0.65rem 0.85rem; border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                    <small style="color: var(--text-muted);">
                        Need administrator access? Please contact your inventory supervisor.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Warehouse Transactions -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Recent Warehouse Movement Log</h2>
        <a href="<?= url('pages/stock-history.php') ?>" class="btn btn-sm btn-outline">Full Transaction History &rarr;</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reason / Reference</th>
                        <th>Operator</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                No recent transactions found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td><small><?= date('M d, H:i', strtotime($tx['created_at'])) ?></small></td>
                                <td><strong><?= htmlspecialchars($tx['product_name']) ?></strong></td>
                                <td><code><?= htmlspecialchars($tx['sku']) ?></code></td>
                                <td>
                                    <span class="badge <?= $tx['type'] === 'IN' ? 'badge-movement-in' : 'badge-movement-out' ?>">
                                        <?= $tx['type'] === 'IN' ? '+ IN' : '- OUT' ?>
                                    </span>
                                </td>
                                <td><strong><?= number_format($tx['quantity']) ?></strong></td>
                                <td>
                                    <small><?= htmlspecialchars($tx['reason']) ?></small>
                                    <?php if (!empty($tx['reference_no'])): ?>
                                        <div><small style="color: var(--text-muted);">Ref: <?= htmlspecialchars($tx['reference_no']) ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($tx['user_name'] ?? 'Warehouse Staff') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

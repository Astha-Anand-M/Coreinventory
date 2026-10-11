<?php
/**
 * Reports & Valuation Analytics View
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Inventory Reports & Alerts';
require_once __DIR__ . '/../includes/header.php';

// 1. Valuation & Stock counts by Category
$catValQuery = "SELECT 
                    c.name AS category_name,
                    COUNT(p.id) AS total_items,
                    COALESCE(SUM(p.quantity), 0) AS total_units,
                    COALESCE(SUM(p.quantity * p.cost_price), 0) AS total_cost_value,
                    COALESCE(SUM(p.quantity * p.unit_price), 0) AS total_retail_value
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                GROUP BY c.id
                ORDER BY total_retail_value DESC";
$categoryReports = $pdo->query($catValQuery)->fetchAll();

// 2. Comprehensive Low Stock Alert List
$lowStockQuery = "SELECT p.*, c.name AS category_name, s.name AS supplier_name, s.phone AS supplier_phone, s.email AS supplier_email
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN suppliers s ON p.supplier_id = s.id
                  WHERE p.quantity <= p.min_threshold
                  ORDER BY p.quantity ASC";
$lowStockItems = $pdo->query($lowStockQuery)->fetchAll();

// 3. Overall Totals
$totals = [
    'units'        => 0,
    'cost_value'   => 0,
    'retail_value' => 0
];
foreach ($categoryReports as $cr) {
    $totals['units']        += $cr['total_units'];
    $totals['cost_value']   += $cr['total_cost_value'];
    $totals['retail_value'] += $cr['total_retail_value'];
}
?>

<!-- Print Button Header -->
<div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
    <button onclick="window.print();" class="btn btn-outline btn-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        <span>Print Report</span>
    </button>
</div>

<!-- Reports Banner -->
<?php if (!isAdmin()): ?>
    <div class="alert alert-info" style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
        <span><strong>Staff Operations View:</strong> You have access to physical stock counts and replenishment alert reports. Financial valuations, capital investment, and profit margin analysis are restricted to Administrators.</span>
        <span class="badge badge-secondary">Staff Mode</span>
    </div>
<?php endif; ?>

<!-- Low Stock Alerts Report -->
<div class="card">
    <div class="card-header" style="background: var(--warning-light);">
        <div>
            <h2 class="card-title" style="color: #92400e;">⚠️ Urgent Replenishment & Low Stock Report</h2>
            <small style="color: #b45309;">Products currently at or below minimum threshold point</small>
        </div>
        <span class="badge badge-warning"><?= count($lowStockItems) ?> Items Require Action</span>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock Remaining</th>
                        <th>Reorder Level</th>
                        <th>Deficit</th>
                        <th>Supplier Contact</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lowStockItems)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #047857; background: #ecfdf5;">
                                ✅ Excellent! All catalog items have sufficient stock above reorder thresholds.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lowStockItems as $item): 
                            $deficit = $item['min_threshold'] - $item['quantity'];
                        ?>
                            <tr>
                                <td><code><?= htmlspecialchars($item['sku']) ?></code></td>
                                <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['category_name'] ?? 'None') ?></span></td>
                                <td>
                                    <span class="stock-status-pill <?= $item['quantity'] == 0 ? 'outofstock' : 'lowstock' ?>">
                                        <?= $item['quantity'] ?> <?= htmlspecialchars($item['unit_of_measure']) ?>
                                    </span>
                                </td>
                                <td><?= $item['min_threshold'] ?> <?= htmlspecialchars($item['unit_of_measure']) ?></td>
                                <td><strong style="color: var(--danger);">+<?= max(0, $deficit) ?> needed</strong></td>
                                <td>
                                    <div><small><strong><?= htmlspecialchars($item['supplier_name'] ?? 'No supplier') ?></strong></small></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($item['supplier_phone'] ?? $item['supplier_email'] ?? '') ?></div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= url('pages/stock-in.php?product_id=' . $item['id']) ?>" class="btn btn-sm btn-success">+ Restock</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (isAdmin()): ?>
    <!-- Inventory Valuation by Category (Admin Only) -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Inventory Financial Valuation by Category</h2>
                <small style="color: var(--text-muted);">Breakdown of physical assets, cost investment, and estimated retail potential (Admin Exclusive)</small>
            </div>
            <span class="badge badge-primary">Financial Intelligence</span>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Unique Items</th>
                            <th>Total Units in Stock</th>
                            <th>Total Cost Investment</th>
                            <th>Total Retail Value</th>
                            <th>Gross Margin Potential</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoryReports as $cat): 
                            $margin = $cat['total_retail_value'] - $cat['total_cost_value'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($cat['category_name']) ?></strong></td>
                                <td><?= $cat['total_items'] ?></td>
                                <td><strong><?= number_format($cat['total_units']) ?></strong></td>
                                <td><?= formatCurrency($cat['total_cost_value']) ?></td>
                                <td><strong><?= formatCurrency($cat['total_retail_value']) ?></strong></td>
                                <td>
                                    <span style="color: <?= $margin >= 0 ? '#047857' : '#b91c1c' ?>; font-weight: 600;">
                                        <?= formatCurrency($margin) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f1f5f9; font-weight: 700;">
                            <td>TOTALS</td>
                            <td>-</td>
                            <td><?= number_format($totals['units']) ?> units</td>
                            <td><?= formatCurrency($totals['cost_value']) ?></td>
                            <td><?= formatCurrency($totals['retail_value']) ?></td>
                            <td style="color: #047857;"><?= formatCurrency($totals['retail_value'] - $totals['cost_value']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>

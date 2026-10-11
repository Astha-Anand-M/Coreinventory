<?php
/**
 * Stock Out (Inventory Dispatch & Deduction) View
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Stock Out (Dispatch & Sales)';
$extraJs = 'stock.js';
require_once __DIR__ . '/../includes/header.php';

$selectedProductId = (int)($_GET['product_id'] ?? 0);

// Fetch products with positive quantity
$products = $pdo->query("SELECT id, name, sku, quantity, unit_of_measure FROM products ORDER BY name ASC")->fetchAll();
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Record Outgoing Stock (Dispatch)</h2>
        <a href="<?= url('pages/stock-history.php') ?>" class="btn btn-sm btn-outline">View History &rarr;</a>
    </div>

    <div class="card-body">
        <form action="<?= url('api/stock.php?action=stock_out') ?>" method="POST" id="stockForm">
            <input type="hidden" id="isStockOut" value="1">

            <!-- Product Selection -->
            <div class="form-group">
                <label class="form-label required" for="stockProductSelect">Select Product</label>
                <select id="stockProductSelect" name="product_id" class="form-control" required autofocus>
                    <option value="">-- Choose Item to Deduct --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" 
                                data-stock="<?= $p['quantity'] ?>" 
                                data-unit="<?= htmlspecialchars($p['unit_of_measure']) ?>"
                                <?= $selectedProductId === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>) - Available: <?= $p['quantity'] ?> <?= htmlspecialchars($p['unit_of_measure']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Current Stock Info Badge -->
            <div id="stockInfoCard" style="display: <?= $selectedProductId ? 'block' : 'none' ?>; background: var(--warning-light); border: 1px solid #fde68a; border-radius: var(--radius-sm); padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <span style="font-size: 0.85rem; color: #92400e;">
                    Current Available Stock: <strong id="currentStockDisplay">-</strong>
                </span>
            </div>

            <div class="form-grid">
                <!-- Quantity to Deduct -->
                <div class="form-group">
                    <label class="form-label required" for="quantity">Quantity to Deduct (-)</label>
                    <input type="number" min="1" id="quantity" name="quantity" class="form-control" placeholder="e.g. 5" required>
                    <span class="form-text">Cannot exceed current available stock.</span>
                </div>

                <!-- Dispatch Reason -->
                <div class="form-group">
                    <label class="form-label required" for="reason">Dispatch Reason</label>
                    <select id="reason" name="reason" class="form-control" required>
                        <option value="Sale / Customer Order">Customer Sale / Order Fulfillment</option>
                        <option value="Damaged / Broken">Damaged / Defective Stock</option>
                        <option value="Internal Consumption">Internal Office / Warehouse Use</option>
                        <option value="Return to Vendor">Return to Vendor / Supplier</option>
                        <option value="Expired">Expired / Obsolete Write-off</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <!-- Reference Number -->
                <div class="form-group">
                    <label class="form-label" for="reference_no">Order / Invoice / Ticket #</label>
                    <input type="text" id="reference_no" name="reference_no" class="form-control" placeholder="e.g. INV-90412">
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label" for="notes">Notes / Reason Details</label>
                    <input type="text" id="notes" name="notes" class="form-control" placeholder="e.g. Shipped via FedEx Ground">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= url('pages/dashboard.php') ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Confirm Stock Out</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

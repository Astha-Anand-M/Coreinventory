<?php
/**
 * Stock In (Inventory Intake) View
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Stock In (Intake & Restock)';
$extraJs = 'stock.js';
require_once __DIR__ . '/../includes/header.php';

$selectedProductId = (int)($_GET['product_id'] ?? 0);

// Fetch all active products
$products = $pdo->query("SELECT id, name, sku, quantity, unit_of_measure FROM products ORDER BY name ASC")->fetchAll();
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Record Incoming Stock (Restock)</h2>
        <a href="<?= url('pages/stock-history.php') ?>" class="btn btn-sm btn-outline">View History &rarr;</a>
    </div>

    <div class="card-body">
        <form action="<?= url('api/stock.php?action=stock_in') ?>" method="POST" id="stockForm">
            <!-- Product Selection -->
            <div class="form-group">
                <label class="form-label required" for="stockProductSelect">Select Product</label>
                <select id="stockProductSelect" name="product_id" class="form-control" required autofocus>
                    <option value="">-- Choose Item to Restock --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" 
                                data-stock="<?= $p['quantity'] ?>" 
                                data-unit="<?= htmlspecialchars($p['unit_of_measure']) ?>"
                                <?= $selectedProductId === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>) - Current Stock: <?= $p['quantity'] ?> <?= htmlspecialchars($p['unit_of_measure']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Current Stock Info Badge -->
            <div id="stockInfoCard" style="display: <?= $selectedProductId ? 'block' : 'none' ?>; background: var(--primary-light); border: 1px solid #bfdbfe; border-radius: var(--radius-sm); padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <span style="font-size: 0.85rem; color: var(--primary);">
                    Available Inventory on Hand: <strong id="currentStockDisplay">-</strong>
                </span>
            </div>

            <div class="form-grid">
                <!-- Quantity Received -->
                <div class="form-group">
                    <label class="form-label required" for="quantity">Quantity Received (+)</label>
                    <input type="number" min="1" id="quantity" name="quantity" class="form-control" placeholder="e.g. 50" required>
                    <span class="form-text">Will increment current inventory balance.</span>
                </div>

                <!-- Intake Reason -->
                <div class="form-group">
                    <label class="form-label required" for="reason">Intake Reason</label>
                    <select id="reason" name="reason" class="form-control" required>
                        <option value="Restock">Vendor Restock / Delivery</option>
                        <option value="Customer Return">Customer Return</option>
                        <option value="Audit Adjustment">Inventory Audit Count Correction</option>
                        <option value="Other">Other / Miscellaneous</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <!-- Reference Number -->
                <div class="form-group">
                    <label class="form-label" for="reference_no">PO / Delivery Slip / Invoice #</label>
                    <input type="text" id="reference_no" name="reference_no" class="form-control" placeholder="e.g. PO-84920">
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label" for="notes">Notes / Observations</label>
                    <input type="text" id="notes" name="notes" class="form-control" placeholder="e.g. Received shipment in good condition">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= url('pages/dashboard.php') ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Confirm Stock In</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

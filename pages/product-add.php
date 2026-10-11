<?php
/**
 * Add New Product Form View
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();

$pageTitle = 'Add New Product';
$extraJs = 'products.js';
require_once __DIR__ . '/../includes/header.php';

// Fetch categories & suppliers for dropdowns
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Register New Inventory Item</h2>
        <a href="<?= url('pages/products.php') ?>" class="btn btn-sm btn-outline">&larr; Back to Catalog</a>
    </div>

    <div class="card-body">
        <form action="<?= url('api/products.php?action=create') ?>" method="POST">
            <div class="form-grid">
                <!-- SKU with Auto Generate -->
                <div class="form-group">
                    <label class="form-label required" for="sku">SKU / Item Barcode</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="sku" name="sku" class="form-control" placeholder="e.g. SKU-100234" required>
                        <button type="button" class="btn btn-outline btn-sm" onclick="generateSKU();" title="Auto Generate SKU">Generate</button>
                    </div>
                    <span class="form-text">Must be a unique inventory identifier code.</span>
                </div>

                <!-- Product Name -->
                <div class="form-group">
                    <label class="form-label required" for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Ergonomic Office Chair" required>
                </div>
            </div>

            <div class="form-grid">
                <!-- Category -->
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Supplier -->
                <div class="form-group">
                    <label class="form-label" for="supplier_id">Primary Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-control">
                        <option value="">-- Select Supplier --</option>
                        <?php foreach ($suppliers as $sup): ?>
                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <!-- Cost Price -->
                <div class="form-group">
                    <label class="form-label" for="cost_price">Cost Price (₹)</label>
                    <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" class="form-control" value="0.00" placeholder="0.00">
                    <span class="form-text">Cost to acquire from vendor.</span>
                </div>

                <!-- Selling Price -->
                <div class="form-group">
                    <label class="form-label required" for="unit_price">Selling / Unit Price (₹)</label>
                    <input type="number" step="0.01" min="0" id="unit_price" name="unit_price" class="form-control" value="0.00" placeholder="0.00" required>
                    <span class="form-text">Retail or sale price per unit.</span>
                </div>
            </div>

            <div class="form-grid">
                <!-- Initial Stock -->
                <div class="form-group">
                    <label class="form-label" for="quantity">Initial Stock On Hand</label>
                    <input type="number" min="0" id="quantity" name="quantity" class="form-control" value="0">
                    <span class="form-text">Will automatically log an initial stock intake entry.</span>
                </div>

                <!-- Low Stock Threshold -->
                <div class="form-group">
                    <label class="form-label required" for="min_threshold">Low Stock Alert Threshold</label>
                    <input type="number" min="1" id="min_threshold" name="min_threshold" class="form-control" value="5" required>
                    <span class="form-text">Triggers a warning when stock drops to or below this level.</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="unit_of_measure">Unit of Measure</label>
                <input type="text" id="unit_of_measure" name="unit_of_measure" class="form-control" value="pcs" placeholder="pcs, kg, box, bundle, pair">
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="description">Description / Specifications</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter product specs, dimensions, or warehouse location notes..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= url('pages/products.php') ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

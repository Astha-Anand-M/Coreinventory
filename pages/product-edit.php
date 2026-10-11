<?php
/**
 * Edit Product Form View
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    setFlash('error', 'Product not specified.');
    header('Location: ' . url('pages/products.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: ' . url('pages/products.php'));
    exit;
}

$pageTitle = 'Edit Product: ' . $product['name'];
require_once __DIR__ . '/../includes/header.php';

// Fetch categories & suppliers
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Modify Product Information</h2>
        <a href="<?= url('pages/products.php') ?>" class="btn btn-sm btn-outline">&larr; Back to Catalog</a>
    </div>

    <div class="card-body">
        <form action="<?= url('api/products.php?action=update') ?>" method="POST">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label required" for="sku">SKU / Item Barcode</label>
                    <input type="text" id="sku" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="supplier_id">Primary Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-control">
                        <option value="">-- Select Supplier --</option>
                        <?php foreach ($suppliers as $sup): ?>
                            <option value="<?= $sup['id'] ?>" <?= $product['supplier_id'] == $sup['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sup['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="cost_price">Cost Price (₹)</label>
                    <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" class="form-control" value="<?= htmlspecialchars($product['cost_price']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label required" for="unit_price">Selling / Unit Price (₹)</label>
                    <input type="number" step="0.01" min="0" id="unit_price" name="unit_price" class="form-control" value="<?= htmlspecialchars($product['unit_price']) ?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="current_stock">Current Stock On Hand</label>
                    <input type="text" id="current_stock" class="form-control" value="<?= $product['quantity'] ?> <?= htmlspecialchars($product['unit_of_measure']) ?>" disabled>
                    <span class="form-text">To modify quantity, use <a href="<?= url('pages/stock-in.php?product_id=' . $product['id']) ?>">Stock In</a> or <a href="<?= url('pages/stock-out.php?product_id=' . $product['id']) ?>">Stock Out</a> to keep audit integrity.</span>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="min_threshold">Low Stock Alert Threshold</label>
                    <input type="number" min="1" id="min_threshold" name="min_threshold" class="form-control" value="<?= htmlspecialchars($product['min_threshold']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="unit_of_measure">Unit of Measure</label>
                <input type="text" id="unit_of_measure" name="unit_of_measure" class="form-control" value="<?= htmlspecialchars($product['unit_of_measure']) ?>" placeholder="pcs, kg, box">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description / Notes</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= url('pages/products.php') ?>" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

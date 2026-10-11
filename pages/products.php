<?php
/**
 * Master Products Catalog View
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Products Catalog';
$extraJs = 'products.js';
require_once __DIR__ . '/../includes/header.php';

// Fetch categories for filtering
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// Fetch products with joined category and supplier
$query = "SELECT p.*, c.name AS category_name, s.name AS supplier_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN suppliers s ON p.supplier_id = s.id 
          ORDER BY p.name ASC";
$products = $pdo->query($query)->fetchAll();
?>

<?php if (!isAdmin()): ?>
    <div class="alert alert-info" style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
        <span><strong>Staff Operations View:</strong> You have permissions to view inventory quantities and execute Stock In / Stock Out transactions. Product creation, editing, deletion, and wholesale cost data are restricted to Administrators.</span>
        <span class="badge badge-secondary">Staff Mode</span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Inventory Items (<?= count($products) ?>)</h2>
        <?php if (isAdmin()): ?>
            <a href="<?= url('pages/product-add.php') ?>" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add New Item</span>
            </a>
        <?php else: ?>
            <span class="badge badge-primary">Catalog Directory</span>
        <?php endif; ?>
    </div>

    <!-- Filters Bar -->
    <div class="card-body" style="padding-bottom: 0.5rem;">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" id="productSearchInput" class="form-control" placeholder="Search by name, SKU, or supplier...">
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <select id="categoryFilter" class="form-control" style="width: auto; min-width: 170px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="statusFilter" class="form-control" style="width: auto; min-width: 160px;">
                    <option value="">All Stock Status</option>
                    <option value="instock">In Stock</option>
                    <option value="lowstock">Low Stock Alert</option>
                    <option value="outofstock">Out of Stock</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" id="productsTable">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <?php if (isAdmin()): ?>
                            <th>Cost Price</th>
                        <?php endif; ?>
                        <th>Selling Price</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="<?= isAdmin() ? 9 : 8 ?>" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                No products found in the catalog. <?= isAdmin() ? '<a href="' . url('pages/product-add.php') . '">Create your first product</a>.' : '' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): 
                            $status = 'instock';
                            $statusLabel = 'In Stock';
                            if ($p['quantity'] == 0) {
                                $status = 'outofstock';
                                $statusLabel = 'Out of Stock';
                            } elseif ($p['quantity'] <= $p['min_threshold']) {
                                $status = 'lowstock';
                                $statusLabel = 'Low Stock';
                            }
                        ?>
                            <tr data-category="<?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>" data-status="<?= $status ?>">
                                <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                    <?php if (!empty($p['description'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($p['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($p['category_name'] ?? 'None') ?></span></td>
                                <td><small><?= htmlspecialchars($p['supplier_name'] ?? '-') ?></small></td>
                                <?php if (isAdmin()): ?>
                                    <td><small><?= formatCurrency($p['cost_price']) ?></small></td>
                                <?php endif; ?>
                                <td><strong><?= formatCurrency($p['unit_price']) ?></strong></td>
                                <td>
                                    <strong><?= number_format($p['quantity']) ?></strong> 
                                    <small><?= htmlspecialchars($p['unit_of_measure']) ?></small>
                                </td>
                                <td>
                                    <span class="stock-status-pill <?= $status ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem;">
                                        <a href="<?= url('pages/stock-in.php?product_id=' . $p['id']) ?>" class="btn btn-sm btn-success" title="Quick Stock In">+ In</a>
                                        <a href="<?= url('pages/stock-out.php?product_id=' . $p['id']) ?>" class="btn btn-sm btn-outline" title="Quick Stock Out">- Out</a>
                                        <?php if (isAdmin()): ?>
                                            <a href="<?= url('pages/product-edit.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline" title="Edit Product">Edit</a>
                                            <a href="<?= url('api/products.php?action=delete&id=' . $p['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product? All movement history will also be removed.');" title="Delete">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Suppliers Directory View
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Suppliers Directory';
require_once __DIR__ . '/../includes/header.php';

// Fetch suppliers with products supplied count
$query = "SELECT s.*, COUNT(p.id) AS supplied_count 
          FROM suppliers s 
          LEFT JOIN products p ON s.id = p.supplier_id 
          GROUP BY s.id 
          ORDER BY s.name ASC";
$suppliers = $pdo->query($query)->fetchAll();
?>

<?php if (!isAdmin()): ?>
    <div class="alert alert-info" style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
        <span><strong>Staff Mode:</strong> Suppliers directory is displayed for contact reference. Adding, modifying, or removing supplier records is restricted to Administrators.</span>
        <span class="badge badge-secondary">Read-Only</span>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: <?= isAdmin() ? '1fr 2fr' : '1fr' ?>; gap: 1.5rem; align-items: start;">
    <?php if (isAdmin()): ?>
        <!-- Add Supplier Card (Admin Only) -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Add New Supplier</h2>
            </div>
            <div class="card-body">
                <form action="<?= url('api/suppliers.php?action=create') ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label required" for="sup_name">Company / Vendor Name</label>
                        <input type="text" id="sup_name" name="name" class="form-control" placeholder="e.g. Apex Hardware Supplies" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact_person">Contact Representative</label>
                        <input type="text" id="contact_person" name="contact_person" class="form-control" placeholder="e.g. Michael Scott">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sup_email">Email Address</label>
                        <input type="email" id="sup_email" name="email" class="form-control" placeholder="orders@apexsupply.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sup_phone">Phone Number</label>
                        <input type="text" id="sup_phone" name="phone" class="form-control" placeholder="+1 (555) 019-2834">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sup_addr">Office / Warehouse Address</label>
                        <textarea id="sup_addr" name="address" class="form-control" rows="2" placeholder="Street, City, State, ZIP"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Save Supplier</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Suppliers Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Registered Suppliers (<?= count($suppliers) ?>)</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Contact Details</th>
                            <th>Products</th>
                            <?php if (isAdmin()): ?>
                                <th style="text-align: right;">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="<?= isAdmin() ? 5 : 4 ?>" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    No suppliers registered yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $sup): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($sup['name']) ?></strong>
                                        <?php if (!empty($sup['address'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($sup['address']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($sup['contact_person'] ?? '-') ?></td>
                                    <td>
                                        <div><small><?= htmlspecialchars($sup['email'] ?? '-') ?></small></div>
                                        <div><small style="color: var(--text-muted);"><?= htmlspecialchars($sup['phone'] ?? '-') ?></small></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= $sup['supplied_count'] ?> items</span>
                                    </td>
                                    <?php if (isAdmin()): ?>
                                        <td style="text-align: right;">
                                            <a href="<?= url('api/suppliers.php?action=delete&id=' . $sup['id']) ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Delete this supplier record?');">
                                                Delete
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

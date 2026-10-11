<?php
/**
 * Stock Movement Audit Log & Transaction History
 */
require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Stock Movement History';
require_once __DIR__ . '/../includes/header.php';

// Filter parameters
$typeFilter = $_GET['type'] ?? '';
$search     = trim($_GET['search'] ?? '');

$query = "SELECT st.*, p.name AS product_name, p.sku, p.unit_of_measure, u.full_name AS user_name 
          FROM stock_transactions st
          JOIN products p ON st.product_id = p.id
          LEFT JOIN users u ON st.user_id = u.id
          WHERE 1=1";

$params = [];

if (in_array($typeFilter, ['IN', 'OUT'])) {
    $query .= " AND st.type = :type";
    $params[':type'] = $typeFilter;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE :s1 OR p.sku LIKE :s2 OR st.reference_no LIKE :s3 OR st.reason LIKE :s4)";
    $params[':s1'] = "%{$search}%";
    $params[':s2'] = "%{$search}%";
    $params[':s3'] = "%{$search}%";
    $params[':s4'] = "%{$search}%";
}

$query .= " ORDER BY st.created_at DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Inventory Audit Trail (<?= count($transactions) ?> Entries)</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= url('pages/stock-in.php') ?>" class="btn btn-sm btn-success">+ New Stock In</a>
            <a href="<?= url('pages/stock-out.php') ?>" class="btn btn-sm btn-danger">- New Stock Out</a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card-body" style="padding-bottom: 0.5rem;">
        <form method="GET" action="<?= url('pages/stock-history.php') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="search" class="form-control" placeholder="Search by SKU, product, reference, or reason..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="type" class="form-control" style="width: auto; min-width: 150px;" onchange="this.form.submit();">
                <option value="">All Types (IN & OUT)</option>
                <option value="IN" <?= $typeFilter === 'IN' ? 'selected' : '' ?>>Stock In Only (+)</option>
                <option value="OUT" <?= $typeFilter === 'OUT' ? 'selected' : '' ?>>Stock Out Only (-)</option>
            </select>

            <button type="submit" class="btn btn-outline">Filter</button>
            <?php if (!empty($search) || !empty($typeFilter)): ?>
                <a href="<?= url('pages/stock-history.php') ?>" class="btn btn-sm btn-outline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference / Slip</th>
                        <th>Product & SKU</th>
                        <th>Movement</th>
                        <th>Quantity</th>
                        <th>Stock Transition</th>
                        <th>Reason / Notes</th>
                        <th>Operator</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                No movement transactions matching your filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td>
                                    <div><strong><?= date('M d, Y', strtotime($t['created_at'])) ?></strong></div>
                                    <small style="color: var(--text-muted);"><?= date('H:i:s', strtotime($t['created_at'])) ?></small>
                                </td>
                                <td>
                                    <code><?= htmlspecialchars($t['reference_no'] ?: 'N/A') ?></code>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($t['product_name']) ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($t['sku']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $t['type'] === 'IN' ? 'badge-movement-in' : 'badge-movement-out' ?>">
                                        <?= $t['type'] === 'IN' ? '+ STOCK IN' : '- STOCK OUT' ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= number_format($t['quantity']) ?></strong> 
                                    <small><?= htmlspecialchars($t['unit_of_measure']) ?></small>
                                </td>
                                <td>
                                    <small style="color: var(--text-muted);"><?= $t['balance_before'] ?></small> 
                                    &rarr; 
                                    <strong><?= $t['balance_after'] ?></strong>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($t['reason']) ?></strong></div>
                                    <?php if (!empty($t['notes'])): ?>
                                        <small style="color: var(--text-muted);"><?= htmlspecialchars($t['notes']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-outline"><?= htmlspecialchars($t['user_name'] ?? 'System') ?></span>
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

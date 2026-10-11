<?php
/**
 * Administrator User & Staff Management View
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();

$pageTitle = 'User & Staff Management';
require_once __DIR__ . '/../includes/header.php';

// Fetch users
$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, created_at DESC")->fetchAll();

$totalUsers = count($users);
$adminCount = 0;
$staffCount = 0;
$activeCount = 0;

foreach ($users as $u) {
    if ($u['role'] === 'admin') $adminCount++;
    if ($u['role'] === 'staff') $staffCount++;
    if ($u['status'] === 'active') $activeCount++;
}

$currentUserId = currentUser()['id'] ?? 0;
?>

<!-- Statistics Overview -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Total Accounts</span>
            <span class="metric-value"><?= $totalUsers ?></span>
        </div>
        <div class="metric-icon primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Administrators</span>
            <span class="metric-value"><?= $adminCount ?></span>
        </div>
        <div class="metric-icon" style="background: #f3e8ff; color: #7e22ce;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-data">
            <span class="metric-label">Warehouse Staff</span>
            <span class="metric-value"><?= $staffCount ?></span>
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
            <span class="metric-label">Active Users</span>
            <span class="metric-value"><?= $activeCount ?></span>
        </div>
        <div class="metric-icon warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 14 14"></polyline>
            </svg>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2.2fr; gap: 1.5rem; align-items: start;">
    <!-- Add New User Form Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Create New Account</h2>
        </div>
        <div class="card-body">
            <form action="<?= url('api/users.php?action=create') ?>" method="POST">
                <div class="form-group">
                    <label class="form-label required" for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. John Doe" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="e.g. jdoe" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="jdoe@inventory.local" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="role">System Role & Privileges</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="staff">Staff (Warehouse Operations, Intake & Dispatch)</option>
                        <option value="admin">Administrator (Full Executive & System Access)</option>
                    </select>
                    <span class="form-text">Staff have restricted operational privileges; Admins have full privileges.</span>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="password">Initial Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Create User Account</button>
            </form>
        </div>
    </div>

    <!-- Users List Table Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">System Users & Privileges (<?= $totalUsers ?>)</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th style="text-align: right;">Privilege Controls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): 
                            $isSelf = ((int)$u['id'] === (int)$currentUserId);
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                                    <?php if ($isSelf): ?>
                                        <span class="badge badge-outline" style="font-size: 0.65rem; margin-left: 0.25rem;">You</span>
                                    <?php endif; ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">@<?= htmlspecialchars($u['username']) ?></div>
                                </td>
                                <td><small><?= htmlspecialchars($u['email']) ?></small></td>
                                <td>
                                    <span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="stock-status-pill <?= $u['status'] === 'active' ? 'instock' : 'outofstock' ?>">
                                        <?= ucfirst($u['status']) ?>
                                    </span>
                                </td>
                                <td><small><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
                                <td style="text-align: right;">
                                    <?php if ($isSelf): ?>
                                        <small style="color: var(--text-muted);">Current Active Session</small>
                                    <?php else: ?>
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                            <!-- Role switch -->
                                            <form action="<?= url('api/users.php?action=change_role') ?>" method="POST" style="margin: 0; display: inline;">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'staff' : 'admin' ?>">
                                                <button type="submit" class="btn btn-sm btn-outline" title="Switch between Admin and Staff">
                                                    <?= $u['role'] === 'admin' ? 'Make Staff' : 'Make Admin' ?>
                                                </button>
                                            </form>

                                            <!-- Status toggle -->
                                            <a href="<?= url('api/users.php?action=toggle_status&id=' . $u['id']) ?>" 
                                               class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-outline' : 'btn-success' ?>" 
                                               title="<?= $u['status'] === 'active' ? 'Deactivate account' : 'Activate account' ?>">
                                                <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                            </a>

                                            <!-- Delete -->
                                            <a href="<?= url('api/users.php?action=delete&id=' . $u['id']) ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to permanently delete user <?= htmlspecialchars($u['full_name']) ?>?');" 
                                               title="Delete User">
                                                Delete
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

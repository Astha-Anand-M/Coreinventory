<?php
/**
 * Core Inventory - User Sign In
 */
require_once __DIR__ . '/../config/config.php';

// Redirect if already authenticated
if (isLoggedIn()) {
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/auth.css') ?>">
</head>
<body class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-brand">
                <div class="brand-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <span>Core<strong>Inventory</strong></span>
            </div>
            <p class="auth-subtitle">Sign in to manage warehouse stock & catalog</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" style="margin: 0 2rem 1rem 2rem;">
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <div class="auth-body">
            <form action="<?= url('api/auth.php?action=login') ?>" method="POST">
                <div class="form-group">
                    <label class="form-label required" for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="admin or staff" required autofocus>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label required" for="password">Password</label>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    Sign In to Dashboard
                </button>
            </form>

            <div class="demo-credentials-box">
                <p style="font-weight: 700; margin-bottom: 0.5rem;">Role-Based Test Accounts:</p>
                <div style="margin-bottom: 0.65rem; padding-bottom: 0.5rem; border-bottom: 1px dashed #cbd5e1;">
                    <p style="margin: 0;"><strong>👑 Administrator</strong> (Executive Access):</p>
                    <div style="font-size: 0.8rem; margin: 0.2rem 0;"><code>admin</code> / <code>admin123</code></div>
                    <small style="color: var(--text-muted);">Full permissions: User management, wholesale pricing, add/edit/delete items, financial valuation reports.</small>
                </div>
                <div>
                    <p style="margin: 0;"><strong>📦 Warehouse Staff</strong> (Operations Access):</p>
                    <div style="font-size: 0.8rem; margin: 0.2rem 0;"><code>staff</code> / <code>staff123</code></div>
                    <small style="color: var(--text-muted);">Floor permissions: Stock In (Intake), Stock Out (Dispatch), inventory checks. Confidential wholesale costs & deletions restricted.</small>
                </div>
            </div>
        </div>

        <div class="auth-footer">
            <p>Need a new account? <a href="<?= url('pages/register.php') ?>">Create an Account</a></p>
        </div>
    </div>
</body>
</html>

<?php
/**
 * Core Inventory - User Registration
 */
require_once __DIR__ . '/../config/config.php';

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
    <title>Create Account | <?= APP_NAME ?></title>
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
            <p class="auth-subtitle">Register new warehouse or management account</p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" style="margin: 0 2rem 1rem 2rem;">
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <div class="auth-body">
            <form action="<?= url('api/auth.php?action=register') ?>" method="POST" id="registerForm">
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
                    <input type="email" id="email" name="email" class="form-control" placeholder="jdoe@example.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="role">Account Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="staff">Warehouse Staff (Intake & Dispatch)</option>
                        <option value="admin">Administrator (Full Access)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    Create Account
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?= url('pages/login.php') ?>">Sign In</a></p>
        </div>
    </div>

    <script src="<?= url('assets/js/auth.js') ?>"></script>
</body>
</html>

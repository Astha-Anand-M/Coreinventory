<?php
/**
 * ==============================================================================
 * Core Inventory - Database Auto-Installer / Migration Runner
 * ==============================================================================
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$title = "Database Setup & Initializer";
$status = null;
$error = null;

if (!$pdo) {
    $error = "Unable to connect to MySQL database. Please verify your Railway credentials (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD or DATABASE_URL) in Vercel.";
} else {
    // Check if table already exists
    try {
        $checkStmt = $pdo->query("SHOW TABLES LIKE 'users'");
        $exists = $checkStmt->fetchColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run']) || !$exists) {
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                
                // Remove any leftover CREATE DATABASE or USE statements
                $sql = preg_replace('/CREATE\s+DATABASE[^;]+;/i', '', $sql);
                $sql = preg_replace('/USE\s+`?[a-zA-Z0-9_]+`?;/i', '', $sql);
                
                // Execute multi-query script
                $pdo->exec($sql);
                $status = "Database successfully initialized! All tables and seed data created.";
            } else {
                $error = "Schema file not found at " . htmlspecialchars($schemaFile);
            }
        } else {
            $status = "Tables already exist in this database. Click 'Re-initialize Database' if you want to reset all tables.";
        }
    } catch (Throwable $e) {
        $error = "Setup failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/auth.css') ?>">
</head>
<body class="auth-wrapper">
    <div class="auth-container" style="max-width: 550px;">
        <div class="auth-header">
            <div class="auth-brand">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
                <span><?= APP_NAME ?> Database Setup</span>
            </div>
            <p class="auth-subtitle">Initialize tables & seed default accounts on Railway MySQL</p>
        </div>

        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($status): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                    <strong>Success:</strong> <?= htmlspecialchars($status) ?>
                </div>
            <?php endif; ?>

            <div style="background: rgba(15, 23, 42, 0.6); padding: 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
                <h4 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 0.95rem;">Database Seed Accounts:</h4>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                    <li><strong>Admin:</strong> <code>admin</code> / <code>admin123</code></li>
                    <li><strong>Staff:</strong> <code>staff</code> / <code>staff123</code></li>
                </ul>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <form method="POST" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">
                        Initialize / Reset Database
                    </button>
                </form>
                <a href="<?= url('pages/login.php') ?>" class="btn btn-outline" style="text-decoration: none;">
                    Go to Login &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>

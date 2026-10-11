<?php
/**
 * Global Header Component
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = $pageTitle ?? APP_NAME;
$user = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?></title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/dashboard.css') ?>">
    <?php if (isset($extraCss)): ?>
        <link rel="stylesheet" href="<?= url('assets/css/' . $extraCss) ?>">
    <?php endif; ?>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="main-container">
            <!-- Top Navbar -->
            <header class="top-navbar">
                <div class="navbar-left">
                    <button id="sidebarToggle" class="btn-icon" aria-label="Toggle Sidebar" title="Toggle Sidebar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="page-header-title"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>

                <div class="navbar-right">
                    <?php if ($user): ?>
                        <div class="user-profile-badge">
                            <div class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
                            <div class="user-info">
                                <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
                                <span class="user-role badge badge-<?= $user['role'] === 'admin' ? 'admin' : 'staff' ?>">
                                    <?= $user['role'] === 'admin' ? '👑 Admin' : '📦 Staff' ?>
                                </span>
                            </div>
                        </div>
                        <a href="<?= url('api/auth.php?action=logout') ?>" class="btn btn-outline btn-sm btn-logout" title="Sign Out">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>Logout</span>
                        </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Flash Alert Messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible" id="flashAlert">
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                    <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Page Body Content -->
            <main class="page-content">

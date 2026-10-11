<?php
/**
 * Authentication Check Middleware
 * Include at the top of protected pages to verify session
 */
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Please sign in to access the system.');
    header('Location: ' . url('pages/login.php'));
    exit;
}

/**
 * Middleware function to enforce admin-only access on pages
 */
function requireAdmin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please sign in to access this page.');
        header('Location: ' . url('pages/login.php'));
        exit;
    }
    if (!isAdmin()) {
        setFlash('error', 'Access denied. Administrator privileges are required to access this resource.');
        header('Location: ' . url('pages/dashboard.php'));
        exit;
    }
}

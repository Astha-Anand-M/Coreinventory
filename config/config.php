<?php
/**
 * ==============================================================================
 * Core Inventory Management System - Global Configuration & Utilities
 * ==============================================================================
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Global App Constants
define('APP_NAME', 'Core Inventory');
define('APP_VERSION', '1.0.0');

/**
 * Determine dynamic base URL for both local XAMPP and Vercel environments
 */
function getBaseUrl(): string {
    // Check if running under XAMPP subdirectory (e.g. /Coreinventory or /Core_inventory2)
    $candidates = [
        $_SERVER['SCRIPT_NAME'] ?? '',
        $_SERVER['REQUEST_URI'] ?? ''
    ];
    
    foreach ($candidates as $cand) {
        if (preg_match('#^/([^/?]+)#', $cand, $matches)) {
            $firstDir = $matches[1];
            if (stripos($firstDir, 'Coreinventory') !== false || stripos($firstDir, 'Core_inventory') !== false) {
                return '/' . $firstDir;
            }
        }
    }
    
    // On Vercel or root domain, base URL is root path
    return '';
}

define('BASE_URL', getBaseUrl());

/**
 * Helper to generate relative application URLs (immune to mixed content and proxy issues)
 */
function url(string $path = ''): string {
    $trimmedPath = ltrim($path, '/');
    if (empty(BASE_URL)) {
        return '/' . $trimmedPath;
    }
    return rtrim(BASE_URL, '/') . '/' . $trimmedPath;
}

/**
 * Check if a user is currently logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current authenticated user details
 */
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'full_name' => $_SESSION['user_name'] ?? 'User',
        'username'  => $_SESSION['username'] ?? '',
        'email'     => $_SESSION['user_email'] ?? '',
        'role'      => $_SESSION['user_role'] ?? 'staff'
    ];
}

/**
 * Check if the logged-in user is an admin
 */
function isAdmin(): bool {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'admin');
}

/**
 * Check if the logged-in user is staff
 */
function isStaff(): bool {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'staff');
}

/**
 * Set a flash notification message (success, error, warning, info)
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear the flash notification message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Input sanitization helper
 */
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency value
 */
function formatCurrency(float $amount): string {
    return '₹' . number_format($amount, 2);
}

/**
 * Standardized JSON API response helper
 */
function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

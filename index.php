<?php
/**
 * Core Inventory - Root Dispatcher
 */
require_once __DIR__ . '/config/config.php';
header('Location: ' . url('pages/dashboard.php'));
exit;

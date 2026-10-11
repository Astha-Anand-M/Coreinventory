<?php
/**
 * Database Connection Entrypoint
 * Bridges to config/db.php
 */
require_once __DIR__ . '/config/db.php';
return $pdo ?? getDBConnection();

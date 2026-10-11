<?php
/**
 * Database Connection Alias
 * Points directly to config/db.php
 */
require_once __DIR__ . '/db.php';
return $pdo ?? getDBConnection();

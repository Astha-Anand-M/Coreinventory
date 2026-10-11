<?php
/**
 * ==============================================================================
 * Core Inventory Management System - Database Connection Manager
 * ==============================================================================
 * Handles robust PDO database connections for both:
 * 1. Cloud Production: Railway MySQL (supports MYSQL* env vars, DATABASE_URL/MYSQL_URL, and SSL)
 * 2. Local Development: XAMPP / MariaDB (supports port 3306 and port 3307 automatically)
 */

if (!function_exists('getDBConnection')) {
    function getDBConnection(): PDO {
        static $pdo = null;

        if ($pdo !== null) {
            return $pdo;
        }

        // 1. Check for single Connection String URL (Provided by Railway / cloud providers)
        $dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
        if ($dbUrl) {
            $parsedUrl = parse_url($dbUrl);
            if ($parsedUrl) {
                $host = $parsedUrl['host'] ?? '127.0.0.1';
                $port = $parsedUrl['port'] ?? 3306;
                $user = $parsedUrl['user'] ?? 'root';
                $pass = $parsedUrl['pass'] ?? '';
                $dbName = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : 'core_inventory';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                try {
                    $pdo = new PDO($dsn, $user, $pass, $options);
                    return $pdo;
                } catch (PDOException $e) {
                    error_log("Database connection failed via URL: " . $e->getMessage());
                }
            }
        }

        // 2. Resolve parameters from standard Environment Variables (Railway / Vercel / .env)
        // Railway typically defines MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
        $host   = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
        $port   = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: null);
        $dbName = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'core_inventory');
        $user   = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
        $pass   = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '');

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        // 3. Connect: if port was explicitly defined, use it
        if ($port) {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo;
            } catch (PDOException $e) {
                error_log("PDO Connection error with explicit port {$port}: " . $e->getMessage());
                throw new PDOException("Database connection error: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        // 4. Local Development Autodetection: try port 3306 first, fallback to 3307 (common in XAMPP)
        $portsToTry = [3306, 3307];
        $lastException = null;

        foreach ($portsToTry as $tryPort) {
            try {
                $dsn = "mysql:host={$host};port={$tryPort};dbname={$dbName};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, $options);
                return $pdo;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        // If local ports failed, throw exception
        error_log("Database connection failed on all ports: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
        throw new PDOException("Database connection error: " . ($lastException ? $lastException->getMessage() : 'Unable to connect to database'), 500);
    }
}

/**
 * OOP Singleton Wrapper for OOP-style access
 */
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::$instance = getDBConnection();
        }
        return self::$instance;
    }

    public static function getInstance(): PDO {
        return self::getConnection();
    }
}

// Global variable instance for procedural scripts
try {
    $pdo = getDBConnection();
} catch (Throwable $e) {
    // Keep $pdo null if not yet configured, letting individual endpoints handle connection errors gracefully
    $pdo = null;
    error_log("Notice: Unable to initialize global \$pdo: " . $e->getMessage());
}

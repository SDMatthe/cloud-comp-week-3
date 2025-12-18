<?php
/**
 * Database Configuration - MySQL
 * Redis Cache Configuration
 * Unified configuration file for all database operations
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cloudcomp-db');
define('DB_CHARSET', 'utf8mb4');

// Redis configuration
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);
define('REDIS_DB', 0);
define('REDIS_TIMEOUT', 0);

/**
 * Get PDO Database Connection
 * @return PDO|null
 */
function getDBConnection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get Redis Connection
 * @return Redis|null
 */
function getRedisConnection() {
    try {
        if (class_exists('Redis')) {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_TIMEOUT);
            $redis->select(REDIS_DB);
            return $redis;
        }
        return null;
    } catch (Exception $e) {
        error_log('Redis connection failed: ' . $e->getMessage());
        return null;
    }
}

// Define other constants
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);
define('CACHE_TIMEOUT', 3600); // 1 hour cache

// CSRF Token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check and regenerate session if needed
 */
function checkSessionTimeout() {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            session_destroy();
            return false;
        }
        $_SESSION['login_time'] = time();
    }
    return true;
}
?>

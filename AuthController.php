<?php
namespace App\Controllers;

use Exception;
use PDO;

class AuthController {
    private $db;
    private $cache;

    public function __construct(PDO $db, \Redis $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    // Register user
    public function register($email, $password, $name) {
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }
        
        if (strlen($name) < 2 || strlen($name) > 100) {
            return ['success' => false, 'message' => 'Name must be between 2 and 100 characters'];
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM shopusers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $this->db->prepare("
                INSERT INTO shopusers (name, email, password, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $hashedPassword]);

            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login
    public function login($email, $password) {
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM shopusers WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Regenerate session ID
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['login_time'] = time();

            // Cache session in Redis
            $token = bin2hex(random_bytes(32));
            $this->cache->setex("session_{$token}", CACHE_TIMEOUT, json_encode([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'login_time' => time()
            ]));

            return ['success' => true, 'token' => $token, 'user' => $user];
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed'];
        }
    }

    // Logout
    public function logout($token = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear Redis cache if token provided
        if ($token && $this->cache) {
            $this->cache->delete("session_{$token}");
        }
        
        session_destroy();
        return ['success' => true];
    }
}
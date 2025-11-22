<?php
namespace App\Controllers;

use Exception;

class AuthController {
    private $db;
    private $cache;

    public function __construct(PDO $db, \Redis $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    // Register user
    public function register($email, $password, $name) {
        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM shopusers WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $this->db->prepare("
                INSERT INTO shopusers (name, email, password_hash, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $hashedPassword]);

            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Login
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM shopusers WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Generate JWT token placeholder
        $token = $this->generateToken($user['id'], $user['email']);

        // Cache session
        $this->cache->setex("session_{$token}", 86400, json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ]));

        return ['success' => true, 'token' => $token, 'user' => $user];
    }

    private function generateToken($userId, $email) {
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + 86400
        ];
        return 'jwt_token_here'; 
    }

}
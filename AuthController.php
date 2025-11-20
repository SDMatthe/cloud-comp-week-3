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
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $mfaSecret = $this->generateMFASecret();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password_hash, name, mfa_secret, mfa_enabled, created_at)
                VALUES (?, ?, ?, ?, FALSE, NOW())
            ");
            $stmt->execute([$email, $hashedPassword, $name, $mfaSecret]);

            return ['success' => true, 'user_id' => $this->db->lastInsertId(), 'mfa_secret' => $mfaSecret];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Login with MFA
    public function login($email, $password, $mfaCode = null) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Check MFA if enabled
        if ($user['mfa_enabled']) {
            if (!$mfaCode || !$this->verifyMFACode($user['mfa_secret'], $mfaCode)) {
                return ['success' => false, 'message' => 'Invalid MFA code', 'requires_mfa' => true];
            }
        }

        // Generate JWT token
        $token = $this->generateToken($user['id'], $user['email']);

        // Cache session
        $this->cache->setex("session_{$token}", 86400, json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ]));

        return ['success' => true, 'token' => $token, 'user' => $user];
    }

    // Enable MFA
    public function enableMFA($userId) {
        $secret = $this->generateMFASecret();
        $stmt = $this->db->prepare("UPDATE users SET mfa_secret=?, mfa_enabled=TRUE WHERE id=?");
        $stmt->execute([$secret, $userId]);

        return ['success' => true, 'secret' => $secret, 'qr_code' => $this->generateQRCode($secret)];
    }

    // OAuth (Google/GitHub)
    public function oauthLogin($provider, $accessToken) {
        // Verify token with provider
        $userInfo = $this->verifyOAuthToken($provider, $accessToken);

        if (!$userInfo) {
            return ['success' => false, 'message' => 'Invalid OAuth token'];
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE oauth_provider=? AND oauth_id=?");
        $stmt->execute([$provider, $userInfo['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Create new user
            $stmt = $this->db->prepare("
                INSERT INTO users (email, name, oauth_provider, oauth_id, mfa_enabled, created_at)
                VALUES (?, ?, ?, ?, FALSE, NOW())
            ");
            $stmt->execute([$userInfo['email'], $userInfo['name'], $provider, $userInfo['id']]);
            $userId = $this->db->lastInsertId();
        } else {
            $userId = $user['id'];
        }

        $token = $this->generateToken($userId, $userInfo['email']);
        
        return ['success' => true, 'token' => $token];
    }

    private function generateMFASecret() {
        return bin2hex(random_bytes(16));
    }

    private function verifyMFACode($secret, $code) {
        // Implement TOTP verification (Google Authenticator)
        // Using a library like OTPHP
        return true; // Simplified
    }

    private function generateToken($userId, $email) {
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + 86400
        ];
        // Use JWT library like firebase/php-jwt
        return 'jwt_token_here'; // Simplified
    }

    private function generateQRCode($secret) {
        // Generate QR code for MFA setup
        return 'qr_code_url';
    }

    private function verifyOAuthToken($provider, $token) {
        // Verify with Azure AD, Google, or GitHub
        return [];
    }
}
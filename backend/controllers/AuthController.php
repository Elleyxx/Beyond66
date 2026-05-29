<?php

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Jwt.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function login(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $missing = Validator::required($payload, ['password']);

        $identity = trim((string) ($payload['email'] ?? $payload['username'] ?? ''));
        if ($identity === '') {
            $missing[] = 'email_or_username';
        }

        if (!empty($missing)) {
            Response::json(['success' => false, 'message' => 'Email/username and password are required'], 422);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByIdentity($identity);

        if (!$user || !password_verify((string) $payload['password'], $user['password_hash'])) {
            Response::json(['success' => false, 'message' => 'Invalid credentials'], 401);
            return;
        }

        $token = createJwt([
            'sub' => (int) $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'display_name' => $user['display_name'],
            'iat' => time(),
        ]);

        Response::json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => (int) $user['id'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'display_name' => $user['display_name'],
                ],
            ]
        ]);
    }

    public function register(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $missing = Validator::required($payload, ['name', 'email', 'username', 'password']);

        if (!empty($missing)) {
            Response::json([
                'success' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missing)
            ], 422);
            return;
        }

        $email = strtolower(trim((string) $payload['email']));
        $name = trim((string) $payload['name']);
        $username = trim((string) $payload['username']);
        $contact = trim((string) ($payload['contact'] ?? ''));
        $password = (string) $payload['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => 'Invalid email format'], 422);
            return;
        }

        if (strlen($password) < 6) {
            Response::json(['success' => false, 'message' => 'Password must be at least 6 characters'], 422);
            return;
        }

        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            Response::json(['success' => false, 'message' => 'Username must be 3-30 characters and use only letters, numbers, underscore'], 422);
            return;
        }

        if ($contact !== '' && !preg_match('/^[0-9+\\-\\s]{8,20}$/', $contact)) {
            Response::json(['success' => false, 'message' => 'Invalid contact number format'], 422);
            return;
        }

        $userModel = new User();
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser) {
            Response::json(['success' => false, 'message' => 'Email is already registered'], 409);
            return;
        }

        $existingUsername = $userModel->findByUsername($username);
        if ($existingUsername) {
            Response::json(['success' => false, 'message' => 'Username is already taken'], 409);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $displayName = $name;
        $userId = $userModel->create($email, $passwordHash, $displayName, $username);

        $token = createJwt([
            'sub' => $userId,
            'email' => $email,
            'username' => $username,
            'display_name' => $displayName,
            'iat' => time(),
        ]);

        Response::json([
            'success' => true,
            'message' => 'Register successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                ]
            ]
        ], 201);
    }
}

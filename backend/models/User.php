<?php

class User
{
    private \PDO $pdo;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $this->pdo = new \PDO($dsn, $config['username'], $config['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByIdentity(string $identity): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE email = :email_identity OR username = :username_identity LIMIT 1'
        );
        $stmt->execute([
            'email_identity' => $identity,
            'username_identity' => $identity,
        ]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(string $email, string $passwordHash, string $displayName, string $username): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash, display_name, username) VALUES (:email, :password_hash, :display_name, :username)'
        );

        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'display_name' => $displayName,
            'username' => $username,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}

<?php

if (!function_exists('backendEnvValue')) {
    function backendEnvValue(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) {
            return $default;
        }

        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$envKey, $envValue] = explode('=', $line, 2);
            if (trim($envKey) === $key) {
                return trim($envValue, " \t\n\r\0\x0B\"'");
            }
        }

        return $default;
    }
}

return [
    'host' => backendEnvValue('DB_HOST', backendEnvValue('MYSQLHOST', '127.0.0.1')),
    'port' => (int) backendEnvValue('DB_PORT', backendEnvValue('MYSQLPORT', '3306')),
    'dbname' => backendEnvValue('DB_NAME', backendEnvValue('MYSQLDATABASE', 'beyond66')),
    'username' => backendEnvValue('DB_USER', backendEnvValue('MYSQLUSER', 'root')),
    'password' => backendEnvValue('DB_PASS', backendEnvValue('MYSQLPASSWORD', '')),
    'charset' => backendEnvValue('DB_CHARSET', 'utf8mb4'),
];

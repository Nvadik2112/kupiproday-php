<?php

namespace App\Database;

use PDO;

class DataBaseModule {
    private static ?PDO $connection = null;

    public static function getInstance(): PDO {
        if (self::$connection === null) {
            self::initializeConnection();
        }
        return self::$connection;
    }

    private static function initializeConnection(): void {
        $config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'username' => $_ENV['DB_USER'] ?? 'student',
            'password' => $_ENV['DB_PASSWORD'] ?? 'student',
            'database' => $_ENV['DB_NAME'] ?? 'nest_project',
            'schema' => $_ENV['DB_SCHEMA'] ?? 'kupipodariday'
        ];

        try {
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['Database']}";

            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Установка схемы
            self::$connection->exec("SET search_path TO {$config['schema']}");

        } catch (\PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }
}
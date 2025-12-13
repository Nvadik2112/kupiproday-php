<?php

namespace App\Database;

use PDO;

class DataBaseModule {
    private static ?PDO $connection = null;
    private static ?MigrationManager $migrationManager = null;

    public static function getInstance(): PDO {
        if (self::$connection === null) {
            self::initializeConnection();
        }
        return self::$connection;
    }

    public static function runMigrations(): void {
        if (self::$migrationManager === null) {
            $connection = self::getInstance();
            self::$migrationManager = new MigrationManager($connection);
        }

        self::$migrationManager->applyMigrations();
    }

    public static function getMigrationManager(): MigrationManager {
        if (self::$migrationManager === null) {
            $connection = self::getInstance();
            self::$migrationManager = new MigrationManager($connection);
        }

        return self::$migrationManager;
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
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            self::$connection->exec("SET search_path TO {$config['schema']}");

        } catch (\PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }
}
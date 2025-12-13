<?php

namespace App\Database;

use PDO;
use RuntimeException;

class MigrationManager
{
    private PDO $connection;
    private string $migrationsTable = 'migrations';
    private string $migrationsPath;

    public function __construct(PDO $connection, string $migrationsPath = null)
    {
        $this->connection = $connection;
        $this->migrationsPath = $migrationsPath ?? __DIR__ . '/../../../database/migrations/';
        $this->migrationsPath = rtrim($this->migrationsPath, '/') . '/';

        $this->createMigrationsTable();
    }

    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) UNIQUE NOT NULL,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->connection->exec($sql);
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->connection->query("SELECT migration FROM {$this->migrationsTable}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Находит все файлы миграций в директории, которые еще не были применены.
     * Важно, чтобы файлы были отсортированы по имени для правильного порядка выполнения.
     * @return array Массив путей к файлам (например, ['/path/0002_add_index.sql'])
     */
    private function getPendingMigrations(): array
    {
        $applied = $this->getAppliedMigrations();
        $allFiles = scandir($this->migrationsPath);
        $pending = [];

        foreach ($allFiles as $file) {
            // Ищем только SQL-файлы
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                // Если миграция еще не применена, добавляем в список ожидающих
                if (!in_array($file, $applied, true)) {
                    $pending[] = $this->migrationsPath . $file;
                }
            }
        }

        sort($pending);
        return $pending;
    }
    public function applyMigrations(): void
    {
        $pendingMigrations = $this->getPendingMigrations();

        if (empty($pendingMigrations)) {
            echo "База данных в актуальном состоянии. Новых миграций для применения нет.\n";
            return;
        }

        echo "Начинается применение " . count($pendingMigrations) . " миграции(й)...\n";

        $this->connection->beginTransaction();

        try {
            foreach ($pendingMigrations as $filePath) {
                $this->applyMigration($filePath);
            }
            $this->connection->commit();
            echo "Все миграции успешно применены.\n";
        } catch (\Exception $e) {
            // В случае ошибки откатываем все изменения этой сессии
            $this->connection->rollBack();
            throw new RuntimeException("Ошибка применения миграций: " . $e->getMessage(), 0, $e);
        }
    }
    private function applyMigration(string $filePath): void
    {
        $fileName = basename($filePath);
        $sql = file_get_contents($filePath);

        if ($sql === false) {
            throw new RuntimeException("Не удалось прочитать файл миграции: {$fileName}");
        }

        echo "Применяется миграция: {$fileName}\n";

        $this->connection->exec($sql);

        $stmt = $this->connection->prepare(
            "INSERT INTO {$this->migrationsTable} (migration) VALUES (:migration)"
        );
        $stmt->execute(['migration' => $fileName]);
    }
}
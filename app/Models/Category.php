<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }

    public static function exists(int $id): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }
}

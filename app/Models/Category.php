<?php

/*
 * Model: categorieën (tabel categories), bijv. Concert, Comedy, Workshop.
 */

function getCategories(): array
{
    return db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function categoryExists(int $id): bool
{
    $query = db()->prepare('SELECT id FROM categories WHERE id = ?');
    $query->execute([$id]);
    return $query->fetch() !== false;
}

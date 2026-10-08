<?php

// Model: categorieën (tabel categories)

function getCategories()
{
    return db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function categoryExists($id)
{
    $query = db()->prepare('SELECT id FROM categories WHERE id = ?');
    $query->execute([$id]);
    return $query->fetch() !== false;
}

<?php
namespace App\Models;

use App\Core\Database;

class CategoryModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllCategories() {
        return $this->pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll();
    }
}

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

    public function getCategoryBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    // ===== Phương thức dành cho Admin =====

    public function getCategoryById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategoriesWithSearch($search, $start, $limit) {
        $whereSql = '';
        $params   = [];
        if ($search !== '') {
            $whereSql = " WHERE name LIKE ? OR slug LIKE ?";
            $params   = ["%$search%", "%$search%"];
        }
        $sql  = "SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id) as article_count
                 FROM categories c{$whereSql} ORDER BY c.id DESC LIMIT " . (int)$start . ", " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countCategoriesWithSearch($search) {
        $whereSql = '';
        $params   = [];
        if ($search !== '') {
            $whereSql = " WHERE name LIKE ? OR slug LIKE ?";
            $params   = ["%$search%", "%$search%"];
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM categories" . $whereSql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function countArticlesByCategoryId($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    public function nameExists($name, $excludeId = null) {
        $sql    = "SELECT id FROM categories WHERE name = ?" . ($excludeId ? " AND id != ?" : "");
        $params = $excludeId ? [$name, $excludeId] : [$name];
        $stmt   = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function slugExists($slug, $excludeId = null) {
        $sql    = "SELECT id FROM categories WHERE slug = ?" . ($excludeId ? " AND id != ?" : "");
        $params = $excludeId ? [$slug, $excludeId] : [$slug];
        $stmt   = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function createCategory($name, $slug, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $slug, $description]);
    }

    public function updateCategory($id, $name, $slug, $description) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $description, $id]);
    }

    public function deleteCategory($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}


<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class ArticleModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getLatestPublished($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function buildFilterSql($search, $categoryId, $status, &$params) {
        $whereParts = [];
        if ($search !== '') {
            $whereParts[] = "(a.title LIKE ? OR a.summary LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($categoryId > 0) {
            $whereParts[] = "a.category_id = ?";
            $params[] = $categoryId;
        }
        if ($status === 'published' || $status === 'draft') {
            $whereParts[] = "a.status = ?";
            $params[] = $status;
        }
        return count($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';
    }

    public function getArticlesWithFilters($search, $categoryId, $status, $start, $limit) {
        $params = [];
        $whereSql = $this->buildFilterSql($search, $categoryId, $status, $params);
        
        $sql = "
            SELECT a.id, a.title, a.image, a.status, a.created_at, c.name AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            {$whereSql}
            ORDER BY a.created_at DESC
            LIMIT " . (int)$start . ", " . (int)$limit;
            
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countArticlesWithFilters($search, $categoryId, $status) {
        $params = [];
        $whereSql = $this->buildFilterSql($search, $categoryId, $status, $params);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM articles a LEFT JOIN categories c ON a.category_id = c.id" . $whereSql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getArticleById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createArticle($data) {
        $stmt = $this->pdo->prepare("INSERT INTO articles (category_id, author_id, title, slug, summary, content, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['category_id'], $data['author_id'], $data['title'], $data['slug'], 
            $data['summary'], $data['content'], $data['image'], $data['status']
        ]);
    }

    public function updateArticle($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE articles SET category_id=?, title=?, slug=?, summary=?, content=?, image=?, status=? WHERE id=?");
        return $stmt->execute([
            $data['category_id'], $data['title'], $data['slug'], 
            $data['summary'], $data['content'], $data['image'], $data['status'], $id
        ]);
    }

    public function deleteArticle($id) {
        $stmt = $this->pdo->prepare("DELETE FROM articles WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

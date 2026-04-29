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

    // ===== Phương thức dành cho trang công khai =====

    public function getArticlesByCategoryId($categoryId) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.username as author_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.category_id = ? AND a.status = 'published'
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public function getArticleBySlug($slug) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.slug = ? AND a.status = 'published'
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function incrementViews($articleId) {
        $this->pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$articleId]);
    }

    // ===== Phương thức quản lý bình luận =====

    public function getCommentsByArticleId($articleId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.article_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$articleId]);
        return $stmt->fetchAll();
    }

    public function addComment($articleId, $userId, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)");
        return $stmt->execute([$articleId, $userId, $content]);
    }

    public function updateComment($commentId, $userId, $content) {
        $stmt = $this->pdo->prepare("UPDATE comments SET content = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$content, $commentId, $userId]);
    }

    public function deleteComment($commentId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        return $stmt->execute([$commentId, $userId]);
    }

    // ===== Phương thức tìm kiếm =====

    public function searchArticles($query) {
        $words      = array_filter(array_map('trim', explode(' ', $query)));
        $whereParts = [];
        $params     = [];
        foreach ($words as $i => $word) {
            $whereParts[] = "(a.title LIKE :title$i OR a.summary LIKE :summary$i OR a.content LIKE :content$i)";
            $params["title$i"]   = "%$word%";
            $params["summary$i"] = "%$word%";
            $params["content$i"] = "%$word%";
        }
        $whereSql = count($whereParts) > 0 ? "AND " . implode(" AND ", $whereParts) : "";
        $sql = "
            SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published' $whereSql
            ORDER BY a.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ===== Phương thức dành cho Dashboard Admin =====

    public function countAll() {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    }

    public function getCategoryDistribution() {
        return $this->pdo->query("
            SELECT c.name, COUNT(a.id) as total
            FROM categories c
            LEFT JOIN articles a ON a.category_id = c.id
            GROUP BY c.id
        ")->fetchAll();
    }

    public function getDailyCount($table, $date) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        return (int)$stmt->fetchColumn();
    }

    public function getMonthlyCount($table, $start, $end) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table WHERE created_at BETWEEN ? AND ?");
        $stmt->execute([$start, $end]);
        return (int)$stmt->fetchColumn();
    }

    public function getTopArticles($limit = 5) {
        return $this->pdo->query("
            SELECT a.title, a.views, c.name as category_name, a.slug
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY a.views DESC
            LIMIT $limit
        ")->fetchAll();
    }
}



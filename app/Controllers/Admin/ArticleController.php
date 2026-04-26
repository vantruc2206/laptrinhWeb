<?php
namespace App\Controllers\Admin;

use App\Models\ArticleModel;
use App\Models\CategoryModel;

class ArticleController {
    private $articleModel;
    private $categoryModel;
    private $uploadDir = '../assets/images/articles/';

    public function __construct() {
        $this->articleModel = new ArticleModel();
        $this->categoryModel = new CategoryModel();
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function index() {
        $limit = 10;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filterCat = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $filterStatus = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

        $totalResults = $this->articleModel->countArticlesWithFilters($search, $filterCat, $filterStatus);
        $totalPages = max(1, (int)ceil($totalResults / $limit));
        if ($page > $totalPages) $page = $totalPages;
        $start = ($page - 1) * $limit;

        $articles = $this->articleModel->getArticlesWithFilters($search, $filterCat, $filterStatus, $start, $limit);
        $categories = $this->categoryModel->getAllCategories();

        $action = 'list';
        $error = '';
        $success = $this->getSuccessMessage();

        require_once __DIR__ . '/../../Views/admin/articles/index.php';
    }

    public function create() {
        $categories = $this->categoryModel->getAllCategories();
        $article = ['title' => '', 'category_id' => '', 'summary' => '', 'content' => '', 'image' => '', 'status' => 'published'];
        $action = 'create';
        $error = '';
        require_once __DIR__ . '/../../Views/admin/articles/form.php';
    }

    public function edit() {
        if (!isset($_GET['id'])) {
            $this->redirect('articles.php');
        }
        $categories = $this->categoryModel->getAllCategories();
        $article = $this->articleModel->getArticleById($_GET['id']);
        if (!$article) $this->redirect('articles.php');

        $action = 'edit';
        $error = '';
        require_once __DIR__ . '/../../Views/admin/articles/form.php';
    }

    public function store() {
        $this->handleSave('create');
    }

    public function update() {
        if (!isset($_GET['id'])) $this->redirect('articles.php');
        $this->handleSave('edit', $_GET['id']);
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $article = $this->articleModel->getArticleById($_GET['id']);
            if ($article && $article['image'] && file_exists($this->uploadDir . $article['image'])) {
                unlink($this->uploadDir . $article['image']);
            }
            if ($this->articleModel->deleteArticle($_GET['id'])) {
                $this->redirect('articles.php?success=deleted');
            }
        }
        $this->redirect('articles.php');
    }

    private function handleSave($action, $id = null) {
        // Sử dụng sanitize() nếu hàm đó khả dụng từ functions.php
        $title       = trim($_POST['title'] ?? '');
        $category_id = $_POST['category_id'] ?? '';
        $summary     = trim($_POST['summary'] ?? '');
        $content     = $_POST['content'] ?? '';
        $status      = $_POST['status'] ?? 'published';
        
        // Tạo slug đơn giản (trong hệ thống thực nên dùng hàm generateSlug() từ functions.php)
        $slug = function_exists('generateSlug') ? generateSlug($title) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        if (empty($title) || empty($category_id) || empty($summary) || empty($content)) {
            $error = "Vui lòng điền đầy đủ các trường bắt buộc.";
            $categories = $this->categoryModel->getAllCategories();
            $article = $_POST;
            $article['image'] = $_POST['current_image'] ?? '';
            require_once __DIR__ . '/../../Views/admin/articles/form.php';
            return;
        }

        $imageName = $_POST['current_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $imageName = time() . '_' . uniqid() . '.' . $ext;
                if (!move_uploaded_file($tmpName, $this->uploadDir . $imageName)) {
                    $error = "Tải ảnh lên thất bại.";
                } elseif (isset($_POST['current_image']) && file_exists($this->uploadDir . $_POST['current_image'])) {
                    unlink($this->uploadDir . $_POST['current_image']);
                }
            } else {
                $error = "Định dạng ảnh không hợp lệ.";
            }
        }

        if (!isset($error)) {
            $data = [
                'category_id' => $category_id,
                'author_id' => $_SESSION['user_id'] ?? 1,
                'title' => $title,
                'slug' => $slug,
                'summary' => $summary,
                'content' => $content,
                'image' => $imageName,
                'status' => $status
            ];
            if ($action == 'create') {
                $this->articleModel->createArticle($data);
                $this->redirect('articles.php?success=created');
            } else {
                $this->articleModel->updateArticle($id, $data);
                $this->redirect('articles.php?success=updated');
            }
        } else {
            $categories = $this->categoryModel->getAllCategories();
            $article = $_POST;
            require_once __DIR__ . '/../../Views/admin/articles/form.php';
        }
    }

    private function getSuccessMessage() {
        if (isset($_GET['success'])) {
            $msgs = ['created' => 'Tạo bài viết thành công.', 'updated' => 'Cập nhật bài viết thành công.', 'deleted' => 'Đã xóa bài viết.'];
            return $msgs[$_GET['success']] ?? '';
        }
        return '';
    }

    private function redirect($url) {
        header("Location: $url");
        exit;
    }
}

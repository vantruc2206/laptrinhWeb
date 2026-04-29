<?php
namespace App\Controllers\Admin;

use App\Models\CategoryModel;

class CategoryController {
    private $categoryModel;
    private $limit = 10;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }

    public function index() {
        $action = $_GET['action'] ?? 'list';
        $error  = '';
        $success = '';

        // Xử lý xóa
        if ($action === 'delete' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            if ($this->categoryModel->countArticlesByCategoryId($id) > 0) {
                $error = "Không thể xóa danh mục này vì đang có bài viết thuộc danh mục!";
            } elseif ($this->categoryModel->deleteCategory($id)) {
                $this->redirect('categories.php?success=deleted');
            } else {
                $error = "Không thể xóa danh mục.";
            }
        }

        // Xử lý POST (tạo / sửa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handleSave($action);
            if ($result !== true) {
                $error = $result;
            }
        }

        // Thông báo thành công
        if (isset($_GET['success'])) {
            $msgs    = ['created' => 'Tạo thành công.', 'updated' => 'Cập nhật thành công.', 'deleted' => 'Đã xóa.'];
            $success = $msgs[$_GET['success']] ?? '';
        }

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $total  = $this->categoryModel->countCategoriesWithSearch($search);
        $totalPages = max(1, (int)ceil($total / $this->limit));
        if ($page > $totalPages) $page = $totalPages;
        $start = ($page - 1) * $this->limit;

        $categories  = $this->categoryModel->getCategoriesWithSearch($search, $start, $this->limit);
        $totalResults = $total;
        $category    = ['name' => '', 'description' => ''];

        if ($action === 'edit' && isset($_GET['id'])) {
            $category = $this->categoryModel->getCategoryById((int)$_GET['id']);
            if (!$category) $this->redirect('categories.php');
        }

        require_once __DIR__ . '/../../Views/admin/categories/index.php';
    }

    private function handleSave($action) {
        $name        = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $id          = isset($_POST['id']) ? (int)$_POST['id'] : null;

        if (empty($name)) return "Tên danh mục không được để trống!";

        if ($this->categoryModel->nameExists($name, $id)) {
            return "Tên danh mục '$name' đã tồn tại! Vui lòng chọn tên khác.";
        }

        $slug = generateSlug($name);

        // Đảm bảo slug là duy nhất
        if ($this->categoryModel->slugExists($slug, $id)) {
            $base    = $slug;
            $counter = 2;
            do {
                $slug = $base . '-' . $counter++;
            } while ($this->categoryModel->slugExists($slug, $id));
        }

        if ($action === 'create') {
            if ($this->categoryModel->createCategory($name, $slug, $description)) {
                $this->redirect('categories.php?success=created');
            }
            return "Không thể tạo danh mục. Vui lòng thử lại.";
        }

        if ($action === 'edit' && $id) {
            if ($this->categoryModel->updateCategory($id, $name, $slug, $description)) {
                $this->redirect('categories.php?success=updated');
            }
            return "Không thể cập nhật danh mục.";
        }

        return true;
    }

    private function redirect($url) {
        header("Location: $url");
        exit;
    }
}

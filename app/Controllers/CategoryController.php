<?php
namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ArticleModel;

class CategoryController {
    private $categoryModel;
    private $articleModel;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
        $this->articleModel  = new ArticleModel();
    }

    public function index() {
        $slug = $_GET['slug'] ?? '';
        if (!$slug) {
            redirect('index.php');
        }

        $category = $this->categoryModel->getCategoryBySlug($slug);
        if (!$category) {
            require_once __DIR__ . '/../Views/public/layouts/header.php';
            echo "<div class='container' style='text-align:center; padding: 4rem 0;'><h2>Không tìm thấy danh mục.</h2></div>";
            require_once __DIR__ . '/../Views/public/layouts/footer.php';
            exit;
        }

        $articles   = $this->articleModel->getArticlesByCategoryId($category['id']);
        $uploadPath = 'assets/images/articles/';

        require_once __DIR__ . '/../Views/public/category.php';
    }
}

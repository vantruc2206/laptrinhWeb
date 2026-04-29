<?php
namespace App\Controllers;

use App\Models\ArticleModel;

class SearchController {
    private $articleModel;

    public function __construct() {
        $this->articleModel = new ArticleModel();
    }

    public function index() {
        $query = isset($_GET['search']) ? trim($_GET['search']) : '';

        if (empty($query)) {
            require_once __DIR__ . '/../Views/public/layouts/header.php';
            echo "<div class='container' style='text-align:center; padding: 4rem 0;'><h2>Vui lòng nhập từ khóa tìm kiếm!</h2></div>";
            require_once __DIR__ . '/../Views/public/layouts/footer.php';
            exit;
        }

        $articles   = $this->articleModel->searchArticles($query);
        $uploadPath = 'assets/images/articles/';

        require_once __DIR__ . '/../Views/public/search.php';
    }
}

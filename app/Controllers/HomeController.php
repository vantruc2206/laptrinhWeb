<?php
namespace App\Controllers;

use App\Models\ArticleModel;

class HomeController {
    public function index() {
        $articleModel = new ArticleModel();
        $articles = $articleModel->getLatestPublished(10);
        
        $featured = !empty($articles) ? $articles[0] : null;
        $regularArticles = !empty($articles) ? array_slice($articles, 1) : [];
        $uploadPath = 'assets/images/articles/';

        // Gọi View để hiển thị giao diện
        require_once __DIR__ . '/../Views/public/home.php';
    }
}

<?php
namespace App\Controllers\Admin;

use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\UserModel;

class DashboardController {
    private $articleModel;
    private $categoryModel;
    private $userModel;

    public function __construct() {
        $this->articleModel  = new ArticleModel();
        $this->categoryModel = new CategoryModel();
        $this->userModel     = new UserModel();
    }

    public function index() {
        $userCount     = $this->userModel->countAll();
        $categoryCount = (int)$this->categoryModel->countCategoriesWithSearch('');
        $articleCount  = $this->articleModel->countAll();

        // Dữ liệu 7 ngày gần nhất
        $days          = [];
        $articleGrowth = [];
        $userGrowth    = [];
        for ($i = 6; $i >= 0; $i--) {
            $date            = date('Y-m-d', strtotime("-$i days"));
            $days[]          = date('d/m', strtotime($date));
            $articleGrowth[] = $this->articleModel->getDailyCount('articles', $date);
            $userGrowth[]    = $this->userModel->getDailyCount($date);
        }

        // Dữ liệu 6 tháng gần nhất
        $months         = [];
        $articleMonthly = [];
        $userMonthly    = [];
        for ($i = 5; $i >= 0; $i--) {
            $startMonth       = date('Y-m-01', strtotime("-$i months"));
            $endMonth         = date('Y-m-t', strtotime($startMonth));
            $months[]         = date('M', strtotime($startMonth));
            $articleMonthly[] = $this->articleModel->getMonthlyCount('articles', $startMonth, $endMonth);
            $userMonthly[]    = $this->userModel->getMonthlyCount($startMonth, $endMonth);
        }

        // Tính % tăng trưởng
        $growthArticles = 0;
        $growthUsers    = 0;
        $lastIndex      = count($articleMonthly) - 1;
        $prevIndex      = $lastIndex - 1;
        if ($prevIndex >= 0) {
            if ($articleMonthly[$prevIndex] > 0) {
                $growthArticles = (($articleMonthly[$lastIndex] - $articleMonthly[$prevIndex]) / $articleMonthly[$prevIndex]) * 100;
            }
            if ($userMonthly[$prevIndex] > 0) {
                $growthUsers = (($userMonthly[$lastIndex] - $userMonthly[$prevIndex]) / $userMonthly[$prevIndex]) * 100;
            }
        }

        // Phân bố danh mục và top bài viết
        $distribution   = $this->articleModel->getCategoryDistribution();
        $categoryLabels = array_column($distribution, 'name');
        $categoryCounts = array_map('intval', array_column($distribution, 'total'));
        $topArticles    = $this->articleModel->getTopArticles(5);

        require_once __DIR__ . '/../../Views/admin/dashboard.php';
    }
}

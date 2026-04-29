<?php
namespace App\Controllers;

use App\Models\ArticleModel;

class ArticleController {
    private $articleModel;

    public function __construct() {
        $this->articleModel = new ArticleModel();
    }

    public function show() {
        $slug = $_GET['slug'] ?? '';
        if (!$slug) {
            redirect('index.php');
        }

        $article = $this->articleModel->getArticleBySlug($slug);
        if (!$article) {
            require_once __DIR__ . '/../Views/public/layouts/header.php';
            echo "<div class='container' style='text-align:center; padding: 4rem 0;'><h2>Bài viết không tồn tại hoặc đã bị ẩn.</h2></div>";
            require_once __DIR__ . '/../Views/public/layouts/footer.php';
            exit;
        }

        // Tăng lượt xem
        $this->articleModel->incrementViews($article['id']);

        // Xử lý POST request cho bình luận
        $commentSuccess = '';
        $commentError   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if (isLoggedIn()) {
                $commentId = (int)($_POST['comment_id'] ?? 0);
                $userId    = $_SESSION['user_id'];

                if ($_POST['action'] === 'delete_comment') {
                    $this->articleModel->deleteComment($commentId, $userId);
                    redirect("article.php?slug=" . urlencode($slug) . "&success=deleted");
                } elseif ($_POST['action'] === 'update_comment') {
                    $newContent = sanitize($_POST['content'] ?? '');
                    if (!empty($newContent)) {
                        $this->articleModel->updateComment($commentId, $userId, $newContent);
                        redirect("article.php?slug=" . urlencode($slug) . "&success=updated");
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
            if (!isLoggedIn()) {
                $commentError = "Bạn cần đăng nhập để bình luận.";
            } else {
                $content = sanitize($_POST['content'] ?? '');
                if (empty($content)) {
                    $commentError = "Nội dung bình luận không được để trống.";
                } else {
                    if ($this->articleModel->addComment($article['id'], $_SESSION['user_id'], $content)) {
                        $commentSuccess = "Bình luận đã được gửi!";
                    } else {
                        $commentError = "Có lỗi xảy ra, vui lòng thử lại.";
                    }
                }
            }
        }

        // Xử lý thông báo từ redirect
        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'deleted') $commentSuccess = "Đã xóa bình luận thành công.";
            if ($_GET['success'] === 'updated') $commentSuccess = "Đã cập nhật bình luận thành công.";
        }

        $comments   = $this->articleModel->getCommentsByArticleId($article['id']);
        $uploadPath = 'assets/images/articles/';

        $protocol   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
        $currentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        require_once __DIR__ . '/../Views/public/article.php';
    }
}

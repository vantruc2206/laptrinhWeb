<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

// Hàm làm sạch dữ liệu đầu vào
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Kiểm tra xem người dùng đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Kiểm tra xem người dùng có phải là admin không
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Hàm chuyển hướng
function redirect($url) {
    header("Location: $url");
    exit();
}

// Tạo slug cho bài viết và danh mục
function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}
?>

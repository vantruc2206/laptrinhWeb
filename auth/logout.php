<?php
require_once '../includes/functions.php';

// Kiểm tra xem có phải Admin đang đăng xuất không trước khi hủy session
$wasAdmin = isset($_SESSION['is_admin_login']);

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Điều hướng thông minh dựa trên vai trò vừa đăng xuất
if ($wasAdmin) {
    redirect('../admin/login.php');
} else {
    redirect('../index.php');
}
?>

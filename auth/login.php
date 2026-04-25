<?php
require_once '../includes/functions.php';

isLoggedIn() && redirect('../index.php');
$error = '';

$redirect = '../index.php';
if (isset($_GET['redirect'])) {
    $r = $_GET['redirect'];
    // Chỉ cho phép redirect đến URL trong cùng domain (bắt đầu bằng http://localhost hoặc https://localhost)
    if (strpos($r, 'http') === 0) {
        $redirect = $r;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    // Lấy redirect từ hidden field, không sanitize vì cần giữ nguyên URL
    $postRedirect = $_POST['redirect'] ?? '';
    if (!empty($postRedirect) && strpos($postRedirect, 'http') === 0) {
        $redirect = $postRedirect;
    }

    if (empty($username) || empty($password)) {
        $error = "Vui lòng điền đầy đủ các trường.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            redirect($redirect);
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không hợp lệ.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Website Tin tức</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Chào mừng trở lại</h2>
                <p>Đăng nhập vào tài khoản của bạn</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>
            <div class="auth-footer">
                <p>Chưa có tài khoản? <a href="register.php?redirect=<?= urlencode($redirect) ?>">Đăng ký tại đây</a></p>
                <p><a href="../index.php">Quay lại Trang chủ</a></p>
            </div>
        </div>
    </div>
</body>
</html>

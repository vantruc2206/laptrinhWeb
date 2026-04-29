<?php
require_once __DIR__ . '/../app/Helpers/functions.php';

// Nếu đã đăng nhập admin rồi thì vào thẳng index
if (isAdmin() && isset($_SESSION['is_admin_login'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_admin_login'] = true; // Đánh dấu đây là phiên đăng nhập Admin
                
                redirect('index.php');
            } else {
                $error = "Tài khoản của bạn không có quyền truy cập khu vực này.";
            }
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - Website Tin tức</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body.admin-login-body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
        }
        .admin-login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .admin-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .admin-login-header h2 {
            color: #111827;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="admin-login-body">
    <div class="admin-login-card">
        <div class="admin-login-header">
            <h2>Quản trị Hệ thống</h2>
            <p style="color: #6b7280;">Vui lòng đăng nhập để tiếp tục</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group" style="margin-top: 1rem;">
                <label>Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 0.75rem;">
                Đăng nhập Admin
            </button>
        </form>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="../index.php" style="color: #6b7280; font-size: 0.875rem; text-decoration: none;">&larr; Quay lại Trang chủ</a>
        </div>
    </div>
</body>
</html>

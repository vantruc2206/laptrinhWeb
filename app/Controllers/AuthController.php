<?php
namespace App\Controllers;

use App\Models\UserModel;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login() {
        isLoggedIn() && redirect('../index.php');

        $error    = '';
        $redirect = '../index.php';

        if (isset($_GET['redirect'])) {
            $r = $_GET['redirect'];
            if (strpos($r, 'http') === 0) $redirect = $r;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $postRedirect = $_POST['redirect'] ?? '';
            if (!empty($postRedirect) && strpos($postRedirect, 'http') === 0) {
                $redirect = $postRedirect;
            }

            if (empty($username) || empty($password)) {
                $error = "Vui lòng điền đầy đủ các trường.";
            } else {
                $user = $this->userModel->findByUsername($username);
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['username']  = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    redirect($redirect);
                } else {
                    $error = "Tên đăng nhập hoặc mật khẩu không hợp lệ.";
                }
            }
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function register() {
        isLoggedIn() && redirect('../index.php');

        $error    = '';
        $success  = '';
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../index.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username         = sanitize($_POST['username'] ?? '');
            $email            = sanitize($_POST['email'] ?? '');
            $password         = $_POST['password'] ?? '';
            $confirmPassword  = $_POST['confirm_password'] ?? '';
            $redirect         = $_POST['redirect'] ?? '../index.php';

            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = "Vui lòng điền đầy đủ các trường.";
            } elseif ($password !== $confirmPassword) {
                $error = "Mật khẩu không khớp.";
            } elseif ($this->userModel->usernameOrEmailExists($username, $email)) {
                $error = "Tên đăng nhập hoặc Email đã tồn tại.";
            } elseif ($this->userModel->registerUser($username, $email, $password)) {
                $success = "Đăng ký thành công! Bạn hiện có thể <a href='login.php?redirect=" . urlencode($redirect) . "'>đăng nhập</a>.";
            } else {
                $error = "Đã có lỗi xảy ra. Vui lòng thử lại.";
            }
        }

        require_once __DIR__ . '/../Views/auth/register.php';
    }
}

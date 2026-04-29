<?php
namespace App\Controllers\Admin;

use App\Models\UserModel;

class UserController {
    private $userModel;
    private $limit = 10;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function index() {
        $action  = $_GET['action'] ?? 'list';
        $error   = '';
        $success = '';

        // Xử lý xóa
        if ($action === 'delete' && isset($_GET['id']) && (int)$_GET['id'] !== (int)$_SESSION['user_id']) {
            if ($this->userModel->deleteUser((int)$_GET['id'])) {
                $this->redirect('users.php?success=deleted');
            }
        }

        // Xử lý POST (tạo / sửa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handleSave($action);
            if ($result !== true) {
                $error = $result;
            }
        }

        // Thông báo thành công
        if (isset($_GET['success'])) {
            $msgs    = ['created' => 'Tạo người dùng thành công.', 'updated' => 'Cập nhật người dùng thành công.', 'deleted' => 'Đã xóa người dùng.'];
            $success = $msgs[$_GET['success']] ?? '';
        }

        $page       = max(1, (int)($_GET['page'] ?? 1));
        $totalUsers = $this->userModel->countAll();
        $totalPages = max(1, (int)ceil($totalUsers / $this->limit));
        if ($page > $totalPages) $page = $totalPages;
        $start = ($page - 1) * $this->limit;

        $users = $this->userModel->getAllUsers($start, $this->limit);
        $user  = ['username' => '', 'email' => '', 'role' => 'user'];

        if ($action === 'edit' && isset($_GET['id'])) {
            $user = $this->userModel->getUserById((int)$_GET['id']);
            if (!$user) $this->redirect('users.php');
        }

        require_once __DIR__ . '/../../Views/admin/users/index.php';
    }

    private function handleSave($action) {
        $username = sanitize($_POST['username'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $role     = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'user';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email)) return "Tên đăng nhập và Email là bắt buộc.";

        if ($action === 'create') {
            if (empty($password)) return "Mật khẩu là bắt buộc khi tạo người dùng mới.";
            if ($this->userModel->createUser($username, $email, $password, $role)) {
                $this->redirect('users.php?success=created');
            }
            return "Không thể tạo người dùng. Tên đăng nhập hoặc Email có thể đã tồn tại.";
        }

        if ($action === 'edit' && isset($_GET['id'])) {
            $pw = !empty($password) ? $password : null;
            if ($this->userModel->updateUser((int)$_GET['id'], $username, $email, $role, $pw)) {
                $this->redirect('users.php?success=updated');
            }
            return "Không thể cập nhật người dùng.";
        }

        return true;
    }

    private function redirect($url) {
        header("Location: $url");
        exit;
    }
}

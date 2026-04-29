<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../app/Helpers/functions.php';

if (!isAdmin() || !isset($_SESSION['is_admin_login'])) {
    redirect('../auth/login.php');
}

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/UserModel.php';
require_once __DIR__ . '/../app/Controllers/Admin/UserController.php';

use App\Controllers\Admin\UserController;

$controller = new UserController();
$controller->index();

<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/../app/Helpers/functions.php';


if (!isAdmin() || !isset($_SESSION['is_admin_login'])) {
    redirect('../auth/login.php');
}


require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/ArticleModel.php';
require_once __DIR__ . '/../app/Models/CategoryModel.php';
require_once __DIR__ . '/../app/Controllers/Admin/ArticleController.php';

use App\Controllers\Admin\ArticleController;

$controller = new ArticleController();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $controller->store();
    } elseif ($action === 'edit') {
        $controller->update();
    }
} else {
    if ($action === 'list') {
        $controller->index();
    } elseif ($action === 'create') {
        $controller->create();
    } elseif ($action === 'edit') {
        $controller->edit();
    } elseif ($action === 'delete') {
        $controller->delete();
    } else {
        $controller->index();
    }
}

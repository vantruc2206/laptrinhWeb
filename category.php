<?php
require_once __DIR__ . '/app/Helpers/functions.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Models/CategoryModel.php';
require_once __DIR__ . '/app/Models/ArticleModel.php';
require_once __DIR__ . '/app/Controllers/CategoryController.php';

use App\Controllers\CategoryController;

$controller = new CategoryController();
$controller->index();

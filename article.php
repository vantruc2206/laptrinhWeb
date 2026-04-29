<?php
require_once __DIR__ . '/app/Helpers/functions.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Models/ArticleModel.php';
require_once __DIR__ . '/app/Controllers/ArticleController.php';

use App\Controllers\ArticleController;

$controller = new ArticleController();
$controller->show();

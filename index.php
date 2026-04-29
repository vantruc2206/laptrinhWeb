<?php
require_once __DIR__ . '/app/Helpers/functions.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Models/ArticleModel.php';
require_once __DIR__ . '/app/Controllers/HomeController.php';

use App\Controllers\HomeController;

$controller = new HomeController();
$controller->index();

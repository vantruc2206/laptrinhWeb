<?php
require_once __DIR__ . '/app/Helpers/functions.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Models/ArticleModel.php';
require_once __DIR__ . '/app/Controllers/SearchController.php';

use App\Controllers\SearchController;

$controller = new SearchController();
$controller->index();

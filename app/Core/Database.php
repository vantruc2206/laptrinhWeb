<?php
namespace App\Core;

class Database {
    public static function getConnection() {
        global $pdo;
        if (!isset($pdo)) {
            // Bao gồm config.php hiện tại để tái sử dụng $pdo
            require_once __DIR__ . '/../../includes/config.php';
        }
        return $pdo;
    }
}

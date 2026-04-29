<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class UserModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // ===== Phương thức xác thực người dùng =====

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function usernameOrEmailExists($username, $email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetchColumn() > 0;
    }

    public function registerUser($username, $email, $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt   = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        return $stmt->execute([$username, $email, $hashed]);
    }

    // ===== Phương thức dành cho Admin =====

    public function countAll() {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function getAllUsers($start, $limit) {
        $stmt = $this->pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT :start, :limit");
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($username, $email, $password, $role) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt   = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed, $role]);
    }

    public function updateUser($id, $username, $email, $role, $password = null) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $this->pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
            return $stmt->execute([$username, $email, $role, $hashed, $id]);
        }
        $stmt = $this->pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $role, $id]);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Dành cho Dashboard
    public function getDailyCount($date) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        return (int)$stmt->fetchColumn();
    }

    public function getMonthlyCount($start, $end) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at BETWEEN ? AND ?");
        $stmt->execute([$start, $end]);
        return (int)$stmt->fetchColumn();
    }
}

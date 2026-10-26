<?php
session_start();

class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.status = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['last_regen'] = time();
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function checkAccess($requiredRole = null) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        if ($requiredRole && $_SESSION['role'] !== $requiredRole && $_SESSION['role'] !== 'Admin') {
            die("Acceso denegado: No tienes permisos suficientes.");
        }
    }

    public function regenerateSession() {
        if (!isset($_SESSION['last_regen']) || time() - $_SESSION['last_regen'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regen'] = time();
        }
    }
}
?>

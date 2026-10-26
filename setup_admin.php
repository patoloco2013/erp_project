<?php
require_once 'config/database.php';

try {
    // 1. Insertar Roles
    $roles = ['Admin', 'Manager', 'Staff'];
    $stmtRole = $pdo->prepare("INSERT IGNORE INTO roles (name) VALUES (?)");
    foreach ($roles as $role) {
        $stmtRole->execute([$role]);
    }

    // 2. Crear Empresa Default
    $stmtCompany = $pdo->prepare("INSERT INTO companies (name, tax_id, address) VALUES (?, ?, ?)");
    $stmtCompany->execute(['Empresa Principal', '123456789', 'Dirección Central']);
    $companyId = $pdo->lastInsertId();

    // 3. Crear Usuario Admin
    $username = 'admin';
    $password = 'admin123'; // Contraseña genérica
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $roleId = 1; // ID de Admin

    $stmtUser = $pdo->prepare("INSERT INTO users (company_id, role_id, username, password, full_name, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtUser->execute([$companyId, $roleId, $username, $hashedPassword, 'Administrador Sistema', 'admin@erp.com']);

    echo "Base de datos inicializada con éxito.\n";
    echo "Usuario: admin\n";
    echo "Contraseña: admin123\n";
    echo "Empresa creada con ID: $companyId\n";

} catch (PDOException $e) {
    die("Error al inicializar: " . $e->getMessage());
}
?>

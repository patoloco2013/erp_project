<?php
require_once '../config/database.php';
require_once '../modules/auth/Auth.php';

$auth = new Auth($pdo);
$auth->checkAccess();

$company_id = $_SESSION['company_id'];

// Estadísticas rápidas para el dashboard
$stmtProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE company_id = ?");
$stmtProducts->execute([$company_id]);
$totalProducts = $stmtProducts->fetchColumn();

$stmtSales = $pdo->prepare("SELECT SUM(total) FROM sales WHERE company_id = ? AND status = 'completed'");
$stmtSales->execute([$company_id]);
$totalSales = $stmtSales->fetchColumn() ?? 0;

$stmtClients = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE company_id = ?");
$stmtClients->execute([$company_id]);
$totalClients = $stmtClients->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ERP Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>ERP Sistema</h2>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="../modules/inventory/products.php">📦 Inventario</a>
            <a href="../modules/inventory/entities.php">👥 Clientes/Prov.</a>
            <a href="../modules/sales/sales.php">💰 Ventas</a>
            <?php if($_SESSION['role'] === 'Admin'): ?>
                <a href="../modules/auth/audit_view.php">📜 Auditoría</a>
            <?php endif; ?>
            <a href="../modules/auth/logout.php" style="color: #e74c3c;">🚪 Salir</a>
        </div>

        <div class="main-content">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <p>Empresa: <strong><?= htmlspecialchars($_SESSION['company_id']) ?></strong> | Rol: <?= $_SESSION['role'] ?></p>

            <div class="card-grid">
                <div class="card">
                    <h3>Productos</h3>
                    <p><?= $totalProducts ?></p>
                </div>
                <div class="card">
                    <h3>Ventas Totales</h3>
                    <p>$<?= number_format($totalSales, 2) ?></p>
                </div>
                <div class="card">
                    <h3>Clientes</h3>
                    <p><?= $totalClients ?></p>
                </div>
            </div>

            <h2>Resumen Reciente</h2>
            <p>Selecciona un módulo en el menú lateral para comenzar a gestionar tu negocio.</p>
        </div>
    </div>
</body>
</html>

<?php
require_once '../../config/database.php';
require_once '../auth/Auth.php';
require_once '../../includes/Auditor.php';

$auth = new Auth($pdo);
$auth->checkAccess('Admin'); // Solo Administradores pueden ver la auditoría

$auditor = new Auditor($pdo);
$company_id = $_SESSION['company_id'];
$logs = $auditor->getLogs($company_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Auditoría del Sistema</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <h1>Log de Auditoría</h1>
    <p>Registro detallado de todas las acciones realizadas en la empresa.</p>

    <table border="1">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Tabla</th>
                <th>Registro ID</th>
                <th>Valor Anterior</th>
                <th>Valor Nuevo</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
                <tr>
                    <td><?= $log['created_at'] ?></td>
                    <td><?= htmlspecialchars($log['username']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['table_name']) ?></td>
                    <td><?= $log['record_id'] ?></td>
                    <td><small><?= htmlspecialchars($log['old_value']) ?></small></td>
                    <td><small><?= htmlspecialchars($log['new_value']) ?></small></td>
                    <td><?= $log['ip_address'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

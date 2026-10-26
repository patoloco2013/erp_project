<?php
require_once '../../config/database.php';
require_once 'EntityManager.php';
require_once '../auth/Auth.php';

$auth = new Auth($pdo);
$auth->checkAccess(); // Solo usuarios logueados

$em = new EntityManager($pdo);
$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['type'] === 'client') {
        $em->createClient($company_id, $_POST);
    } elseif ($_POST['type'] === 'supplier') {
        $em->createSupplier($company_id, $_POST);
    }
    header("Location: entities.php");
    exit();
}

$clients = $em->getClients($company_id);
$suppliers = $em->getSuppliers($company_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Entidades</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <h1>Gestión de Clientes y Proveedores</h1>
    
    <h3>Agregar Nueva Entidad</h3>
    <form method="POST">
        <select name="type">
            <option value="client">Cliente</option>
            <option value="supplier">Proveedor</option>
        </select>
        <input type="text" name="name" placeholder="Nombre" required>
        <input type="text" name="tax_id" placeholder="RUC/NIT/Tax ID">
        <input type="email" name="email" placeholder="Email">
        <input type="text" name="phone" placeholder="Teléfono">
        <textarea name="address" placeholder="Dirección"></textarea>
        <button type="submit">Guardar</button>
    </form>

    <hr>

    <h3>Clientes</h3>
    <table border="1">
        <tr><th>Nombre</th><th>Tax ID</th><th>Email</th></tr>
        <?php foreach($clients as $c): ?>
            <tr><td><?= $c['name'] ?></td><td><?= $c['tax_id'] ?></td><td><?= $c['email'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>Proveedores</h3>
    <table border="1">
        <tr><th>Nombre</th><th>Tax ID</th><th>Email</th></tr>
        <?php foreach($suppliers as $s): ?>
            <tr><td><?= $s['name'] ?></td><td><?= $s['tax_id'] ?></td><td><?= $s['email'] ?></td></tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

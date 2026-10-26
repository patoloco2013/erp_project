<?php
require_once '../../config/database.php';
require_once '../inventory/InventoryManager.php';
require_once 'TransactionManager.php';
require_once '../auth/Auth.php';

$auth = new Auth($pdo);
$auth->checkAccess();

$im = new InventoryManager($pdo);
$tm = new TransactionManager($pdo, $im);
$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_sale') {
            // Simplificado: Un solo producto por venta para el ejemplo
            $items = [[
                'product_id' => $_POST['product_id'],
                'quantity' => $_POST['quantity'],
                'price' => $_POST['price'],
                'subtotal' => $_POST['quantity'] * $_POST['price']
            ]];
            $tm->createSale($company_id, $user_id, $_POST['client_id'], $items);
        } elseif ($_POST['action'] === 'cancel_sale') {
            $tm->cancelSale($_POST['sale_id'], $company_id, $user_id);
        }
    }
    header("Location: sales.php");
    exit();
}

$sales = $pdo->prepare("SELECT s.*, c.name as client_name FROM sales s JOIN clients c ON s.client_id = c.id WHERE s.company_id = ?");
$sales->execute([$company_id]);
$all_sales = $sales->fetchAll();

$clients = $pdo->prepare("SELECT * FROM clients WHERE company_id = ?");
$clients->execute([$company_id]);
$all_clients = $clients->fetchAll();

$products = $pdo->prepare("SELECT * FROM products WHERE company_id = ?");
$products->execute([$company_id]);
$all_products = $products->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ventas</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <h1>Gestión de Ventas</h1>

    <div class="sale-form">
        <h3>Nueva Venta (Simplificada)</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create_sale">
            <select name="client_id" required>
                <option value="">Seleccione Cliente</option>
                <?php foreach($all_clients as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="product_id" required>
                <option value="">Seleccione Producto</option>
                <?php foreach($all_products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?> (Stock: <?= $p['stock_current'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity" placeholder="Cant." required>
            <input type="number" step="0.01" name="price" placeholder="Precio" required>
            <button type="submit">Registrar Venta</button>
        </form>
    </div>

    <hr>

    <h3>Historial de Ventas</h3>
    <table border="1">
        <tr>
            <th>ID</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Acciones</th>
        </tr>
        <?php foreach($all_sales as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= $s['client_name'] ?></td>
                <td>$<?= number_format($s['total'], 2) ?></td>
                <td><?= $s['status'] ?></td>
                <td>
                    <?php if($s['status'] === 'completed'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="cancel_sale">
                            <input type="hidden" name="sale_id" value="<?= $s['id'] ?>">
                            <button type="submit">Cancelar/Devolver</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

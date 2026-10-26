<?php
require_once '../../config/database.php';
require_once 'InventoryManager.php';
require_once '../auth/Auth.php';

$auth = new Auth($pdo);
$auth->checkAccess();

$im = new InventoryManager($pdo);
$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_product') {
            $im->createProduct($company_id, $_POST);
        } elseif ($_POST['action'] === 'adjust_stock') {
            // Ajuste manual de stock
            $im->moveStock($company_id, $_POST['product_id'], $_POST['quantity'], 'adjustment', 0, $user_id);
        }
    }
    header("Location: products.php");
    exit();
}

$products = $im->getProducts($company_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventario de Productos</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <h1>Inventario de Productos</h1>
    
    <div class="product-form">
        <h3>Nuevo Producto</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add_product">
            <input type="text" name="sku" placeholder="SKU" required>
            <input type="text" name="name" placeholder="Nombre" required>
            <input type="text" name="description" placeholder="Descripción">
            <input type="number" step="0.01" name="price_buy" placeholder="Precio Compra" required>
            <input type="number" step="0.01" name="price_sell" placeholder="Precio Venta" required>
            <input type="number" name="stock_min" placeholder="Stock Mínimo">
            <button type="submit">Crear Producto</button>
        </form>
    </div>

    <hr>

    <h3>Lista de Productos</h3>
    <table border="1">
        <tr>
            <th>SKU</th><th>Nombre</th><th>Stock Actual</th><th>Precio Venta</th><th>Acciones</th>
        </tr>
        <?php foreach($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['sku']) ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td style="<?= $p['stock_current'] <= $p['stock_min'] ? 'color:red;font-weight:bold' : '' ?>">
                    <?= $p['stock_current'] ?>
                </td>
                <td>$<?= number_format($p['price_sell'], 2) ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="adjust_stock">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="number" name="quantity" placeholder="Cant." style="width:60px" required>
                        <button type="submit">Ajustar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

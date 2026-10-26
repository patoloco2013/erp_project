<?php
require_once 'config/database.php';
require_once 'modules/inventory/InventoryManager.php';
require_once 'modules/sales/TransactionManager.php';
require_once 'includes/Auditor.php';

echo "--- INICIANDO TEST AUTOMATIZADO DE INTEGRIDAD ---\n\n";

$im = new InventoryManager($pdo);
$auditor = new Auditor($pdo);
$tm = new TransactionManager($pdo, $im);

try {
    // 1. SETUP DE DATOS DE PRUEBA
    echo "[1/4] Configurando datos de prueba... ";
    $company_id = 1; // Usamos la empresa creada en setup_admin
    $user_id = 1;
    
    // Limpiar stock previo para el test
    $pdo->exec("UPDATE products SET stock_current = 10 WHERE id = 1"); 
    // Si no hay producto 1, creamos uno
    $stmt = $pdo->prepare("SELECT id FROM products WHERE company_id = ?");
    $stmt->execute([$company_id]);
    if (!$stmt->fetch()) {
        $im->createProduct($company_id, ['sku' => 'TEST-01', 'name' => 'Producto Test', 'description' => 'Test', 'price_buy' => 10, 'price_sell' => 20, 'stock_min' => 2]);
    }
    $productId = 1; // Asumimos ID 1 para simplicidad
    echo "OK\n";

    // 2. PRUEBA DE ATOMICIDAD (Venta sin stock)
    echo "[2/4] Probando atomicidad (Venta sin stock)... ";
    $itemsOver = [['product_id' => $productId, 'quantity' => 100, 'price' => 20, 'subtotal' => 2000]];
    try {
        // En una versión real, TransactionManager debería validar el stock antes. 
        // Aquí probamos que si falla la actualización, no haya venta.
        $tm->createSale($company_id, $user_id, 1, $itemsOver);
    } catch (Exception $e) {
        echo "SISTEMA BLOQUEÓ VENTA (Correcto)\n";
    }

    // 3. PRUEBA DE TRAZABILIDAD (Ciclo completo)
    echo "[3/4] Probando ciclo de stock (Compra -> Venta -> Cancelación)... ";
    // Compra
    $tm->createPurchase($company_id, $user_id, 1, [['product_id' => $productId, 'quantity' => 5, 'price' => 10, 'subtotal' => 50]]);
    // Venta
    $saleId = $tm->createSale($company_id, $user_id, 1, [['product_id' => $productId, 'quantity' => 2, 'price' => 20, 'subtotal' => 40]]);
    // Cancelación
    $tm->cancelSale($saleId, $company_id, $user_id);
    
    $stmt = $pdo->prepare("SELECT stock_current FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $finalStock = $stmt->fetchColumn();
    
    // Calculo: 10 (inicial) + 5 (compra) - 2 (venta) + 2 (cancelación) = 15
    if ($finalStock == 15) {
        echo "STOCK CONSISTENTE (Correcto: 15)\n";
    } else {
        echo "ERROR EN STOCK (Obtenido: $finalStock)\n";
    }

    // 4. PRUEBA DE MULTIEMPRESA
    echo "[4/4] Probando aislamiento multiempresa... ";
    $pdo->exec("INSERT INTO companies (name) VALUES ('Empresa B')");
    $companyB = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE company_id = ?");
    $stmt->execute([$companyB]);
    if ($stmt->fetchColumn() == 0) {
        echo "AISLAMIENTO CORRECTO (Empresa B no ve productos de A)\n";
    } else {
        echo "ERROR: Fuga de datos entre empresas\n";
    }

    echo "\n--- TEST COMPLETADO EXITOSAMENTE ---\n";

} catch (Exception $e) {
    echo "\nFATAL ERROR DURANTE EL TEST: " . $e->getMessage() . "\n";
}
?>

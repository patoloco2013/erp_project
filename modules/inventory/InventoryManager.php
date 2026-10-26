<?php
class InventoryManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createProduct($company_id, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO products (company_id, sku, name, description, price_buy, price_sell, stock_min) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $company_id, 
            $data['sku'], 
            $data['name'], 
            $data['description'], 
            $data['price_buy'], 
            $data['price_sell'], 
            $data['stock_min']
        ]);
    }

    public function getProducts($company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE company_id = ?");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    /**
     * Registra un movimiento de stock y actualiza el saldo actual del producto
     * Usamos una TRANSACCIÓN para asegurar que no haya inconsistencias
     */
    public function moveStock($company_id, $product_id, $quantity, $type, $reference_id, $user_id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Registrar el movimiento
            $stmtMove = $this->pdo->prepare("INSERT INTO stock_movements (product_id, company_id, quantity, type, reference_id, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtMove->execute([$product_id, $company_id, $quantity, $type, $reference_id, $user_id]);

            // 2. Actualizar el stock actual en la tabla products
            $stmtUpdate = $this->pdo->prepare("UPDATE products SET stock_current = stock_current + ? WHERE id = ? AND company_id = ?");
            $stmtUpdate->execute([$quantity, $product_id, $company_id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getProductMovements($product_id) {
        $stmt = $this->pdo->prepare("SELECT m.*, p.name as product_name FROM stock_movements m JOIN products p ON m.product_id = p.id WHERE m.product_id = ? ORDER BY m.created_at DESC");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll();
    }
}
?>

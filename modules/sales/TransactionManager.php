<?php
class TransactionManager {
    private $pdo;
    private $inventory;

    public function __construct($pdo, $inventory) {
        $this->pdo = $pdo;
        $this->inventory = $inventory;
    }

    public function createSale($company_id, $user_id, $client_id, $items) {
        try {
            $this->pdo->beginTransaction();

            $total = 0;
            foreach ($items as $item) {
                $total += $item['subtotal'];
            }

            $stmt = $this->pdo->prepare("INSERT INTO sales (company_id, client_id, user_id, total) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_id, $client_id, $user_id, $total]);
            $sale_id = $this->pdo->lastInsertId();

            foreach ($items as $item) {
                // Detalle de venta
                $stmtDet = $this->pdo->prepare("INSERT INTO sale_details (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmtDet->execute([$sale_id, $item['product_id'], $item['quantity'], $item['price'], $item['subtotal']]);

                // Salida de Stock
                $this->inventory->moveStock($company_id, $item['product_id'], -$item['quantity'], 'sale', $sale_id, $user_id);
            }

            $this->pdo->commit();
            return $sale_id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function cancelSale($sale_id, $company_id, $user_id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Cambiar estado de la venta
            $stmt = $this->pdo->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ? AND company_id = ?");
            $stmt->execute([$sale_id, $company_id]);

            // 2. Reintegrar stock
            $stmtDetails = $this->pdo->prepare("SELECT product_id, quantity FROM sale_details WHERE sale_id = ?");
            $stmtDetails->execute([$sale_id]);
            $details = $stmtDetails->fetchAll();

            foreach ($details as $item) {
                $this->inventory->moveStock($company_id, $item['product_id'], $item['quantity'], 'return_sale', $sale_id, $user_id);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function createPurchase($company_id, $user_id, $supplier_id, $items) {
        try {
            $this->pdo->beginTransaction();

            $total = 0;
            foreach ($items as $item) {
                $total += $item['subtotal'];
            }

            $stmt = $this->pdo->prepare("INSERT INTO purchases (company_id, supplier_id, user_id, total) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_id, $supplier_id, $user_id, $total]);
            $purchase_id = $this->pdo->lastInsertId();

            foreach ($items as $item) {
                // Detalle de compra
                $stmtDet = $this->pdo->prepare("INSERT INTO purchase_details (purchase_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmtDet->execute([$purchase_id, $item['product_id'], $item['quantity'], $item['price'], $item['subtotal']]);

                // Entrada de Stock
                $this->inventory->moveStock($company_id, $item['product_id'], $item['quantity'], 'purchase', $purchase_id, $user_id);
            }

            $this->pdo->commit();
            return $purchase_id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>

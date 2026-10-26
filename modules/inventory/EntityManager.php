<?php
class EntityManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function validate($data) {
        return array_map('htmlspecialchars', $data);
    }

    public function createClient($company_id, $data) {
        $data = $this->validate($data);
        $stmt = $this->pdo->prepare("INSERT INTO clients (company_id, name, tax_id, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$company_id, $data['name'], $data['tax_id'], $data['email'], $data['phone'], $data['address']]);
    }

    public function getClients($company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE company_id = ?");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    public function createSupplier($company_id, $data) {
        $data = $this->validate($data);
        $stmt = $this->pdo->prepare("INSERT INTO suppliers (company_id, name, tax_id, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$company_id, $data['name'], $data['tax_id'], $data['email'], $data['phone'], $data['address']]);
    }

    public function getSuppliers($company_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE company_id = ?");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }
}
?>

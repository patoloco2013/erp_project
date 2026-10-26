<?php
class Auditor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function log($company_id, $user_id, $action, $table_name = null, $record_id = null, $old_value = null, $new_value = null) {
        $stmt = $this->pdo->prepare("INSERT INTO audit_log (company_id, user_id, action, table_name, record_id, old_value, new_value, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        return $stmt->execute([
            $company_id, 
            $user_id, 
            $action, 
            $table_name, 
            $record_id, 
            is_array($old_value) ? json_encode($old_value) : $old_value, 
            is_array($new_value) ? json_encode($new_value) : $new_value, 
            $ip
        ]);
    }

    public function getLogs($company_id) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.username 
            FROM audit_log a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.company_id = ? 
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }
}
?>

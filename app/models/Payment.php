// FILE: /app/models/Payment.php
<?php

/**
 * Payment Model
 *
 * Represents payment transactions
 */
class Payment extends Model
{
    protected $table = 'payments';

    /**
     * Get payments by tenant
     */
    public function findByTenant($tenantId, $limit = null)
    {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = ?
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $stmt = $this->query($sql, [$tenantId, $limit]);
        } else {
            $stmt = $this->query($sql, [$tenantId]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Create payment
     */
    public function createPayment($data)
    {
        // Simulate payment processing
        $data['transaction_id'] = 'txn_' . bin2hex(random_bytes(12));
        $data['status'] = 'completed'; // Simulated success

        return $this->insert($data);
    }

    /**
     * Get total revenue by tenant
     */
    public function getTotalRevenue($tenantId)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table}
                WHERE tenant_id = ? AND status = 'completed'";

        $stmt = $this->query($sql, [$tenantId]);
        $result = $stmt->fetch();

        return (float)($result['total'] ?? 0);
    }
}

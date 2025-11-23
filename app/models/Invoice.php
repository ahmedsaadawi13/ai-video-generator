// FILE: /app/models/Invoice.php
<?php

/**
 * Invoice Model
 *
 * Represents billing invoices
 */
class Invoice extends Model
{
    protected $table = 'invoices';

    /**
     * Get invoices by tenant with pagination
     */
    public function findByTenantWithPagination($tenantId, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$tenantId, $perPage, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Y') . '-';
        $lastInvoice = $this->query(
            "SELECT invoice_number FROM {$this->table}
             WHERE invoice_number LIKE ?
             ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        )->fetch();

        if ($lastInvoice) {
            $lastNumber = (int)str_replace($prefix, '', $lastInvoice['invoice_number']);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid($id)
    {
        return $this->update($id, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get pending invoices by tenant
     */
    public function getPending($tenantId)
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table}
             WHERE tenant_id = ? AND status = 'pending'
             ORDER BY due_date ASC",
            [$tenantId]
        );
        return $stmt->fetchAll();
    }
}

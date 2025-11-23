// FILE: /app/models/UsageTracking.php
<?php

/**
 * UsageTracking Model
 *
 * Tracks tenant usage metrics
 */
class UsageTracking extends Model
{
    protected $table = 'usage_tracking';

    /**
     * Get or create current month usage
     */
    public function getCurrentMonthUsage($tenantId)
    {
        $currentMonth = date('Y-m-01');

        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE tenant_id = ? AND month = ? LIMIT 1",
            [$tenantId, $currentMonth]
        );

        $usage = $stmt->fetch();

        if (!$usage) {
            $id = $this->insert([
                'tenant_id' => $tenantId,
                'month' => $currentMonth,
                'projects_count' => 0,
                'renders_count' => 0,
                'render_minutes' => 0,
                'storage_mb' => 0,
            ]);

            return $this->findById($id);
        }

        return $usage;
    }

    /**
     * Increment project count
     */
    public function incrementProjects($tenantId)
    {
        $usage = $this->getCurrentMonthUsage($tenantId);

        $sql = "UPDATE {$this->table} SET projects_count = projects_count + 1 WHERE id = ?";
        $stmt = $this->query($sql, [$usage['id']]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Increment render count
     */
    public function incrementRenders($tenantId, $renderMinutes = 0)
    {
        $usage = $this->getCurrentMonthUsage($tenantId);

        $sql = "UPDATE {$this->table}
                SET renders_count = renders_count + 1,
                    render_minutes = render_minutes + ?
                WHERE id = ?";

        $stmt = $this->query($sql, [$renderMinutes, $usage['id']]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update storage usage
     */
    public function updateStorage($tenantId, $storageMb)
    {
        $usage = $this->getCurrentMonthUsage($tenantId);

        return $this->update($usage['id'], ['storage_mb' => $storageMb]);
    }

    /**
     * Get usage history
     */
    public function getHistory($tenantId, $months = 6)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY month DESC
                LIMIT ?";

        $stmt = $this->query($sql, [$tenantId, $months]);
        return $stmt->fetchAll();
    }
}

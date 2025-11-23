// FILE: /app/models/Tenant.php
<?php

/**
 * Tenant Model
 *
 * Represents a tenant (brand/agency account) in the multi-tenant system
 */
class Tenant extends Model
{
    protected $table = 'tenants';

    /**
     * Find tenant by slug
     */
    public function findBySlug($slug)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1", [$slug]);
        return $stmt->fetch();
    }

    /**
     * Get tenant's active subscription
     */
    public function getActiveSubscription($tenantId)
    {
        $sql = "SELECT ts.*, p.name as plan_name, p.max_projects, p.max_renders_per_month,
                p.max_render_minutes_per_month, p.max_storage_mb, p.max_team_members
                FROM tenant_subscriptions ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                LIMIT 1";

        $stmt = $this->query($sql, [$tenantId]);
        return $stmt->fetch();
    }

    /**
     * Get tenant usage for current month
     */
    public function getCurrentUsage($tenantId)
    {
        $currentMonth = date('Y-m-01');

        $sql = "SELECT * FROM usage_tracking
                WHERE tenant_id = ? AND month = ?
                LIMIT 1";

        $stmt = $this->query($sql, [$tenantId, $currentMonth]);
        $usage = $stmt->fetch();

        if (!$usage) {
            // Create new usage record for current month
            $usageId = $this->insert([
                'tenant_id' => $tenantId,
                'month' => $currentMonth,
                'projects_count' => 0,
                'renders_count' => 0,
                'render_minutes' => 0,
                'storage_mb' => 0,
            ]);

            return [
                'id' => $usageId,
                'tenant_id' => $tenantId,
                'month' => $currentMonth,
                'projects_count' => 0,
                'renders_count' => 0,
                'render_minutes' => 0,
                'storage_mb' => 0,
            ];
        }

        return $usage;
    }

    /**
     * Check if tenant can create more projects
     */
    public function canCreateProject($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);
        $usage = $this->getCurrentUsage($tenantId);

        if ($subscription['max_projects'] === -1) {
            return true; // Unlimited
        }

        return $usage['projects_count'] < $subscription['max_projects'];
    }

    /**
     * Check if tenant can render more videos this month
     */
    public function canRenderVideo($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);
        $usage = $this->getCurrentUsage($tenantId);

        if ($subscription['max_renders_per_month'] === -1) {
            return true; // Unlimited
        }

        return $usage['renders_count'] < $subscription['max_renders_per_month'];
    }

    /**
     * Get all active tenants
     */
    public function getAllActive()
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll();
    }
}

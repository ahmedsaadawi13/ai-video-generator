// FILE: /app/models/Subscription.php
<?php

/**
 * Subscription Model
 *
 * Represents tenant subscription
 */
class Subscription extends Model
{
    protected $table = 'tenant_subscriptions';

    /**
     * Get active subscription for tenant
     */
    public function getActiveByTenant($tenantId)
    {
        $sql = "SELECT ts.*, p.name as plan_name, p.slug as plan_slug,
                p.max_projects, p.max_renders_per_month, p.max_render_minutes_per_month,
                p.max_storage_mb, p.max_team_members
                FROM {$this->table} ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                LIMIT 1";

        $stmt = $this->query($sql, [$tenantId]);
        return $stmt->fetch();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired($subscriptionId)
    {
        $subscription = $this->findById($subscriptionId);

        if (!$subscription) {
            return true;
        }

        $endDate = strtotime($subscription['current_period_end']);
        $now = time();

        return $now > $endDate;
    }

    /**
     * Cancel subscription
     */
    public function cancel($subscriptionId)
    {
        return $this->update($subscriptionId, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Renew subscription
     */
    public function renew($subscriptionId)
    {
        $subscription = $this->findById($subscriptionId);

        if (!$subscription) {
            return false;
        }

        $billingCycle = $subscription['billing_cycle'];
        $currentEnd = strtotime($subscription['current_period_end']);

        if ($billingCycle === 'monthly') {
            $newEnd = date('Y-m-d', strtotime('+1 month', $currentEnd));
        } else {
            $newEnd = date('Y-m-d', strtotime('+1 year', $currentEnd));
        }

        return $this->update($subscriptionId, [
            'current_period_start' => $subscription['current_period_end'],
            'current_period_end' => $newEnd,
            'status' => 'active',
        ]);
    }
}

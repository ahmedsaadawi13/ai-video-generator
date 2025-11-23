// FILE: /app/models/Plan.php
<?php

/**
 * Plan Model
 *
 * Represents a subscription plan
 */
class Plan extends Model
{
    protected $table = 'plans';

    /**
     * Get all active plans
     */
    public function getAllActive()
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order, price_monthly"
        );
        return $stmt->fetchAll();
    }

    /**
     * Find plan by slug
     */
    public function findBySlug($slug)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1", [$slug]);
        return $stmt->fetch();
    }

    /**
     * Get plan features as array
     */
    public function getFeatures($planId)
    {
        $plan = $this->findById($planId);

        if (!$plan || empty($plan['features'])) {
            return [];
        }

        return json_decode($plan['features'], true) ?: [];
    }
}

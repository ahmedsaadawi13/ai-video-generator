// FILE: /app/models/BrandSetting.php
<?php

/**
 * BrandSetting Model
 *
 * Represents brand settings for a tenant
 */
class BrandSetting extends Model
{
    protected $table = 'brand_settings';

    /**
     * Get brand settings by tenant
     */
    public function findByTenant($tenantId)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE tenant_id = ? LIMIT 1", [$tenantId]);
        $settings = $stmt->fetch();

        // Create default settings if not exists
        if (!$settings) {
            $id = $this->insert([
                'tenant_id' => $tenantId,
                'primary_color' => '#3B82F6',
                'secondary_color' => '#10B981',
                'default_font' => 'Arial',
            ]);

            $settings = $this->findById($id);
        }

        return $settings;
    }

    /**
     * Update brand settings
     */
    public function updateByTenant($tenantId, $data)
    {
        $settings = $this->findByTenant($tenantId);

        return $this->update($settings['id'], $data);
    }
}

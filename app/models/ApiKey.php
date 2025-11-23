// FILE: /app/models/ApiKey.php
<?php

/**
 * ApiKey Model
 *
 * Represents API keys for external access
 */
class ApiKey extends Model
{
    protected $table = 'api_keys';

    /**
     * Get API keys by tenant
     */
    public function findByTenant($tenantId)
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE tenant_id = ? ORDER BY created_at DESC",
            [$tenantId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Verify API key
     */
    public function verify($apiKey)
    {
        $hash = hash('sha256', $apiKey);

        $stmt = $this->query(
            "SELECT ak.*, t.status as tenant_status
             FROM {$this->table} ak
             JOIN tenants t ON ak.tenant_id = t.id
             WHERE ak.key_hash = ? AND ak.is_active = 1
             LIMIT 1",
            [$hash]
        );

        $key = $stmt->fetch();

        if (!$key) {
            return false;
        }

        // Check if expired
        if ($key['expires_at'] && strtotime($key['expires_at']) < time()) {
            return false;
        }

        // Check tenant status
        if ($key['tenant_status'] !== 'active') {
            return false;
        }

        // Update last used timestamp
        $this->update($key['id'], ['last_used_at' => date('Y-m-d H:i:s')]);

        return $key;
    }

    /**
     * Generate new API key
     */
    public function generate($tenantId, $name, $expiresAt = null)
    {
        $prefix = 'avgen_live_';
        $randomPart = bin2hex(random_bytes(20));
        $apiKey = $prefix . $randomPart;

        $hash = hash('sha256', $apiKey);

        $data = [
            'tenant_id' => $tenantId,
            'name' => $name,
            'key_hash' => $hash,
            'expires_at' => $expiresAt,
            'is_active' => 1,
        ];

        $id = $this->insert($data);

        return [
            'id' => $id,
            'api_key' => $apiKey, // Only returned once
        ];
    }

    /**
     * Revoke API key
     */
    public function revoke($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }
}

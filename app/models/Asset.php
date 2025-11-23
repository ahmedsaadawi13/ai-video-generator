// FILE: /app/models/Asset.php
<?php

/**
 * Asset Model
 *
 * Represents media assets (images, videos, audio)
 */
class Asset extends Model
{
    protected $table = 'assets';

    /**
     * Get assets by tenant with pagination and filters
     */
    public function findByTenantWithPagination($tenantId, $page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $where = ['tenant_id' => $tenantId];

        if (!empty($filters['file_type'])) {
            $where['file_type'] = $filters['file_type'];
        }

        $conditions = [];
        $params = [];

        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        $sql = "SELECT a.*, u.first_name, u.last_name
                FROM {$this->table} a
                JOIN users u ON a.user_id = u.id
                WHERE " . implode(' AND ', $conditions);

        // Add search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (a.name LIKE ? OR a.tags LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Count assets by tenant
     */
    public function countByTenant($tenantId, $filters = [])
    {
        $where = ['tenant_id' => $tenantId];

        if (!empty($filters['file_type'])) {
            $where['file_type'] = $filters['file_type'];
        }

        $count = $this->count($where);

        // Adjust for search if needed
        if (!empty($filters['search'])) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}
                    WHERE tenant_id = ? AND (name LIKE ? OR tags LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $stmt = $this->query($sql, [$tenantId, $searchTerm, $searchTerm]);
            $result = $stmt->fetch();
            return (int)$result['total'];
        }

        return $count;
    }

    /**
     * Get total storage used by tenant
     */
    public function getTotalStorageByTenant($tenantId)
    {
        $sql = "SELECT SUM(file_size) as total FROM {$this->table} WHERE tenant_id = ?";
        $stmt = $this->query($sql, [$tenantId]);
        $result = $stmt->fetch();

        return (int)($result['total'] ?? 0);
    }

    /**
     * Get assets by type
     */
    public function findByType($tenantId, $fileType)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND file_type = ?
                ORDER BY created_at DESC";

        $stmt = $this->query($sql, [$tenantId, $fileType]);
        return $stmt->fetchAll();
    }

    /**
     * Search assets by tags
     */
    public function searchByTags($tenantId, $tag)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND tags LIKE ?
                ORDER BY created_at DESC";

        $stmt = $this->query($sql, [$tenantId, '%' . $tag . '%']);
        return $stmt->fetchAll();
    }
}

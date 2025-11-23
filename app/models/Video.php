// FILE: /app/models/Video.php
<?php

/**
 * Video Model
 *
 * Represents a completed video
 */
class Video extends Model
{
    protected $table = 'videos';

    /**
     * Get videos by tenant with pagination
     */
    public function findByTenantWithPagination($tenantId, $page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT v.*, p.name as project_name, u.first_name, u.last_name
                FROM {$this->table} v
                JOIN projects p ON v.project_id = p.id
                JOIN users u ON v.user_id = u.id
                WHERE v.tenant_id = ?";

        $params = [$tenantId];

        // Add search filter
        if (!empty($filters['search'])) {
            $sql .= " AND v.title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY v.created_at DESC LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Count videos by tenant
     */
    public function countByTenant($tenantId, $filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['search'])) {
            $sql .= " AND title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Find by public token
     */
    public function findByToken($token)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE public_token = ? LIMIT 1", [$token]);
        return $stmt->fetch();
    }

    /**
     * Generate unique public token
     */
    public function generatePublicToken()
    {
        do {
            $token = bin2hex(random_bytes(32));
            $existing = $this->findByToken($token);
        } while ($existing);

        return $token;
    }

    /**
     * Increment view count
     */
    public function incrementViews($id)
    {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Increment download count
     */
    public function incrementDownloads($id)
    {
        $sql = "UPDATE {$this->table} SET download_count = download_count + 1 WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get recent videos
     */
    public function getRecent($tenantId, $limit = 5)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY created_at DESC
                LIMIT ?";

        $stmt = $this->query($sql, [$tenantId, $limit]);
        return $stmt->fetchAll();
    }
}

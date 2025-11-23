// FILE: /app/models/RenderJob.php
<?php

/**
 * RenderJob Model
 *
 * Represents a video rendering job
 */
class RenderJob extends Model
{
    protected $table = 'render_jobs';

    /**
     * Get render jobs by tenant with pagination
     */
    public function findByTenantWithPagination($tenantId, $page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $where = ['rj.tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $where['rj.status'] = $filters['status'];
        }

        $conditions = [];
        $params = [];

        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        $sql = "SELECT rj.*, p.name as project_name, u.first_name, u.last_name
                FROM {$this->table} rj
                JOIN projects p ON rj.project_id = p.id
                JOIN users u ON rj.user_id = u.id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY rj.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Count render jobs by tenant
     */
    public function countByTenant($tenantId, $filters = [])
    {
        $where = ['tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $where['status'] = $filters['status'];
        }

        return $this->count($where);
    }

    /**
     * Get queued jobs
     */
    public function getQueued($limit = 10)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'queued'
                ORDER BY created_at ASC
                LIMIT ?";

        $stmt = $this->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Update job status
     */
    public function updateStatus($id, $status, $progress = null, $errorMessage = null)
    {
        $data = ['status' => $status];

        if ($progress !== null) {
            $data['progress'] = $progress;
        }

        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }

        if ($status === 'processing' && !$this->findById($id)['started_at']) {
            $data['started_at'] = date('Y-m-d H:i:s');
        }

        if ($status === 'completed' || $status === 'failed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $data);
    }

    /**
     * Get render count for current month by tenant
     */
    public function getMonthlyCount($tenantId)
    {
        $startOfMonth = date('Y-m-01 00:00:00');

        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE tenant_id = ? AND created_at >= ?";

        $stmt = $this->query($sql, [$tenantId, $startOfMonth]);
        $result = $stmt->fetch();

        return (int)$result['total'];
    }

    /**
     * Get recent renders by tenant
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

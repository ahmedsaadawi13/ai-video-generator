// FILE: /app/models/Project.php
<?php

/**
 * Project Model
 *
 * Represents a video project
 */
class Project extends Model
{
    protected $table = 'projects';

    /**
     * Get projects by tenant with pagination
     */
    public function findByTenantWithPagination($tenantId, $page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $where = ['tenant_id' => $tenantId];

        if (!empty($filters['type'])) {
            $where['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where['status'] = $filters['status'];
        }

        $conditions = [];
        $params = [];

        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        $sql = "SELECT p.*, u.first_name, u.last_name
                FROM {$this->table} p
                JOIN users u ON p.user_id = u.id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get project with scenes
     */
    public function findByIdWithScenes($id, $tenantId)
    {
        $project = $this->query(
            "SELECT * FROM {$this->table} WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        )->fetch();

        if (!$project) {
            return null;
        }

        $scenes = $this->query(
            "SELECT * FROM scenes WHERE project_id = ? ORDER BY position",
            [$id]
        )->fetchAll();

        $project['scenes'] = $scenes;

        return $project;
    }

    /**
     * Count projects by tenant
     */
    public function countByTenant($tenantId, $filters = [])
    {
        $where = ['tenant_id' => $tenantId];

        if (!empty($filters['type'])) {
            $where['type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where['status'] = $filters['status'];
        }

        return $this->count($where);
    }

    /**
     * Get recent projects
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

    /**
     * Duplicate project
     */
    public function duplicate($projectId, $tenantId, $userId)
    {
        $original = $this->query(
            "SELECT * FROM {$this->table} WHERE id = ? AND tenant_id = ?",
            [$projectId, $tenantId]
        )->fetch();

        if (!$original) {
            return false;
        }

        unset($original['id']);
        $original['name'] = $original['name'] . ' (Copy)';
        $original['user_id'] = $userId;
        $original['status'] = 'draft';
        $original['created_at'] = date('Y-m-d H:i:s');

        return $this->insert($original);
    }
}

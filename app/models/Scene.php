// FILE: /app/models/Scene.php
<?php

/**
 * Scene Model
 *
 * Represents a scene within a project
 */
class Scene extends Model
{
    protected $table = 'scenes';

    /**
     * Get scenes by project
     */
    public function findByProject($projectId, $tenantId)
    {
        $sql = "SELECT s.*, a.name as asset_name, a.file_type as asset_type
                FROM {$this->table} s
                LEFT JOIN assets a ON s.asset_id = a.id
                WHERE s.project_id = ? AND s.tenant_id = ?
                ORDER BY s.position";

        $stmt = $this->query($sql, [$projectId, $tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Get next position for project
     */
    public function getNextPosition($projectId)
    {
        $sql = "SELECT MAX(position) as max_pos FROM {$this->table} WHERE project_id = ?";
        $stmt = $this->query($sql, [$projectId]);
        $result = $stmt->fetch();

        return ($result['max_pos'] ?? 0) + 1;
    }

    /**
     * Reorder scenes
     */
    public function reorder($projectId, $sceneIds)
    {
        $position = 1;
        foreach ($sceneIds as $sceneId) {
            $this->update($sceneId, ['position' => $position]);
            $position++;
        }
        return true;
    }

    /**
     * Get total duration for project
     */
    public function getTotalDuration($projectId)
    {
        $sql = "SELECT SUM(duration) as total FROM {$this->table} WHERE project_id = ?";
        $stmt = $this->query($sql, [$projectId]);
        $result = $stmt->fetch();

        return (int)($result['total'] ?? 0);
    }
}

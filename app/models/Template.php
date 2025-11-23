// FILE: /app/models/Template.php
<?php

/**
 * Template Model
 *
 * Represents a video template
 */
class Template extends Model
{
    protected $table = 'templates';

    /**
     * Get templates with pagination
     */
    public function findWithPagination($tenantId = null, $page = 1, $perPage = 20, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT t.*, u.first_name, u.last_name
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE (t.is_public = 1 OR t.tenant_id = ?)";

        $params = [$tenantId];

        // Add category filter
        if (!empty($filters['category'])) {
            $sql .= " AND t.category = ?";
            $params[] = $filters['category'];
        }

        $sql .= " ORDER BY t.is_public DESC, t.usage_count DESC, t.created_at DESC
                  LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get template with scenes
     */
    public function findByIdWithScenes($id)
    {
        $template = $this->findById($id);

        if (!$template) {
            return null;
        }

        $scenes = $this->query(
            "SELECT * FROM template_scenes WHERE template_id = ? ORDER BY position",
            [$id]
        )->fetchAll();

        $template['scenes'] = $scenes;

        return $template;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage($id)
    {
        $sql = "UPDATE {$this->table} SET usage_count = usage_count + 1 WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get public templates
     */
    public function getPublicTemplates($limit = null)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_public = 1
                ORDER BY usage_count DESC, created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $stmt = $this->query($sql, [$limit]);
        } else {
            $stmt = $this->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get templates by category
     */
    public function getByCategory($category, $tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE category = ? AND (is_public = 1 OR tenant_id = ?)
                ORDER BY usage_count DESC";

        $stmt = $this->query($sql, [$category, $tenantId]);
        return $stmt->fetchAll();
    }
}

// FILE: /app/models/StylePreset.php
<?php

/**
 * StylePreset Model
 *
 * Represents video style presets
 */
class StylePreset extends Model
{
    protected $table = 'style_presets';

    /**
     * Get all active presets
     */
    public function getAllActive()
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Find by slug
     */
    public function findBySlug($slug)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1", [$slug]);
        return $stmt->fetch();
    }

    /**
     * Get metadata as array
     */
    public function getMetadata($presetId)
    {
        $preset = $this->findById($presetId);

        if (!$preset || empty($preset['metadata'])) {
            return [];
        }

        return json_decode($preset['metadata'], true) ?: [];
    }
}

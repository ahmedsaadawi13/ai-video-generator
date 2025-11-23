// FILE: /app/models/Analytics.php
<?php

/**
 * Analytics Model
 *
 * Represents analytics events
 */
class Analytics extends Model
{
    protected $table = 'analytics_events';

    /**
     * Track event
     */
    public function track($tenantId, $userId, $eventType, $eventData = null)
    {
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'event_data' => $eventData ? json_encode($eventData) : null,
        ];

        return $this->insert($data);
    }

    /**
     * Get events by type for date range
     */
    public function getEventsByType($tenantId, $eventType, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND event_type = ?";
        $params = [$tenantId, $eventType];

        if ($startDate) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get event count by type
     */
    public function getEventCount($tenantId, $eventType, $startDate = null, $endDate = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE tenant_id = ? AND event_type = ?";
        $params = [$tenantId, $eventType];

        if ($startDate) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate;
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();

        return (int)$result['total'];
    }

    /**
     * Get popular event types
     */
    public function getPopularEvents($tenantId, $limit = 10)
    {
        $sql = "SELECT event_type, COUNT(*) as count
                FROM {$this->table}
                WHERE tenant_id = ?
                GROUP BY event_type
                ORDER BY count DESC
                LIMIT ?";

        $stmt = $this->query($sql, [$tenantId, $limit]);
        return $stmt->fetchAll();
    }
}

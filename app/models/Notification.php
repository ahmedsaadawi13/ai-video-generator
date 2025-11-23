// FILE: /app/models/Notification.php
<?php

/**
 * Notification Model
 *
 * Represents user notifications
 */
class Notification extends Model
{
    protected $table = 'notifications';

    /**
     * Get notifications by user
     */
    public function findByUser($userId, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?";

        $stmt = $this->query($sql, [$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE user_id = ? AND is_read = 0";

        $stmt = $this->query($sql, [$userId]);
        $result = $stmt->fetch();

        return (int)$result['total'];
    }

    /**
     * Mark as read
     */
    public function markAsRead($id)
    {
        return $this->update($id, ['is_read' => 1]);
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->rowCount();
    }

    /**
     * Create notification
     */
    public function create($tenantId, $userId, $type, $title, $message, $metadata = null)
    {
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'sent_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }
}

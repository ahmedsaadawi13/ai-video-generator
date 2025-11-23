// FILE: /app/models/User.php
<?php

/**
 * User Model
 *
 * Represents a user in the system
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1", [$email]);
        return $stmt->fetch();
    }

    /**
     * Find users by tenant
     */
    public function findByTenantWithPagination($tenantId, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$tenantId, $perPage, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Count users by tenant
     */
    public function countByTenant($tenantId)
    {
        $stmt = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE tenant_id = ?", [$tenantId]);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Create a new user
     */
    public function create($data)
    {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        return $this->insert($data);
    }

    /**
     * Verify user credentials
     */
    public function verify($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Update last login
        $this->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return $user;
    }

    /**
     * Get user with tenant info
     */
    public function findByIdWithTenant($id)
    {
        $sql = "SELECT u.*, t.name as tenant_name, t.slug as tenant_slug
                FROM {$this->table} u
                JOIN tenants t ON u.tenant_id = t.id
                WHERE u.id = ?
                LIMIT 1";

        $stmt = $this->query($sql, [$id]);
        return $stmt->fetch();
    }

    /**
     * Check if user has role
     */
    public function hasRole($userId, $roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        return in_array($user['role'], $roles);
    }
}

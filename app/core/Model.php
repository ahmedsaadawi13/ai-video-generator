// FILE: /app/core/Model.php
<?php

/**
 * Base Model Class
 *
 * All models extend this class
 * Provides common database operations
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find all records
     */
    public function findAll($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Find by tenant ID (multi-tenant support)
     */
    public function findByTenant($tenantId, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ?";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Count all records
     */
    public function count($where = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $key) {
                $conditions[] = "$key = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($where));
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    /**
     * Insert a new record
     */
    public function insert($data)
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return $this->db->lastInsertId();
    }

    /**
     * Update a record
     */
    public function update($id, $data)
    {
        $keys = array_keys($data);
        $fields = [];

        foreach ($keys as $key) {
            $fields[] = "$key = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete a record
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Execute custom query
     */
    protected function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

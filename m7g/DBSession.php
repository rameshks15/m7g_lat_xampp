<?php
/* Description: Session class for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */

class DBSession implements SessionHandlerInterface
{
    private $db;
    private $table;

    public function __construct(mysqli $db, string $table = 'sessions')
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $stmt = $this->db->prepare("SELECT data FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['data'];
        }
        
        return '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->db->prepare("REPLACE INTO {$this->table} (id, data, last_activity) VALUES (?, ?, NOW())");
        $stmt->bind_param('ss', $id, $data);
        
        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param('s', $id);
        
        return $stmt->execute();
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE last_activity < NOW() - INTERVAL ? SECOND");
        $stmt->bind_param('i', $max_lifetime);
        
        if ($stmt->execute()) {
            return $stmt->affected_rows; // Return the number of deleted rows
        }
        
        return false;
    }
}

?>
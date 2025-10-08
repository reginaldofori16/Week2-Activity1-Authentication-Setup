<?php
require_once __DIR__ . '/../settings/db_class.php';

class CategoryClass extends db_connection {
    public function __construct()
    {
        // Ensure DB connection is established on construction similar to Customer class
        parent::db_connect();
    }

    // Store last DB error message for debugging
    protected static $last_error = '';

    public static function get_last_error()
    {
        return self::$last_error;
    }

    /* Instance methods: use $this->db (mysqli) for prepared statements */
    private function create(string $name)
    {
        $stmt = $this->db->prepare("INSERT INTO categories (cat_name) VALUES (?)");
        if (!$stmt) return false;
        $stmt->bind_param('s', $name);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    private function fetchAll(): array
    {
        $sql = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
        $res = $this->db->query($sql);
        if (!$res) return [];
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        return $rows;
    }

    private function fetchById(int $id)
    {
        $stmt = $this->db->prepare("SELECT cat_id, cat_name FROM categories WHERE cat_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    private function modify(int $id, string $name)
    {
        $stmt = $this->db->prepare("UPDATE categories SET cat_name = ? WHERE cat_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('si', $name, $id);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    private function remove(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE cat_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    private function findByName(string $name)
    {
        $stmt = $this->db->prepare("SELECT cat_id FROM categories WHERE cat_name = ?");
        if (!$stmt) return false;
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    /* Static wrappers to preserve the existing API used by controllers */
    public static function add($name)
    {
        $obj = new self();
        return $obj->create(trim($name));
    }

    public static function get_all()
    {
        $obj = new self();
        return $obj->fetchAll();
    }

    public static function get_by_id($id)
    {
        $obj = new self();
        return $obj->fetchById((int)$id);
    }

    public static function update($id, $name)
    {
        $obj = new self();
        return $obj->modify((int)$id, trim($name));
    }

    public static function delete($id)
    {
        $obj = new self();
        return $obj->remove((int)$id);
    }

    public static function exists($name)
    {
        $obj = new self();
        return $obj->findByName(trim($name));
    }
}
?>

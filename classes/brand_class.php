<?php
require_once __DIR__ . '/../settings/db_class.php';

class BrandClass extends db_connection {
    public function __construct()
    {
        // Ensure DB connection is established on construction similar to Category class
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
        $stmt = $this->db->prepare("INSERT INTO brands (brand_name) VALUES (?)");
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
        $sql = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC";
        $res = $this->db->query($sql);
        if (!$res) return [];
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        return $rows;
    }

    private function fetchById(int $id)
    {
        $stmt = $this->db->prepare("SELECT brand_id, brand_name FROM brands WHERE brand_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    private function modify(int $id, string $name)
    {
        $stmt = $this->db->prepare("UPDATE brands SET brand_name = ? WHERE brand_id = ?");
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
        // Check if brand is referenced by any products before deleting
        $check_stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE product_brand = ?");
        if ($check_stmt) {
            $check_stmt->bind_param('i', $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();

            if ($result['count'] > 0) {
                self::$last_error = "Cannot delete brand: it is referenced by " . $result['count'] . " product(s)";
                return false;
            }
        }

        $stmt = $this->db->prepare("DELETE FROM brands WHERE brand_id = ?");
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
        $stmt = $this->db->prepare("SELECT brand_id FROM brands WHERE brand_name = ?");
        if (!$stmt) return false;
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    private function fetchBrandsByCategory(int $cat_id): array
    {
        // Get brands that have products in this category
        $sql = "SELECT DISTINCT b.brand_id, b.brand_name
                FROM brands b
                INNER JOIN products p ON b.brand_id = p.product_brand
                WHERE p.product_cat = ?
                ORDER BY b.brand_name ASC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('i', $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
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

    public static function get_brands_by_category($cat_id)
    {
        $obj = new self();
        return $obj->fetchBrandsByCategory((int)$cat_id);
    }
}
?>
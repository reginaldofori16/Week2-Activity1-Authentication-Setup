<?php
require_once __DIR__ . '/../settings/db_class.php';

class ProductClass extends db_connection {
    public function __construct()
    {
        // Ensure DB connection is established on construction
        parent::db_connect();
    }

    // Store last DB error message for debugging
    protected static $last_error = '';

    public static function get_last_error()
    {
        return self::$last_error;
    }

    /* Instance methods: use $this->db (mysqli) for prepared statements */
    private function create(int $cat_id, int $brand_id, string $title, float $price, string $desc = null, string $image = null, string $keywords = null)
    {
        $stmt = $this->db->prepare("INSERT INTO products (product_cat, product_brand, product_title, product_price, product_desc, product_image, product_keywords) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('iisdsss', $cat_id, $brand_id, $title, $price, $desc, $image, $keywords);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    private function fetchAll(): array
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                ORDER BY p.product_title ASC";
        $res = $this->db->query($sql);
        if (!$res) return [];
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        return $rows;
    }

    private function fetchById(int $id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.cat_name, b.brand_name
                                    FROM products p
                                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                                    WHERE p.product_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    private function fetchByUser(int $user_id): array
    {
        // Since products don't have user_id in schema, return all products
        // In a real application, you might want to add user_id to products table
        return $this->fetchAll();
    }

    private function modify(int $id, int $cat_id, int $brand_id, string $title, float $price, string $desc = null, string $image = null, string $keywords = null)
    {
        $stmt = $this->db->prepare("UPDATE products SET product_cat = ?, product_brand = ?, product_title = ?, product_price = ?, product_desc = ?, product_image = ?, product_keywords = ? WHERE product_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('iisdsssi', $cat_id, $brand_id, $title, $price, $desc, $image, $keywords, $id);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    private function remove(int $id)
    {
        // Check if product is referenced by any cart items before deleting
        $check_stmt = $this->db->prepare("SELECT COUNT(*) as count FROM cart WHERE p_id = ?");
        if ($check_stmt) {
            $check_stmt->bind_param('i', $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result()->fetch_assoc();
            $check_stmt->close();

            if ($result['count'] > 0) {
                self::$last_error = "Cannot delete product: it is referenced by " . $result['count'] . " cart item(s)";
                return false;
            }
        }

        $stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    private function findByTitle(string $title)
    {
        $stmt = $this->db->prepare("SELECT product_id FROM products WHERE product_title = ?");
        if (!$stmt) return false;
        $stmt->bind_param('s', $title);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    private function updateImage(int $id, string $image_path)
    {
        $stmt = $this->db->prepare("UPDATE products SET product_image = ? WHERE product_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('si', $image_path, $id);
        $ok = $stmt->execute();
        if (!$ok) {
            self::$last_error = $stmt->error ?: $this->db->error;
        }
        $stmt->close();
        return $ok;
    }

    /* Static wrappers to preserve the existing API used by controllers */
    public static function add($cat_id, $brand_id, $title, $price, $desc = null, $image = null, $keywords = null)
    {
        $obj = new self();
        return $obj->create((int)$cat_id, (int)$brand_id, trim($title), (float)$price, $desc, $image, $keywords);
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

    public static function get_by_user($user_id)
    {
        $obj = new self();
        return $obj->fetchByUser((int)$user_id);
    }

    public static function update($id, $cat_id, $brand_id, $title, $price, $desc = null, $image = null, $keywords = null)
    {
        $obj = new self();
        return $obj->modify((int)$id, (int)$cat_id, (int)$brand_id, trim($title), (float)$price, $desc, $image, $keywords);
    }

    public static function delete($id)
    {
        $obj = new self();
        return $obj->remove((int)$id);
    }

    public static function exists($title)
    {
        $obj = new self();
        return $obj->findByTitle(trim($title));
    }

    public static function update_image($id, $image_path)
    {
        $obj = new self();
        return $obj->updateImage((int)$id, $image_path);
    }

    // New methods for product display and search

    // View all products with pagination
    private function fetchAllWithPagination($limit = 10, $offset = 0): array
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                ORDER BY p.product_title ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Search products by title
    private function searchByTitle($query, $limit = 10, $offset = 0): array
    {
        $search_term = '%' . $query . '%';
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_title LIKE ?
                ORDER BY p.product_title ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('sii', $search_term, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Search products by keywords
    private function searchByKeywords($query, $limit = 10, $offset = 0): array
    {
        $search_term = '%' . $query . '%';
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_keywords LIKE ? OR p.product_title LIKE ? OR p.product_desc LIKE ?
                ORDER BY p.product_title ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('sssii', $search_term, $search_term, $search_term, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Filter products by category
    private function filterByCategory($cat_id, $limit = 10, $offset = 0): array
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_cat = ?
                ORDER BY p.product_title ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('iii', $cat_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Filter products by brand
    private function filterByBrand($brand_id, $limit = 10, $offset = 0): array
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE p.product_brand = ?
                ORDER BY p.product_title ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('iii', $brand_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Composite filter by category, brand, and price range
    private function filterComposite($cat_id = null, $brand_id = null, $min_price = null, $max_price = null, $limit = 10, $offset = 0): array
    {
        $conditions = [];
        $params = [];
        $types = '';

        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.product_cat = c.cat_id
                LEFT JOIN brands b ON p.product_brand = b.brand_id
                WHERE 1=1";

        if ($cat_id) {
            $conditions[] = "p.product_cat = ?";
            $params[] = $cat_id;
            $types .= 'i';
        }

        if ($brand_id) {
            $conditions[] = "p.product_brand = ?";
            $params[] = $brand_id;
            $types .= 'i';
        }

        if ($min_price !== null) {
            $conditions[] = "p.product_price >= ?";
            $params[] = $min_price;
            $types .= 'd';
        }

        if ($max_price !== null) {
            $conditions[] = "p.product_price <= ?";
            $params[] = $max_price;
            $types .= 'd';
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY p.product_title ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // Count total products for pagination
    private function countAll(): int
    {
        $sql = "SELECT COUNT(*) as total FROM products";
        $res = $this->db->query($sql);
        if (!$res) return 0;
        $row = $res->fetch_assoc();
        $res->free();
        return (int)$row['total'];
    }

    // Count filtered products for pagination
    private function countFiltered($cat_id = null, $brand_id = null, $min_price = null, $max_price = null, $search_query = null): int
    {
        $conditions = [];
        $params = [];
        $types = '';

        $sql = "SELECT COUNT(*) as total FROM products p WHERE 1=1";

        if ($cat_id) {
            $conditions[] = "p.product_cat = ?";
            $params[] = $cat_id;
            $types .= 'i';
        }

        if ($brand_id) {
            $conditions[] = "p.product_brand = ?";
            $params[] = $brand_id;
            $types .= 'i';
        }

        if ($min_price !== null) {
            $conditions[] = "p.product_price >= ?";
            $params[] = $min_price;
            $types .= 'd';
        }

        if ($max_price !== null) {
            $conditions[] = "p.product_price <= ?";
            $params[] = $max_price;
            $types .= 'd';
        }

        if ($search_query) {
            $search_term = '%' . $search_query . '%';
            $conditions[] = "(p.product_title LIKE ? OR p.product_keywords LIKE ? OR p.product_desc LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'sss';
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 0;

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['total'];
    }

    // Static wrappers for new methods
    public static function view_all_products($limit = 10, $offset = 0)
    {
        $obj = new self();
        $products = $obj->fetchAllWithPagination((int)$limit, (int)$offset);
        $total = $obj->countAll();
        return ['products' => $products, 'total' => $total];
    }

    public static function search_products($query, $limit = 10, $offset = 0)
    {
        $obj = new self();
        $products = $obj->searchByKeywords($query, (int)$limit, (int)$offset);
        $total = $obj->countFiltered(null, null, null, null, $query);
        return ['products' => $products, 'total' => $total];
    }

    public static function filter_products_by_category($cat_id, $limit = 10, $offset = 0)
    {
        $obj = new self();
        $products = $obj->filterByCategory((int)$cat_id, (int)$limit, (int)$offset);
        $total = $obj->countFiltered((int)$cat_id);
        return ['products' => $products, 'total' => $total];
    }

    public static function filter_products_by_brand($brand_id, $limit = 10, $offset = 0)
    {
        $obj = new self();
        $products = $obj->filterByBrand((int)$brand_id, (int)$limit, (int)$offset);
        $total = $obj->countFiltered(null, (int)$brand_id);
        return ['products' => $products, 'total' => $total];
    }

    public static function filter_products_composite($cat_id = null, $brand_id = null, $min_price = null, $max_price = null, $limit = 10, $offset = 0)
    {
        $obj = new self();
        $products = $obj->filterComposite(
            $cat_id ? (int)$cat_id : null,
            $brand_id ? (int)$brand_id : null,
            $min_price ? (float)$min_price : null,
            $max_price ? (float)$max_price : null,
            (int)$limit,
            (int)$offset
        );
        $total = $obj->countFiltered(
            $cat_id ? (int)$cat_id : null,
            $brand_id ? (int)$brand_id : null,
            $min_price ? (float)$min_price : null,
            $max_price ? (float)$max_price : null
        );
        return ['products' => $products, 'total' => $total];
    }

    public static function view_single_product($id)
    {
        return self::get_by_id($id);
    }
}
?>
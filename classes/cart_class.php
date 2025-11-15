<?php
require_once __DIR__ . '/../settings/db_class.php';

class CartClass extends db_connection
{
    private $customer_id;
    private $ip_address;

    public function __construct()
    {
        parent::db_connect();
        $this->ip_address = $this->getRealIpAddr();

        // Set customer_id if user is logged in
        if (isset($_SESSION['user_id'])) {
            $this->customer_id = $_SESSION['user_id'];
        } else {
            $this->customer_id = null;
        }
    }

    // Helper function to get real IP address
    private function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Add product to cart
     * If product already exists, update quantity
     */
    public function addToCart($product_id, $quantity = 1)
    {
        // Validate inputs
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($product_id <= 0 || $quantity <= 0) {
            return false;
        }

        // Check if product exists and get price
        $product_check = $this->db->prepare("SELECT product_id, product_title, product_price FROM products WHERE product_id = ?");
        $product_check->bind_param('i', $product_id);
        $product_check->execute();
        $product_result = $product_check->get_result();

        if ($product_result->num_rows === 0) {
            $product_check->close();
            return false; // Product doesn't exist
        }
        $product_check->close();

        // Check if product already in cart
        if ($this->customer_id) {
            // Logged-in user cart
            $check_sql = "SELECT qty FROM cart WHERE p_id = ? AND c_id = ?";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->bind_param('ii', $product_id, $this->customer_id);
        } else {
            // Guest cart (IP-based)
            $check_sql = "SELECT qty FROM cart WHERE p_id = ? AND ip_add = ? AND c_id IS NULL";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->bind_param('is', $product_id, $this->ip_address);
        }

        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        $check_stmt->close();

        if ($existing->num_rows > 0) {
            // Product exists in cart, update quantity
            $current_qty = $existing->fetch_assoc()['qty'];
            $new_qty = $current_qty + $quantity;

            if ($this->customer_id) {
                $update_sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND c_id = ?";
                $update_stmt = $this->db->prepare($update_sql);
                $update_stmt->bind_param('iii', $new_qty, $product_id, $this->customer_id);
            } else {
                $update_sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ? AND c_id IS NULL";
                $update_stmt = $this->db->prepare($update_sql);
                $update_stmt->bind_param('iis', $new_qty, $product_id, $this->ip_address);
            }

            $result = $update_stmt->execute();
            $update_stmt->close();
            return $result;
        } else {
            // Add new item to cart
            if ($this->customer_id) {
                $insert_sql = "INSERT INTO cart (p_id, c_id, ip_add, qty) VALUES (?, ?, ?, ?)";
                $insert_stmt = $this->db->prepare($insert_sql);
                $insert_stmt->bind_param('iisi', $product_id, $this->customer_id, $this->ip_address, $quantity);
            } else {
                $insert_sql = "INSERT INTO cart (p_id, ip_add, qty) VALUES (?, ?, ?)";
                $insert_stmt = $this->db->prepare($insert_sql);
                $insert_stmt->bind_param('isi', $product_id, $this->ip_address, $quantity);
            }

            $result = $insert_stmt->execute();
            $insert_stmt->close();
            return $result;
        }
    }

    /**
     * Update quantity of a specific cart item
     */
    public function updateQuantity($product_id, $quantity)
    {
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($product_id <= 0 || $quantity <= 0) {
            return false;
        }

        if ($this->customer_id) {
            $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iii', $quantity, $product_id, $this->customer_id);
        } else {
            $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ? AND c_id IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iis', $quantity, $product_id, $this->ip_address);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($product_id)
    {
        $product_id = (int)$product_id;

        if ($product_id <= 0) {
            return false;
        }

        if ($this->customer_id) {
            $sql = "DELETE FROM cart WHERE p_id = ? AND c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $product_id, $this->customer_id);
        } else {
            $sql = "DELETE FROM cart WHERE p_id = ? AND ip_add = ? AND c_id IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('is', $product_id, $this->ip_address);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Get all cart items with product details
     */
    public function getCartItems()
    {
        if ($this->customer_id) {
            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image,
                           cat.cat_name, b.brand_name
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                    WHERE c.c_id = ?
                    ORDER BY p.product_title";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $this->customer_id);
        } else {
            $sql = "SELECT c.p_id, c.qty, p.product_title, p.product_price, p.product_image,
                           cat.cat_name, b.brand_name
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    LEFT JOIN categories cat ON p.product_cat = cat.cat_id
                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                    WHERE c.ip_add = ? AND c.c_id IS NULL
                    ORDER BY p.product_title";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $this->ip_address);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Calculate subtotals
        foreach ($items as &$item) {
            $item['subtotal'] = $item['product_price'] * $item['qty'];
        }

        return $items;
    }

    /**
     * Get cart summary (total items, total amount)
     */
    public function getCartSummary()
    {
        if ($this->customer_id) {
            $sql = "SELECT COUNT(*) as item_count, SUM(c.qty * p.product_price) as total_amount
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    WHERE c.c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $this->customer_id);
        } else {
            $sql = "SELECT COUNT(*) as item_count, SUM(c.qty * p.product_price) as total_amount
                    FROM cart c
                    JOIN products p ON c.p_id = p.product_id
                    WHERE c.ip_add = ? AND c.c_id IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $this->ip_address);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();
        $stmt->close();

        return [
            'item_count' => (int)$summary['item_count'],
            'total_amount' => (float)$summary['total_amount']
        ];
    }

    /**
     * Empty cart for current user/guest
     */
    public function emptyCart()
    {
        if ($this->customer_id) {
            $sql = "DELETE FROM cart WHERE c_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $this->customer_id);
        } else {
            $sql = "DELETE FROM cart WHERE ip_add = ? AND c_id IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $this->ip_address);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Get cart item count
     */
    public function getCartItemCount()
    {
        $summary = $this->getCartSummary();
        return $summary['item_count'];
    }

    /**
     * Check if product is in cart
     */
    public function isInCart($product_id)
    {
        $product_id = (int)$product_id;

        if ($this->customer_id) {
            $sql = "SELECT 1 FROM cart WHERE p_id = ? AND c_id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $product_id, $this->customer_id);
        } else {
            $sql = "SELECT 1 FROM cart WHERE p_id = ? AND ip_add = ? AND c_id IS NULL LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('is', $product_id, $this->ip_address);
        }

        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /**
     * Migrate guest cart to user cart when user logs in
     */
    public function migrateGuestCart($customer_id)
    {
        // Update all guest cart items for this IP to belong to the customer
        $sql = "UPDATE cart SET c_id = ? WHERE ip_add = ? AND c_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('is', $customer_id, $this->ip_address);
        $result = $stmt->execute();
        $stmt->close();

        // Handle any duplicates (if user already had some items in cart)
        if ($result) {
            $this->mergeDuplicateItems($customer_id);
        }

        return $result;
    }

    /**
     * Merge duplicate items in cart (when migrating guest cart)
     */
    private function mergeDuplicateItems($customer_id)
    {
        // Get duplicate items
        $sql = "SELECT p_id, SUM(qty) as total_qty
                FROM cart
                WHERE c_id = ?
                GROUP BY p_id
                HAVING COUNT(*) > 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $duplicates = $stmt->get_result();
        $stmt->close();

        // Update duplicates and remove extra entries
        while ($row = $duplicates->fetch_assoc()) {
            $p_id = $row['p_id'];
            $total_qty = $row['total_qty'];

            // Delete all but one entry
            $delete_sql = "DELETE FROM cart WHERE p_id = ? AND c_id = ? LIMIT ?";
            $delete_stmt = $this->db->prepare($delete_sql);
            $delete_stmt->bind_param('iii', $p_id, $customer_id, $total_qty - 1);
            $delete_stmt->execute();
            $delete_stmt->close();

            // Update the remaining entry
            $update_sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND c_id = ? LIMIT 1";
            $update_stmt = $this->db->prepare($update_sql);
            $update_stmt->bind_param('iii', $total_qty, $p_id, $customer_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }
}
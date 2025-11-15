<?php
require_once __DIR__ . '/../settings/db_class.php';

class OrderClass extends db_connection
{
    public function __construct()
    {
        parent::db_connect();
    }

    /**
     * Create a new order
     * @param int $customer_id The customer ID
     * @param float $total_amount Total order amount
     * @return array|false Order details or false on failure
     */
    public function createOrder($customer_id, $total_amount)
    {
        $customer_id = (int)$customer_id;
        $total_amount = (float)$total_amount;

        if ($customer_id <= 0 || $total_amount <= 0) {
            return false;
        }

        // Generate unique invoice number
        $invoice_no = $this->generateInvoiceNumber();

        // Get current date
        $order_date = date('Y-m-d');
        $order_status = 'Pending';

        // Insert order
        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iiss', $customer_id, $invoice_no, $order_date, $order_status);

        if ($stmt->execute()) {
            $order_id = $this->db->insert_id;
            $stmt->close();

            return [
                'order_id' => $order_id,
                'invoice_no' => $invoice_no,
                'order_date' => $order_date,
                'order_status' => $order_status
            ];
        }

        $stmt->close();
        return false;
    }

    /**
     * Add order details
     * @param int $order_id The order ID
     * @param int $product_id The product ID
     * @param int $quantity The quantity
     * @return bool Success status
     */
    public function addOrderDetails($order_id, $product_id, $quantity)
    {
        $order_id = (int)$order_id;
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($order_id <= 0 || $product_id <= 0 || $quantity <= 0) {
            return false;
        }

        $sql = "INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $order_id, $product_id, $quantity);

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Record payment for an order
     * @param int $order_id The order ID
     * @param int $customer_id The customer ID
     * @param float $amount Payment amount
     * @param string $currency Currency code (default: USD)
     * @return bool Success status
     */
    public function recordPayment($order_id, $customer_id, $amount, $currency = 'USD')
    {
        $order_id = (int)$order_id;
        $customer_id = (int)$customer_id;
        $amount = (float)$amount;

        if ($order_id <= 0 || $customer_id <= 0 || $amount <= 0) {
            return false;
        }

        $payment_date = date('Y-m-d');

        $sql = "INSERT INTO payment (amt, customer_id, order_id, currency, payment_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('diiss', $amount, $customer_id, $order_id, $currency, $payment_date);

        if ($stmt->execute()) {
            // Update order status to 'Paid'
            $this->updateOrderStatus($order_id, 'Paid');
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    /**
     * Update order status
     * @param int $order_id The order ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateOrderStatus($order_id, $status)
    {
        $order_id = (int)$order_id;
        $status = trim($status);

        if ($order_id <= 0 || empty($status)) {
            return false;
        }

        $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $status, $order_id);

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Get order details with products
     * @param int $order_id The order ID
     * @return array|false Order details or false on failure
     */
    public function getOrderDetails($order_id)
    {
        $order_id = (int)$order_id;

        if ($order_id <= 0) {
            return false;
        }

        // Get order info
        $sql = "SELECT o.*, c.customer_name, c.customer_email
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                WHERE o.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            return false;
        }

        // Get order items
        $sql = "SELECT od.*, p.product_title, p.product_price, p.product_image,
                       (p.product_price * od.qty) as item_total
                FROM orderdetails od
                JOIN products p ON od.product_id = p.product_id
                WHERE od.order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get payment info
        $sql = "SELECT * FROM payment WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $order['items'] = $items;
        $order['payment'] = $payment;
        $order['total_amount'] = array_sum(array_column($items, 'item_total'));

        return $order;
    }

    /**
     * Get all orders for a customer
     * @param int $customer_id The customer ID
     * @param int $limit Number of orders to retrieve
     * @param int $offset Offset for pagination
     * @return array Orders list
     */
    public function getCustomerOrders($customer_id, $limit = 10, $offset = 0)
    {
        $customer_id = (int)$customer_id;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT o.*, p.amt as payment_amount, p.payment_date
                FROM orders o
                LEFT JOIN payment p ON o.order_id = p.order_id
                WHERE o.customer_id = ?
                ORDER BY o.order_date DESC, o.order_id DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iii', $customer_id, $limit, $offset);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $orders;
    }

    /**
     * Get order count for a customer
     * @param int $customer_id The customer ID
     * @return int Number of orders
     */
    public function getCustomerOrderCount($customer_id)
    {
        $customer_id = (int)$customer_id;

        $sql = "SELECT COUNT(*) as count FROM orders WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $customer_id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        return (int)$count;
    }

    /**
     * Get all orders (for admin)
     * @param int $limit Number of orders to retrieve
     * @param int $offset Offset for pagination
     * @return array Orders list
     */
    public function getAllOrders($limit = 20, $offset = 0)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT o.*, c.customer_name, c.customer_email, p.amt as payment_amount
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                LEFT JOIN payment p ON o.order_id = p.order_id
                ORDER BY o.order_date DESC, o.order_id DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $orders;
    }

    /**
     * Generate unique invoice number
     * @return string Invoice number
     */
    private function generateInvoiceNumber()
    {
        // Format: INV-YYYYMMDD-XXXXX where XXXXX is a random 5-digit number
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $invoice = "INV-{$date}-{$random}";

        // Ensure uniqueness
        $sql = "SELECT invoice_no FROM orders WHERE invoice_no = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $invoice);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        // If exists, generate again
        if ($exists) {
            return $this->generateInvoiceNumber();
        }

        return $invoice;
    }

    /**
     * Create order from cart items
     * @param int $customer_id The customer ID
     * @param array $cart_items Cart items array
     * @return array|false Order details or false on failure
     */
    public function createOrderFromCart($customer_id, $cart_items)
    {
        if (empty($cart_items)) {
            return false;
        }

        // Calculate total amount
        $total_amount = array_sum(array_column($cart_items, 'subtotal'));

        // Start transaction
        $this->db->begin_transaction();

        try {
            // Create order
            $order = $this->createOrder($customer_id, $total_amount);
            if (!$order) {
                throw new Exception('Failed to create order');
            }

            $order_id = $order['order_id'];

            // Add order details for each cart item
            foreach ($cart_items as $item) {
                $result = $this->addOrderDetails(
                    $order_id,
                    $item['p_id'],
                    $item['qty']
                );
                if (!$result) {
                    throw new Exception('Failed to add order details');
                }
            }

            // Record payment
            $payment_result = $this->recordPayment($order_id, $customer_id, $total_amount);
            if (!$payment_result) {
                throw new Exception('Failed to record payment');
            }

            // Commit transaction
            $this->db->commit();

            return $order;

        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Search orders by invoice number or customer name
     * @param string $query Search query
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Search results
     */
    public function searchOrders($query, $limit = 20, $offset = 0)
    {
        $query = '%' . trim($query) . '%';
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT o.*, c.customer_name, c.customer_email, p.amt as payment_amount
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                LEFT JOIN payment p ON o.order_id = p.order_id
                WHERE o.invoice_no LIKE ? OR c.customer_name LIKE ? OR c.customer_email LIKE ?
                ORDER BY o.order_date DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssii', $query, $query, $query, $limit, $offset);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $orders;
    }
}
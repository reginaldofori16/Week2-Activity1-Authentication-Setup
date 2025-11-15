<?php
require_once __DIR__ . '/../classes/order_class.php';

class OrderController
{
    /**
     * Create a new order
     */
    public static function create_order_ctr($customer_id, $total_amount)
    {
        $order = new OrderClass();
        return $order->createOrder($customer_id, $total_amount);
    }

    /**
     * Add order details
     */
    public static function add_order_details_ctr($order_id, $product_id, $quantity)
    {
        $order = new OrderClass();
        return $order->addOrderDetails($order_id, $product_id, $quantity);
    }

    /**
     * Record payment
     */
    public static function record_payment_ctr($order_id, $customer_id, $amount, $currency = 'USD')
    {
        $order = new OrderClass();
        return $order->recordPayment($order_id, $customer_id, $amount, $currency);
    }

    /**
     * Update order status
     */
    public static function update_order_status_ctr($order_id, $status)
    {
        $order = new OrderClass();
        return $order->updateOrderStatus($order_id, $status);
    }

    /**
     * Get order details
     */
    public static function get_order_details_ctr($order_id)
    {
        $order = new OrderClass();
        return $order->getOrderDetails($order_id);
    }

    /**
     * Get customer orders
     */
    public static function get_customer_orders_ctr($customer_id, $limit = 10, $offset = 0)
    {
        $order = new OrderClass();
        return $order->getCustomerOrders($customer_id, $limit, $offset);
    }

    /**
     * Get customer order count
     */
    public static function get_customer_order_count_ctr($customer_id)
    {
        $order = new OrderClass();
        return $order->getCustomerOrderCount($customer_id);
    }

    /**
     * Get all orders (admin)
     */
    public static function get_all_orders_ctr($limit = 20, $offset = 0)
    {
        $order = new OrderClass();
        return $order->getAllOrders($limit, $offset);
    }

    /**
     * Search orders
     */
    public static function search_orders_ctr($query, $limit = 20, $offset = 0)
    {
        $order = new OrderClass();
        return $order->searchOrders($query, $limit, $offset);
    }

    /**
     * Process order from cart
     * Complete checkout process
     */
    public static function process_order_from_cart_ctr($customer_id, $cart_items)
    {
        $order = new OrderClass();

        // Validate cart is not empty
        if (empty($cart_items)) {
            return [
                'success' => false,
                'message' => 'Your cart is empty'
            ];
        }

        // Create order from cart
        $result = $order->createOrderFromCart($customer_id, $cart_items);

        if ($result) {
            return [
                'success' => true,
                'order_id' => $result['order_id'],
                'invoice_no' => $result['invoice_no'],
                'order_date' => $result['order_date'],
                'order_status' => $result['order_status'],
                'message' => 'Order placed successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to process order. Please try again.'
            ];
        }
    }

    /**
     * Get formatted order details for display
     */
    public static function get_formatted_order_details_ctr($order_id)
    {
        $order = new OrderClass();
        $details = $order->getOrderDetails($order_id);

        if (!$details) {
            return false;
        }

        // Format items
        $formatted_items = [];
        foreach ($details['items'] as $item) {
            $formatted_items[] = [
                'product_id' => $item['product_id'],
                'title' => htmlspecialchars($item['product_title']),
                'price' => number_format($item['product_price'], 2),
                'quantity' => $item['qty'],
                'subtotal' => number_format($item['item_total'], 2),
                'image' => $item['product_image']
            ];
        }

        return [
            'order_id' => $details['order_id'],
            'invoice_no' => $details['invoice_no'],
            'order_date' => date('F j, Y', strtotime($details['order_date'])),
            'order_status' => $details['order_status'],
            'customer_name' => htmlspecialchars($details['customer_name']),
            'customer_email' => htmlspecialchars($details['customer_email']),
            'items' => $formatted_items,
            'subtotal' => number_format($details['total_amount'], 2),
            'tax' => number_format($details['total_amount'] * 0.1, 2), // 10% tax
            'total' => number_format($details['total_amount'] * 1.1, 2), // subtotal + tax
            'payment' => $details['payment'] ? [
                'amount' => number_format($details['payment']['amt'], 2),
                'currency' => $details['payment']['currency'],
                'payment_date' => date('F j, Y', strtotime($details['payment']['payment_date']))
            ] : null
        ];
    }

    /**
     * Get formatted customer orders with pagination
     */
    public static function get_formatted_customer_orders_ctr($customer_id, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $order = new OrderClass();

        $orders = $order->getCustomerOrders($customer_id, $limit, $offset);
        $total_orders = $order->getCustomerOrderCount($customer_id);

        $formatted_orders = [];
        foreach ($orders as $order) {
            $formatted_orders[] = [
                'order_id' => $order['order_id'],
                'invoice_no' => $order['invoice_no'],
                'order_date' => date('M j, Y', strtotime($order['order_date'])),
                'order_status' => $order['order_status'],
                'total_amount' => number_format($order['payment_amount'] ?? 0, 2),
                'payment_date' => $order['payment_date'] ? date('M j, Y', strtotime($order['payment_date'])) : null
            ];
        }

        return [
            'orders' => $formatted_orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total_orders / $limit),
                'total_orders' => $total_orders,
                'has_next' => $page < ceil($total_orders / $limit),
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Get dashboard statistics for admin
     */
    public static function get_order_statistics_ctr()
    {
        // This would require additional SQL queries for statistics
        // For now, returning placeholder data
        return [
            'total_orders' => 0,
            'total_revenue' => 0,
            'pending_orders' => 0,
            'completed_orders' => 0,
            'recent_orders' => []
        ];
    }
}
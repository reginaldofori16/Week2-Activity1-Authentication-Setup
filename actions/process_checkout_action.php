<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';

// Set response header
header('Content-Type: application/json');

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Checkout failed'
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to checkout');
    }

    $customer_id = $_SESSION['user_id'];

    // Validate cart
    $validation = CartController::validate_cart_before_checkout_ctr();
    if (!$validation['valid']) {
        throw new Exception($validation['message']);
    }

    // Get cart items
    $cart_items = CartController::get_cart_items_ctr();
    if (empty($cart_items)) {
        throw new Exception('Your cart is empty');
    }

    // Process order
    $order_result = OrderController::process_order_from_cart_ctr($customer_id, $cart_items);

    if ($order_result['success']) {
        // Empty cart after successful order
        CartController::empty_cart_ctr();

        // Get order details for confirmation
        $order_details = OrderController::get_formatted_order_details_ctr($order_result['order_id']);

        $response = [
            'status' => 'success',
            'message' => 'Order placed successfully',
            'order_id' => $order_result['order_id'],
            'invoice_no' => $order_result['invoice_no'],
            'order_date' => $order_details['order_date'],
            'total_amount' => $order_details['total'],
            'order_details' => $order_details
        ];
    } else {
        throw new Exception($order_result['message']);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
echo json_encode($response);
exit;
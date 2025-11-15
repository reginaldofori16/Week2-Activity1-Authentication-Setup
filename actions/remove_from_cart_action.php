<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Set response header
header('Content-Type: application/json');

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Failed to remove item from cart'
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input
    $product_id = $_POST['product_id'] ?? null;

    if (empty($product_id) || !is_numeric($product_id)) {
        throw new Exception('Invalid product ID');
    }

    // Remove from cart
    $result = CartController::remove_from_cart_ctr($product_id);

    if ($result) {
        // Get updated cart count and summary
        $cart_count = CartController::get_cart_item_count_ctr();
        $cart_summary = CartController::get_cart_summary_ctr();

        $response = [
            'status' => 'success',
            'message' => 'Item removed from cart successfully',
            'cart_count' => $cart_count,
            'cart_total' => number_format($cart_summary['total_amount'], 2),
            'product_id' => $product_id
        ];
    } else {
        throw new Exception('Failed to remove item from cart');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
echo json_encode($response);
exit;
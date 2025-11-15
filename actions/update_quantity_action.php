<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Set response header
header('Content-Type: application/json');

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Failed to update quantity'
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    if (empty($product_id) || !is_numeric($product_id)) {
        throw new Exception('Invalid product ID');
    }

    if (empty($quantity) || !is_numeric($quantity) || $quantity < 1) {
        throw new Exception('Invalid quantity');
    }

    // Limit quantity to prevent abuse
    $quantity = min($quantity, 100);

    // Update quantity
    $result = CartController::update_cart_quantity_ctr($product_id, $quantity);

    if ($result) {
        // Get updated cart summary
        $cart_summary = CartController::get_cart_summary_ctr();

        // Get product details to calculate new subtotal
        $cart_items = CartController::get_cart_items_ctr();
        $item_subtotal = 0;
        foreach ($cart_items as $item) {
            if ($item['p_id'] == $product_id) {
                $item_subtotal = $item['product_price'] * $quantity;
                break;
            }
        }

        $response = [
            'status' => 'success',
            'message' => 'Quantity updated successfully',
            'cart_count' => $cart_summary['item_count'],
            'cart_total' => number_format($cart_summary['total_amount'], 2),
            'item_subtotal' => number_format($item_subtotal, 2),
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
    } else {
        throw new Exception('Failed to update quantity');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
echo json_encode($response);
exit;
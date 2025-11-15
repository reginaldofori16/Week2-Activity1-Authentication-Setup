<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Set response header
header('Content-Type: application/json');

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Failed to add product to cart'
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;

    if (empty($product_id) || !is_numeric($product_id)) {
        throw new Exception('Invalid product ID');
    }

    if (empty($quantity) || !is_numeric($quantity) || $quantity < 1) {
        $quantity = 1;
    }

    // Limit quantity to prevent abuse
    $quantity = min($quantity, 100);

    // Add to cart
    $result = CartController::add_to_cart_ctr($product_id, $quantity);

    if ($result) {
        // Get updated cart count
        $cart_count = CartController::get_cart_item_count_ctr();

        $response = [
            'status' => 'success',
            'message' => 'Product added to cart successfully',
            'cart_count' => $cart_count,
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
    } else {
        throw new Exception('Failed to add product to cart');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
echo json_encode($response);
exit;
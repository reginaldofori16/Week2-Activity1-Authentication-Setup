<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Set response header
header('Content-Type: application/json');

// Get cart items with summary
$cartData = CartController::get_formatted_cart_items_ctr();

if (empty($cartData['items'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Your cart is empty'
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'data' => $cartData
    ]);
}

exit;
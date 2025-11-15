<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Set response header
header('Content-Type: application/json');

// Get cart count
$count = CartController::get_cart_item_count_ctr();

// Send response
echo json_encode([
    'status' => 'success',
    'cart_count' => $count
]);

exit;
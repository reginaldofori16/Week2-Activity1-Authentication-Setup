<?php
// Start session
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Set response header
header('Content-Type: application/json');

// Initialize response
$response = [
    'status' => 'error',
    'message' => 'Failed to empty cart'
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Empty cart
    $result = CartController::empty_cart_ctr();

    if ($result) {
        $response = [
            'status' => 'success',
            'message' => 'Cart emptied successfully',
            'cart_count' => 0,
            'cart_total' => '0.00'
        ];
    } else {
        throw new Exception('Failed to empty cart');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
echo json_encode($response);
exit;
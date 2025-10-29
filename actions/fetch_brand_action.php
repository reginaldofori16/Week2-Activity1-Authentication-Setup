<?php
// Include core helpers and brand controller for handling brand retrieval
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Set response header to JSON
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Ensure session is started and user is admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Fetch all brands
$brands = BrandController::get_all_brands();

if ($brands) {
    // Return brands as JSON
    echo json_encode($brands);
} else {
    // Return empty array if no brands found
    echo json_encode([]);
}

exit();
?>
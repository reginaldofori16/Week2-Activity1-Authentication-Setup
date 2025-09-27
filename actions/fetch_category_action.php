<?php
// Include core helpers and category controller for fetching categories
require_once __DIR__ . '/../settings/core.php';
include_once 'category_controller.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch categories for the logged-in admin
if (is_logged_in() && is_admin()) {
    // Fetch all categories (categories table contains cat_id, cat_name)
    $categories = CategoryController::get_all_categories();
    
    // Return categories as JSON response (if needed for AJAX)
    header('Content-Type: application/json');
    echo json_encode($categories);
} else {
    header('Content-Type: application/json');
    echo json_encode([]);  // Return an empty array if not logged in or not an admin
}
?>

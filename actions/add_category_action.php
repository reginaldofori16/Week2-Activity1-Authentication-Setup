<?php
// Include core helpers and category controller for handling category creation
require_once __DIR__ . '/../settings/core.php';
include_once 'category_controller.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get category name from the form
    $category_name = $_POST['category_name'];

    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($category_name)) {
        echo 'Category name cannot be empty!';
        exit();
    }

    // Only admins can add categories
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 1) {
        echo 'Unauthorized';
        exit();
    }

    // Check if the category name already exists
    if (CategoryController::category_exists($category_name)) {
        echo 'Category name already exists! Please choose another name.';
    } else {
        // Add category to the database
        $result = CategoryController::add_category($category_name);
        echo $result ? 'Category added successfully!' : 'Category creation failed!';
    }
}
?>

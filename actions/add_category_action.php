<?php
// Include core helpers and category controller for handling category creation
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get category name from the form
    $category_name = $_POST['category_name'];

    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($category_name)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Category name cannot be empty']);
            exit();
        }
        redirect_to('admin/category.php?status=error&msg=' . urlencode('Category name cannot be empty'));
        exit();
    }

    // Only admins can add categories
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 1) {
        if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit(); }
        redirect_to('admin/category.php?status=error&msg=' . urlencode('Unauthorized'));
        exit();
    }

    // Check if the category name already exists
    if (CategoryController::category_exists($category_name)) {
        if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Category name already exists']); exit(); }
        redirect_to('admin/category.php?status=error&msg=' . urlencode('Category name already exists'));
        exit();
    } else {
        // Add category to the database
        $result = CategoryController::add_category($category_name);
        if ($isAjax) {
            if ($result) echo json_encode(['status' => 'success', 'message' => 'Category added']);
            else {
                // try to include DB error if available
                $dbErr = null;
                if (method_exists('\CategoryClass', 'get_last_error')) {
                    $dbErr = \CategoryClass::get_last_error();
                }
                echo json_encode(['status' => 'error', 'message' => 'Category creation failed', 'db_error' => $dbErr]);
            }
            exit();
        }
        if ($result) {
            redirect_to('admin/category.php?status=success');
        } else {
            redirect_to('admin/category.php?status=error&msg=' . urlencode('Category creation failed'));
        }
        exit();
    }
}
?>

<?php
// Include core helpers and brand controller for handling brand deletion
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Set response header to JSON
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get brand ID from the form
    $brand_id = $_POST['brand_id'];

    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($brand_id)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Brand ID is required']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand ID is required'));
        exit();
    }

    // Only admins can delete brands
    if (!is_logged_in() || !is_admin()) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Unauthorized'));
        exit();
    }

    // Check if brand exists
    $existing_brand = BrandController::get_brand_by_id($brand_id);
    if (!$existing_brand) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Brand not found']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand not found'));
        exit();
    }

    // Delete brand from the database
    $result = BrandController::delete_brand($brand_id);
    if ($isAjax) {
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Brand deleted successfully']);
        } else {
            // try to include DB error if available
            $dbErr = null;
            if (method_exists('\BrandClass', 'get_last_error')) {
                $dbErr = \BrandClass::get_last_error();
            }
            echo json_encode(['status' => 'error', 'message' => $dbErr ?: 'Brand deletion failed', 'db_error' => $dbErr]);
        }
        exit();
    }
    if ($result) {
        redirect_to('admin/brand.php?status=success');
    } else {
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand deletion failed'));
    }
    exit();
}
?>
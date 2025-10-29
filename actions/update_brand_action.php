<?php
// Include core helpers and brand controller for handling brand updates
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Set response header to JSON
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get brand data from the form
    $brand_id = $_POST['brand_id'];
    $brand_name = $_POST['brand_name'];

    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($brand_id) || empty($brand_name)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Brand ID and name are required']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand ID and name are required'));
        exit();
    }

    // Only admins can update brands
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

    // Check if the new brand name already exists (for a different brand)
    $existing_with_name = BrandController::brand_exists($brand_name);
    if ($existing_with_name && $existing_with_name['brand_id'] != $brand_id) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Brand name already exists']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand name already exists'));
        exit();
    }

    // Update brand in the database
    $result = BrandController::update_brand($brand_id, $brand_name);
    if ($isAjax) {
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Brand updated successfully']);
        } else {
            // try to include DB error if available
            $dbErr = null;
            if (method_exists('\BrandClass', 'get_last_error')) {
                $dbErr = \BrandClass::get_last_error();
            }
            echo json_encode(['status' => 'error', 'message' => 'Brand update failed', 'db_error' => $dbErr]);
        }
        exit();
    }
    if ($result) {
        redirect_to('admin/brand.php?status=success');
    } else {
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand update failed'));
    }
    exit();
}
?>
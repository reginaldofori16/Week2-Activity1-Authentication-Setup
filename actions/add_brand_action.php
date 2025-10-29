<?php
// Include core helpers and brand controller for handling brand creation
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Set response header to JSON
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get brand name from the form
    $brand_name = $_POST['brand_name'];

    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($brand_name)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Brand name cannot be empty']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand name cannot be empty'));
        exit();
    }

    // Only admins can add brands
    if (!is_logged_in() || !is_admin()) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Unauthorized'));
        exit();
    }

    // Check if the brand name already exists
    if (BrandController::brand_exists($brand_name)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Brand name already exists']);
            exit();
        }
        redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand name already exists'));
        exit();
    } else {
        // Add brand to the database
        $result = BrandController::add_brand($brand_name);
        if ($isAjax) {
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Brand added successfully']);
            } else {
                // try to include DB error if available
                $dbErr = null;
                if (method_exists('\BrandClass', 'get_last_error')) {
                    $dbErr = \BrandClass::get_last_error();
                }
                echo json_encode(['status' => 'error', 'message' => 'Brand creation failed', 'db_error' => $dbErr]);
            }
            exit();
        }
        if ($result) {
            redirect_to('admin/brand.php?status=success');
        } else {
            redirect_to('admin/brand.php?status=error&msg=' . urlencode('Brand creation failed'));
        }
        exit();
    }
}
?>
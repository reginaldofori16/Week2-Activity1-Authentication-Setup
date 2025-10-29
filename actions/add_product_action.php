<?php
// Include core helpers and product controller for handling product creation
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';

// Set response header to JSON
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get product data from the form
    $product_cat = $_POST['product_category'];
    $product_brand = $_POST['product_brand'];
    $product_title = $_POST['product_title'];
    $product_price = $_POST['product_price'];
    $product_desc = $_POST['product_desc'] ?? null;
    $product_keywords = $_POST['product_keywords'] ?? null;

    // Ensure session is started and user is admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validate required fields
    if (empty($product_cat) || empty($product_brand) || empty($product_title) || empty($product_price)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('All required fields must be filled'));
        exit();
    }

    // Validate price
    if (!is_numeric($product_price) || $product_price <= 0) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Price must be a positive number']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Price must be a positive number'));
        exit();
    }

    // Only admins can add products
    if (!is_logged_in() || !is_admin()) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Unauthorized'));
        exit();
    }

    // Check if the product title already exists
    if (ProductController::product_exists($product_title)) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Product title already exists']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Product title already exists'));
        exit();
    }

    // Handle image upload if present
    $product_image = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        // Include the upload handler
        require_once __DIR__ . '/upload_product_image_action.php';

        // For new products, we don't have a product_id yet, so we'll use a temporary name
        // The upload will be handled after product creation
        $upload_result = handle_product_image_upload('product_image', null, true);
        if ($upload_result['status'] === 'success') {
            $product_image = $upload_result['file_path'];
        } else {
            if ($isAjax) {
                echo json_encode(['status' => 'error', 'message' => $upload_result['message']]);
                exit();
            }
            redirect_to('admin/product.php?status=error&msg=' . urlencode($upload_result['message']));
            exit();
        }
    }

    // Add product to the database
    $result = ProductController::add_product(
        $product_cat,
        $product_brand,
        $product_title,
        $product_price,
        $product_desc,
        $product_image,
        $product_keywords
    );

    if ($isAjax) {
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully']);
        } else {
            // try to include DB error if available
            $dbErr = null;
            if (method_exists('\ProductClass', 'get_last_error')) {
                $dbErr = \ProductClass::get_last_error();
            }
            echo json_encode(['status' => 'error', 'message' => 'Product creation failed', 'db_error' => $dbErr]);
        }
        exit();
    }

    if ($result) {
        redirect_to('admin/product.php?status=success');
    } else {
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Product creation failed'));
    }
    exit();
}
?>
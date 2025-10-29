<?php
// Include core helpers and product controller for handling product updates
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';

// Set response header to JSON
header('Content-Type: application/json');

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get product data from the form
    $product_id = $_POST['product_id'];
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
    if (empty($product_id) || empty($product_cat) || empty($product_brand) || empty($product_title) || empty($product_price)) {
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

    // Only admins can update products
    if (!is_logged_in() || !is_admin()) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Unauthorized'));
        exit();
    }

    // Check if product exists
    $existing_product = ProductController::get_product_by_id($product_id);
    if (!$existing_product) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Product not found'));
        exit();
    }

    // Check if the new product title already exists (for a different product)
    $existing_with_title = ProductController::product_exists($product_title);
    if ($existing_with_title && $existing_with_title['product_id'] != $product_id) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Product title already exists']);
            exit();
        }
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Product title already exists'));
        exit();
    }

    // Handle image upload if present
    $product_image = $existing_product['product_image']; // Keep existing image by default
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        // Include the upload handler
        require_once __DIR__ . '/upload_product_image_action.php';

        $upload_result = handle_product_image_upload('product_image', $product_id);
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

    // Update product in the database
    $result = ProductController::update_product(
        $product_id,
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
            echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
        } else {
            // try to include DB error if available
            $dbErr = null;
            if (method_exists('\ProductClass', 'get_last_error')) {
                $dbErr = \ProductClass::get_last_error();
            }
            echo json_encode(['status' => 'error', 'message' => 'Product update failed', 'db_error' => $dbErr]);
        }
        exit();
    }

    if ($result) {
        redirect_to('admin/product.php?status=success');
    } else {
        redirect_to('admin/product.php?status=error&msg=' . urlencode('Product update failed'));
    }
    exit();
}
?>
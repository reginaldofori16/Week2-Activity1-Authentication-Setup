<?php
// Include core helpers and product controller for handling product operations
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/category_controller.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Set response header to JSON for AJAX requests
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get action parameter
$action = $_GET['action'] ?? '';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Initialize response
$response = ['status' => 'error', 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'view_all':
            // View all products with pagination
            $result = ProductController::view_all_products_ctr($limit, $offset);
            $response = [
                'status' => 'success',
                'data' => $result['products'],
                'total' => $result['total'],
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($result['total'] / $limit)
            ];
            break;

        case 'search':
            // Search products
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                $response = ['status' => 'error', 'message' => 'Search query is required'];
            } else {
                $result = ProductController::search_products_ctr($query, $limit, $offset);
                $response = [
                    'status' => 'success',
                    'data' => $result['products'],
                    'total' => $result['total'],
                    'query' => $query,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($result['total'] / $limit)
                ];
            }
            break;

        case 'filter_by_category':
            // Filter products by category
            $cat_id = $_GET['cat_id'] ?? null;
            if (!$cat_id) {
                $response = ['status' => 'error', 'message' => 'Category ID is required'];
            } else {
                $result = ProductController::filter_products_by_category_ctr($cat_id, $limit, $offset);
                $response = [
                    'status' => 'success',
                    'data' => $result['products'],
                    'total' => $result['total'],
                    'cat_id' => $cat_id,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($result['total'] / $limit)
                ];
            }
            break;

        case 'filter_by_brand':
            // Filter products by brand
            $brand_id = $_GET['brand_id'] ?? null;
            if (!$brand_id) {
                $response = ['status' => 'error', 'message' => 'Brand ID is required'];
            } else {
                $result = ProductController::filter_products_by_brand_ctr($brand_id, $limit, $offset);
                $response = [
                    'status' => 'success',
                    'data' => $result['products'],
                    'total' => $result['total'],
                    'brand_id' => $brand_id,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($result['total'] / $limit)
                ];
            }
            break;

        case 'filter_composite':
            // Composite filter (category, brand, price range)
            $cat_id = $_GET['cat_id'] ?? null;
            $brand_id = $_GET['brand_id'] ?? null;
            $min_price = $_GET['min_price'] ?? null;
            $max_price = $_GET['max_price'] ?? null;
            $search_query = $_GET['q'] ?? null;

            $result = ProductController::filter_products_composite_ctr(
                $cat_id,
                $brand_id,
                $min_price,
                $max_price,
                $limit,
                $offset
            );

            $response = [
                'status' => 'success',
                'data' => $result['products'],
                'total' => $result['total'],
                'filters' => [
                    'cat_id' => $cat_id,
                    'brand_id' => $brand_id,
                    'min_price' => $min_price,
                    'max_price' => $max_price,
                    'query' => $search_query
                ],
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($result['total'] / $limit)
            ];
            break;

        case 'get_single':
            // Get single product details
            $product_id = $_GET['id'] ?? null;
            if (!$product_id) {
                $response = ['status' => 'error', 'message' => 'Product ID is required'];
            } else {
                $product = ProductController::view_single_product_ctr($product_id);
                if ($product) {
                    $response = [
                        'status' => 'success',
                        'data' => $product
                    ];
                } else {
                    $response = ['status' => 'error', 'message' => 'Product not found'];
                }
            }
            break;

        case 'get_filters':
            // Get all categories and brands for filter dropdowns
            $categories = CategoryController::get_all_categories();
            $brands = BrandController::get_all_brands();
            $response = [
                'status' => 'success',
                'data' => [
                    'categories' => $categories,
                    'brands' => $brands
                ]
            ];
            break;

        default:
            $response = ['status' => 'error', 'message' => 'Unknown action'];
            break;
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ];
}

// Send JSON response
echo json_encode($response);
exit();
?>
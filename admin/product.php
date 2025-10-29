<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    redirect_to(LOGIN_PAGE);  // Redirect to login if not logged in or not an admin
    exit;
}

// Load controllers for categories and brands
require_once __DIR__ . '/../controllers/category_controller.php';
require_once __DIR__ . '/../controllers/brand_controller.php';
require_once __DIR__ . '/../controllers/product_controller.php';

// Get categories and brands for dropdowns
$categories = CategoryController::get_all_categories();
$brands = BrandController::get_all_brands();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: start;
        }
        .form-group {
            flex: 1;
        }
        .form-group.full-width {
            width: 100%;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .product-image {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .actions {
            white-space: nowrap;
        }
        .actions button {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 12px;
        }
        .back-link {
            margin-bottom: 20px;
            display: inline-block;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .image-preview {
            margin-top: 10px;
            max-width: 200px;
            max-height: 200px;
            display: none;
        }
        .edit-mode {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <a href="../index.php" class="back-link">← Back to Home</a>

    <h1>Product Management</h1>

    <!-- Add/Edit Product Form -->
    <div class="form-container">
        <h2 id="form-title">Add New Product</h2>
        <form method="post" id="product-form" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id" value="">

            <div class="form-row">
                <div class="form-group">
                    <label for="product_category">Category</label>
                    <select name="product_category" id="product_category" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['cat_id']; ?>"><?php echo htmlspecialchars($category['cat_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="product_brand">Brand</label>
                    <select name="product_brand" id="product_brand" required>
                        <option value="">Select Brand</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_title">Product Title</label>
                    <input type="text" name="product_title" id="product_title" required>
                </div>
                <div class="form-group">
                    <label for="product_price">Price</label>
                    <input type="number" name="product_price" id="product_price" step="0.01" min="0" required>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="product_desc">Description</label>
                <textarea name="product_desc" id="product_desc"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_keywords">Keywords</label>
                    <input type="text" name="product_keywords" id="product_keywords" placeholder="e.g., shoes, running, athletic">
                </div>
                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <input type="file" name="product_image" id="product_image" accept="image/*">
                    <img id="image-preview" class="image-preview" alt="Image preview">
                </div>
            </div>

            <div class="form-row">
                <button type="submit" class="btn-primary" id="submit-btn">Add Product</button>
                <button type="button" class="btn-warning" id="cancel-edit-btn" style="display:none;">Cancel Edit</button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <h2>Products List</h2>
        <table id="products-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                <!-- populated by js/product.js -->
            </tbody>
        </table>
    </div>

</body>

</html>

<!-- Include jQuery, SweetAlert2, and product JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // API endpoints used by the JS; generated server-side so paths follow BASE_URL
    var PRODUCT_ENDPOINTS = {
        fetch: '../actions/fetch_product_action.php',
        add: '../actions/add_product_action.php',
        update: '../actions/update_product_action.php',
        delete: '../actions/delete_product_action.php',
        upload: '../actions/upload_product_image_action.php'
    };

    // Categories and brands data for dynamic filtering
    var CATEGORIES = <?php echo json_encode($categories); ?>;
    var BRANDS = <?php echo json_encode($brands); ?>;
</script>
<script src="../js/product.js"></script>
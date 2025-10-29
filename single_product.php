<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/settings/core.php';
require_once __DIR__ . '/controllers/product_controller.php';

// Get product ID from URL
$product_id = $_GET['id'] ?? null;

// Initialize variables
$product = null;
$error_message = '';

if ($product_id) {
    $product = ProductController::view_single_product_ctr($product_id);
    if (!$product) {
        $error_message = 'Product not found';
    }
} else {
    $error_message = 'No product specified';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['product_title']) : 'Product Not Found'; ?> - Our Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .product-image-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }
        .product-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            padding: 20px;
        }
        .price-display {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        .breadcrumb {
            background-color: transparent;
            padding: 0;
        }
        .badge-category {
            background-color: #6c757d;
        }
        .badge-brand {
            background-color: #17a2b8;
        }
        .keywords {
            font-style: italic;
            color: #6c757d;
        }
        .btn-cart {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-cart:hover {
            background-color: #0056b3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .product-meta {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .no-image {
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #ccc;
        }
        .back-link {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #007bff;
        }
        .product-description {
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .out-of-stock {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Our Store
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="all_product.php">All Products</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1): ?>
                                    <li><a class="dropdown-item" href="admin/category.php">Category Management</a></li>
                                    <li><a class="dropdown-item" href="admin/brand.php">Brand Management</a></li>
                                    <li><a class="dropdown-item" href="admin/product.php">Add Product</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="actions/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login/register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error_message): ?>
            <!-- Error Message -->
            <div class="alert alert-danger text-center" style="margin-top: 100px;">
                <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
                <h4 class="mt-3"><?php echo htmlspecialchars($error_message); ?></h4>
                <p class="mt-3">
                    <a href="all_product.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Back to Products
                    </a>
                </p>
            </div>
        <?php else: ?>
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php" class="text-decoration-none">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="all_product.php" class="text-decoration-none">All Products</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($product['product_title']); ?>
                    </li>
                </ol>
            </nav>

            <!-- Product Details -->
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4">
                    <div class="product-image-container">
                        <?php if ($product['product_image']): ?>
                            <img src="<?php echo htmlspecialchars($product['product_image']); ?>"
                                 class="product-image"
                                 alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'no-image\\'><i class=\\'bi bi-image\\' style=\\'font-size: 5rem;\\'></i></div>'">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="bi bi-image" style="font-size: 5rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="col-lg-6">
                    <h1 class="mb-3"><?php echo htmlspecialchars($product['product_title']); ?></h1>

                    <!-- Badges -->
                    <div class="mb-3">
                        <span class="badge badge-category me-2">
                            <i class="bi bi-tag"></i> <?php echo htmlspecialchars($product['cat_name'] ?: 'No Category'); ?>
                        </span>
                        <span class="badge badge-brand">
                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($product['brand_name'] ?: 'No Brand'); ?>
                        </span>
                    </div>

                    <!-- Price -->
                    <div class="price-display mb-4">
                        $<?php echo number_format($product['product_price'], 2); ?>
                    </div>

                    <!-- Product Meta Information -->
                    <div class="product-meta">
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>Product ID:</strong><br>
                                <span class="text-muted">#<?php echo $product['product_id']; ?></span>
                            </div>
                            <div class="col-sm-6">
                                <strong>Availability:</strong><br>
                                <span class="text-success">
                                    <i class="bi bi-check-circle"></i> In Stock
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p class="product-description">
                            <?php echo $product['product_desc']
                                ? htmlspecialchars($product['product_desc'])
                                : '<span class="text-muted">No description available</span>'; ?>
                        </p>
                    </div>

                    <!-- Keywords -->
                    <?php if ($product['product_keywords']): ?>
                        <div class="mb-4">
                            <h5>Keywords</h5>
                            <p class="keywords mb-0">
                                <?php echo htmlspecialchars($product['product_keywords']); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Add to Cart Button -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-cart btn-lg" id="addToCartBtn">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-heart"></i> Add to Wishlist
                        </button>
                    </div>

                    <!-- Additional Information -->
                    <div class="mt-4 pt-4 border-top">
                        <div class="row text-center">
                            <div class="col-4">
                                <i class="bi bi-truck" style="font-size: 1.5rem; color: #007bff;"></i>
                                <p class="small mt-2 mb-0">Free Shipping</p>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-shield-check" style="font-size: 1.5rem; color: #28a745;"></i>
                                <p class="small mt-2 mb-0">Secure Payment</p>
                            </div>
                            <div class="col-4">
                                <i class="bi bi-arrow-clockwise" style="font-size: 1.5rem; color: #ffc107;"></i>
                                <p class="small mt-2 mb-0">30-Day Returns</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products Section (Placeholder) -->
            <div class="mt-5 pt-5 border-top">
                <h3 class="mb-4">Related Products</h3>
                <div class="text-center text-muted py-5">
                    <p>Related products will be displayed here</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Our Store. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Add to Cart functionality
            $('#addToCartBtn').on('click', function() {
                const productId = <?php echo $product_id ? $product_id : 'null'; ?>;

                if (!productId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid product'
                    });
                    return;
                }

                // Disable button temporarily
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');

                // Simulate adding to cart (replace with actual AJAX call when cart is implemented)
                setTimeout(function() {
                    btn.prop('disabled', false).html(originalText);

                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: 'Product has been added to your cart',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 1000);
            });

            // Wishlist functionality (placeholder)
            $('.btn-outline-secondary').on('click', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Coming Soon',
                    text: 'Wishlist feature will be available soon!',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        });
    </script>
</body>
</html>
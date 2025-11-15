<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/settings/core.php';
require_once __DIR__ . '/controllers/cart_controller.php';

// Get cart items
$cartData = CartController::get_formatted_cart_items_ctr();
$cartItems = $cartData['items'];
$cartSummary = $cartData['summary'];

// Page title
$pageTitle = 'Shopping Cart - Our Store';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .no-image {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #ccc;
            border-radius: 8px;
        }
        .qty-input {
            width: 60px;
            text-align: center;
        }
        .cart-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            position: sticky;
            top: 20px;
        }
        .btn-remove {
            transition: all 0.3s;
        }
        .btn-remove:hover {
            background-color: #dc3545;
            color: white;
        }
        .empty-cart-illustration {
            font-size: 6rem;
            color: #dee2e6;
        }
        .qty-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">
                            <i class="bi bi-cart3"></i> Cart
                            <span class="badge bg-danger cart-badge ms-1" style="display: none;">0</span>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 1): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/category.php">Category Management</a></li>
                                    <li><a class="dropdown-item" href="admin/brand.php">Brand Management</a></li>
                                    <li><a class="dropdown-item" href="admin/product.php">Add Product</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
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
        <h1 class="mb-4">Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="cart-items-container">
                <div class="text-center py-5">
                    <i class="bi bi-cart-x empty-cart-illustration"></i>
                    <h4 class="mt-3">Your cart is empty</h4>
                    <p class="text-muted">Looks like you haven't added any products to your cart yet</p>
                    <a href="all_product.php" class="btn btn-primary btn-lg mt-3">
                        <i class="bi bi-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-items-container">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="card mb-3" id="cart-item-<?php echo $item['p_id']; ?>">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <!-- Product Image -->
                                        <div class="col-md-2">
                                            <?php if ($item['product_image']): ?>
                                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>"
                                                     class="cart-item-image"
                                                     alt="<?php echo htmlspecialchars($item['product_title']); ?>">
                                            <?php else: ?>
                                                <div class="no-image">
                                                    <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Product Details -->
                                        <div class="col-md-4">
                                            <h5 class="card-title mb-1">
                                                <?php echo htmlspecialchars($item['product_title']); ?>
                                            </h5>
                                            <p class="text-muted mb-1">
                                                <?php echo htmlspecialchars($item['category']); ?> • <?php echo htmlspecialchars($item['brand']); ?>
                                            </p>
                                            <p class="mb-0">
                                                <strong>$<?php echo number_format($item['product_price'], 2); ?></strong>
                                            </p>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-md-3">
                                            <label class="form-label">Quantity</label>
                                            <div class="input-group">
                                                <button class="btn btn-outline-secondary qty-btn qty-decrease" type="button">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" class="form-control qty-input"
                                                       value="<?php echo $item['qty']; ?>"
                                                       min="1" max="100"
                                                       data-product-id="<?php echo $item['p_id']; ?>">
                                                <button class="btn btn-outline-secondary qty-btn qty-increase" type="button">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Subtotal and Actions -->
                                        <div class="col-md-3 text-end">
                                            <h5 class="mb-2" id="subtotal-<?php echo $item['p_id']; ?>">
                                                $<?php echo number_format($item['subtotal'], 2); ?>
                                            </h5>
                                            <button class="btn btn-sm btn-outline-danger btn-remove-item"
                                                    data-product-id="<?php echo $item['p_id']; ?>">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Cart Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="all_product.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Continue Shopping
                            </a>
                            <button class="btn btn-outline-danger" id="emptyCartBtn">
                                <i class="bi bi-trash"></i> Empty Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="mb-4">Order Summary</h4>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (<?php echo $cartSummary['item_count']; ?> items)</span>
                                <strong class="cart-subtotal">$<?php echo number_format($cartSummary['total_amount'], 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%)</span>
                                <strong class="cart-tax">$<?php echo number_format($cartSummary['total_amount'] * 0.1, 2); ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5>Total</h5>
                                <h5 class="cart-grand-total">$<?php echo number_format($cartSummary['total_amount'] * 1.1, 2); ?></h5>
                            </div>
                        </div>

                        <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                            <i class="bi bi-lock"></i> Proceed to Checkout
                        </a>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i> Secure Checkout
                            </small>
                        </div>
                    </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/cart.js"></script>
</body>
</html>
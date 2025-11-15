<?php
// Require login for checkout
require_once __DIR__ . '/settings/core.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    redirect_to('login/login.php');
}

require_once __DIR__ . '/controllers/cart_controller.php';

// Get cart data
$cartData = CartController::get_formatted_cart_items_ctr();

// Redirect to cart if empty
if (empty($cartData['items'])) {
    redirect_to('cart.php');
}

$pageTitle = 'Checkout - Our Store';
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
        .checkout-header {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .progress-step {
            text-align: center;
            flex: 1;
        }
        .progress-step.active {
            color: #007bff;
        }
        .progress-step.completed {
            color: #28a745;
        }
        .checkout-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            position: sticky;
            top: 20px;
        }
        .payment-method-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method-card:hover {
            border-color: #007bff;
            background-color: #f0f8ff;
        }
        .payment-method-card.selected {
            border-color: #007bff;
            background-color: #f0f8ff;
        }
        .card-icon {
            font-size: 2rem;
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
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart3"></i> Cart
                            <span class="badge bg-danger cart-badge ms-1" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Checkout</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Checkout Header -->
    <div class="checkout-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="d-flex justify-content-center">
                        <div class="progress-step completed">
                            <i class="bi bi-check-circle-fill"></i>
                            <p class="mb-0">Cart</p>
                        </div>
                        <div class="progress-step active">
                            <i class="bi bi-circle-fill"></i>
                            <p class="mb-0">Checkout</p>
                        </div>
                        <div class="progress-step">
                            <i class="bi bi-circle"></i>
                            <p class="mb-0">Confirmation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <h3 class="mb-4">Billing Information</h3>

                <form id="checkoutForm">
                    <!-- Contact Information -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-person"></i> Contact Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" placeholder="+1 (555) 123-4567">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-geo-alt"></i> Shipping Address
                            </h5>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" class="form-control" placeholder="123 Main Street" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" placeholder="New York" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" placeholder="NY" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" placeholder="10001" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-credit-card"></i> Payment Method
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card payment-method-card h-100" data-method="card">
                                        <div class="card-body text-center">
                                            <i class="bi bi-credit-card-2-back card-icon text-primary"></i>
                                            <h6 class="mt-2">Credit/Debit Card</h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="cardMethod" value="card" checked>
                                                <label class="form-check-label" for="cardMethod"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card payment-method-card h-100" data-method="paypal">
                                        <div class="card-body text-center">
                                            <i class="bi bi-paypal card-icon text-info"></i>
                                            <h6 class="mt-2">PayPal</h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="paypalMethod" value="paypal">
                                                <label class="form-check-label" for="paypalMethod"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Details Form -->
                            <div id="cardPaymentForm" class="mt-4">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" id="cardNumber">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Cardholder Name</label>
                                        <input type="text" class="form-control" placeholder="John Doe" id="cardName">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" id="cardExpiry">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" placeholder="123" maxlength="4" id="cardCVV">
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Form (Hidden by default) -->
                            <div id="paypalPaymentForm" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> You will be redirected to PayPal to complete your payment after clicking "Place Order".
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" id="simulatePaymentBtn">
                            <i class="bi bi-shield-lock"></i> Simulate Payment
                        </button>
                        <p class="text-center text-muted small">
                            <i class="bi bi-lock"></i> Your payment information is secure and encrypted
                        </p>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="checkout-summary">
                    <h4 class="mb-4">Order Summary</h4>

                    <!-- Items List -->
                    <div class="checkout-items mb-4">
                        <!-- Items will be loaded by JavaScript -->
                    </div>

                    <!-- Totals -->
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong class="checkout-subtotal">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <strong class="text-success">FREE</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%)</span>
                            <strong class="checkout-tax">$0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Total</h5>
                            <h5 class="checkout-total">$0.00</h5>
                        </div>
                    </div>

                    <a href="cart.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-credit-card text-primary" style="font-size: 3rem;"></i>
                        <h6 class="mt-3">This is a simulated payment process</h6>
                        <p class="text-muted">Click "Confirm Payment" to complete your order</p>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Total:</span>
                            <strong class="checkout-total">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Payment Method:</span>
                            <strong id="selectedPaymentMethod">Credit Card</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelPaymentBtn">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmPaymentBtn">
                        <i class="bi bi-check-circle"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Confirmation Modal -->
    <div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="orderConfirmationContent">
                        <!-- Content will be loaded by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house"></i> Continue Shopping
                    </a>
                    <a href="orders.php" class="btn btn-outline-secondary">
                        <i class="bi bi-list"></i> View Orders
                    </a>
                </div>
            </div>
        </div>
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
    <script src="js/checkout.js"></script>

    <script>
        // Format card number input
        $('#cardNumber').on('input', function() {
            let value = $(this).val().replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            $(this).val(formattedValue);
        });

        // Format expiry date input
        $('#cardExpiry').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            $(this).val(value);
        });

        // Only allow numbers for CVV
        $('#cardCVV').on('input', function() {
            $(this).val($(this).val().replace(/\D/g, ''));
        });
    </script>
</body>
</html>
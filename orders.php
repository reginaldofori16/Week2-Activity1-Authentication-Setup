<?php
// Require login
require_once __DIR__ . '/settings/core.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect_to('login/login.php');
}

require_once __DIR__ . '/controllers/order_controller.php';

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// Get user orders
$orders = OrderController::get_formatted_customer_orders_ctr($_SESSION['user_id'], $page, 10);

$pageTitle = 'My Orders - Our Store';
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
        .order-card {
            transition: transform 0.3s;
            border-left: 4px solid transparent;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .order-status {
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-paid {
            color: #28a745;
            border-color: #28a745;
        }
        .status-pending {
            color: #ffc107;
            border-color: #ffc107;
        }
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
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
                        <a class="nav-link active" href="orders.php">My Orders</a>
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

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Orders</h2>
            <a href="all_product.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Continue Shopping
            </a>
        </div>

        <?php if (empty($orders['orders'])): ?>
            <!-- No Orders -->
            <div class="no-orders">
                <i class="bi bi-receipt" style="font-size: 4rem;"></i>
                <h4 class="mt-3">You haven't placed any orders yet</h4>
                <p class="text-muted">Start shopping to see your orders here</p>
                <a href="all_product.php" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-bag"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <div class="row">
                <?php foreach ($orders['orders'] as $order): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card order-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1">Order #<?php echo $order['order_id']; ?></h5>
                                        <p class="text-muted mb-0">
                                            <small>Invoice: <?php echo htmlspecialchars($order['invoice_no']); ?></small>
                                        </p>
                                    </div>
                                    <span class="badge order-status status-<?php echo strtolower($order['order_status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                                    </span>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Order Date</small>
                                        <p class="mb-0"><?php echo $order['order_date']; ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Total Amount</small>
                                        <p class="mb-0 fw-bold">$<?php echo $order['total_amount']; ?></p>
                                    </div>
                                </div>

                                <?php if ($order['payment_date']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">Payment Date</small>
                                        <p class="mb-0"><?php echo $order['payment_date']; ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                        <i class="bi bi-eye"></i> View Details
                                    </button>
                                    <?php if ($order['order_status'] === 'Pending'): ?>
                                        <button class="btn btn-success btn-sm" onclick="confirmPayment(<?php echo $order['order_id']; ?>)">
                                            <i class="bi bi-check-circle"></i> Confirm Payment
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($orders['pagination']['total_pages'] > 1): ?>
                <nav aria-label="Orders pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($orders['pagination']['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $orders['pagination']['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($orders['pagination']['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetailsContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
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
    <script>
        function viewOrderDetails(orderId) {
            // For demo purposes, show a simple message
            // In a real application, you would fetch details from the server
            Swal.fire({
                icon: 'info',
                title: 'Order Details',
                html: `
                    <p><strong>Order ID:</strong> #${orderId}</p>
                    <p>Detailed order information would be displayed here in a full implementation.</p>
                    <p>This would include product details, shipping information, and payment status.</p>
                `,
                confirmButtonText: 'Close'
            });
        }

        function confirmPayment(orderId) {
            Swal.fire({
                icon: 'question',
                title: 'Confirm Payment',
                text: 'Have you completed the payment for this order?',
                showCancelButton: true,
                confirmButtonText: 'Yes, I have paid',
                cancelButtonText: 'No',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Confirmed!',
                        text: 'Your order payment has been recorded.',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    </script>
</body>
</html>
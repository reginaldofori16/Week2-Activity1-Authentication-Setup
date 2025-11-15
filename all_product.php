<?php
// Use this file's directory so includes always resolve correctly
require_once __DIR__ . '/settings/core.php';
require_once __DIR__ . '/controllers/category_controller.php';
require_once __DIR__ . '/controllers/brand_controller.php';

// Get categories and brands for filters
$categories = CategoryController::get_all_categories();
$brands = BrandController::get_all_brands();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Our Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 250px;
            object-fit: cover;
            background-color: #f8f9fa;
        }
        .price-tag {
            font-size: 1.25rem;
            font-weight: bold;
            color: #28a745;
        }
        .filter-sidebar {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            position: sticky;
            top: 20px;
        }
        .product-grid {
            display: grid;
            gap: 20px;
        }
        .badge-category {
            background-color: #6c757d;
        }
        .badge-brand {
            background-color: #17a2b8;
        }
        .search-box {
            max-width: 500px;
            margin: 0 auto 30px;
        }
        .pagination {
            margin-top: 40px;
        }
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .loading {
            text-align: center;
            padding: 40px;
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .btn-cart {
            background-color: #007bff;
            border: none;
            color: white;
            transition: background-color 0.3s;
        }
        .btn-cart:hover {
            background-color: #0056b3;
            color: white;
        }
        .product-title {
            height: 48px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
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
                        <a class="nav-link active" href="all_product.php">All Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
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
        <!-- Search Box -->
        <div class="search-box">
            <form id="searchForm">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar">
                    <h5 class="mb-3">
                        <i class="bi bi-funnel"></i> Filters
                    </h5>

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <h6>Category</h6>
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>">
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Brand Filter -->
                    <div class="mb-4">
                        <h6>Brand</h6>
                        <select class="form-select" id="brandFilter">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>">
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="mb-4">
                        <h6>Price Range</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control" id="minPrice" placeholder="Min" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control" id="maxPrice" placeholder="Max" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-outline-primary w-100" id="applyFilters">
                        <i class="bi bi-check-circle"></i> Apply Filters
                    </button>
                    <button class="btn btn-outline-secondary w-100 mt-2" id="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 id="resultsTitle">All Products</h4>
                    <span id="resultsCount" class="text-muted"></span>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="loading" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading products...</p>
                </div>

                <!-- Products Grid -->
                <div id="productsGrid" class="product-grid row row-cols-1 row-cols-md-2 row-cols-lg-3">
                    <!-- Products will be loaded here via JavaScript -->
                </div>

                <!-- No Products Message -->
                <div id="noProducts" class="no-products" style="display: none;">
                    <i class="bi bi-search" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No products found</h5>
                    <p>Try adjusting your filters or search terms</p>
                </div>

                <!-- Pagination -->
                <nav id="paginationContainer" aria-label="Product pagination" style="display: none;">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be generated here -->
                    </ul>
                </nav>
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
        // API endpoint
        const API_URL = 'actions/product_actions.php';

        // Current state
        let currentPage = 1;
        let currentFilters = {
            action: 'view_all',
            cat_id: '',
            brand_id: '',
            min_price: '',
            max_price: '',
            q: ''
        };

        // Load products
        function loadProducts(page = 1) {
            currentPage = page;
            showLoading();

            const params = {
                ...currentFilters,
                page: page,
                limit: 9
            };

            $.get(API_URL, params, function(response) {
                hideLoading();

                if (response.status === 'success') {
                    renderProducts(response.data);
                    renderPagination(response);
                    updateResultsCount(response.total);
                } else {
                    showNoProducts();
                }
            }).fail(function() {
                hideLoading();
                showNoProducts();
            });
        }

        // Render products
        function renderProducts(products) {
            const grid = $('#productsGrid');
            grid.empty();

            if (products.length === 0) {
                showNoProducts();
                return;
            }

            $('#noProducts').hide();
            $('#paginationContainer').show();

            products.forEach(function(product) {
                const imageHtml = product.product_image
                    ? `<img src="${product.product_image}" class="card-img-top product-image" alt="${product.product_title}">`
                    : `<div class="card-img-top product-image d-flex align-items-center justify-content-center">
                         <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                       </div>`;

                const card = `
                    <div class="col">
                        <div class="card product-card h-100">
                            ${imageHtml}
                            <div class="card-body d-flex flex-column">
                                <div class="product-title mb-2">
                                    <a href="single_product.php?id=${product.product_id}" class="text-decoration-none text-dark">
                                        ${product.product_title}
                                    </a>
                                </div>
                                <div class="mb-2">
                                    <span class="badge badge-category me-1">${product.cat_name || 'No Category'}</span>
                                    <span class="badge badge-brand">${product.brand_name || 'No Brand'}</span>
                                </div>
                                <div class="price-tag mb-3">$${parseFloat(product.product_price).toFixed(2)}</div>
                                <div class="mt-auto">
                                    <button class="btn btn-cart w-100 btn-add-to-cart"
                                            data-product-id="${product.product_id}">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                grid.append(card);
            });
        }

        // Render pagination
        function renderPagination(response) {
            const pagination = $('#pagination');
            pagination.empty();

            const totalPages = response.total_pages;
            const currentPage = response.page;

            if (totalPages <= 1) return;

            // Previous
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            pagination.append(`
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                </li>
            `);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    const active = i === currentPage ? 'active' : '';
                    pagination.append(`
                        <li class="page-item ${active}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }

            // Next
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            pagination.append(`
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                </li>
            `);
        }

        // Update results count
        function updateResultsCount(total) {
            const countText = total === 1 ? '1 product' : `${total} products`;
            $('#resultsCount').text(`Showing ${countText}`);
        }

        // Show/hide loading
        function showLoading() {
            $('#loadingIndicator').show();
            $('#productsGrid').hide();
            $('#noProducts').hide();
        }

        function hideLoading() {
            $('#loadingIndicator').hide();
            $('#productsGrid').show();
        }

        function showNoProducts() {
            $('#productsGrid').empty();
            $('#productsGrid').hide();
            $('#noProducts').show();
            $('#paginationContainer').hide();
            $('#resultsCount').text('0 products');
        }

        // Event handlers
        $(document).ready(function() {
            // Load initial products
            loadProducts();

            // Search form
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                currentFilters.q = $('#searchInput').val().trim();
                currentFilters.action = currentFilters.q ? 'search' : 'view_all';
                loadProducts(1);
            });

            // Apply filters
            $('#applyFilters').on('click', function() {
                currentFilters.cat_id = $('#categoryFilter').val();
                currentFilters.brand_id = $('#brandFilter').val();
                currentFilters.min_price = $('#minPrice').val();
                currentFilters.max_price = $('#maxPrice').val();

                // Determine action based on active filters
                if (currentFilters.q || currentFilters.cat_id || currentFilters.brand_id ||
                    currentFilters.min_price || currentFilters.max_price) {
                    currentFilters.action = 'filter_composite';
                } else {
                    currentFilters.action = 'view_all';
                }

                loadProducts(1);
            });

            // Clear filters
            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                $('#categoryFilter').val('');
                $('#brandFilter').val('');
                $('#minPrice').val('');
                $('#maxPrice').val('');

                currentFilters = {
                    action: 'view_all',
                    cat_id: '',
                    brand_id: '',
                    min_price: '',
                    max_price: '',
                    q: ''
                };

                $('#resultsTitle').text('All Products');
                loadProducts(1);
            });

            // Pagination clicks
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage) {
                    loadProducts(page);
                }
            });

            // Category filter change
            $('#categoryFilter').on('change', function() {
                if ($(this).val()) {
                    $('#applyFilters').click();
                }
            });

            // Brand filter change
            $('#brandFilter').on('change', function() {
                if ($(this).val()) {
                    $('#applyFilters').click();
                }
            });
        });
    </script>
</body>
</html>
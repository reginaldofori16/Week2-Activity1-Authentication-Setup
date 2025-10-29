<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Our Store - Home</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
	<style>
		.hero-section {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 120px 0 80px;
			margin-top: -80px;
		}
		.search-section {
			background: white;
			padding: 60px 0;
			border-radius: 20px;
			margin-top: -40px;
			box-shadow: 0 10px 30px rgba(0,0,0,0.1);
		}
		.search-box {
			max-width: 600px;
			margin: 0 auto;
		}
		.feature-box {
			text-align: center;
			padding: 40px 20px;
			transition: transform 0.3s;
		}
		.feature-box:hover {
			transform: translateY(-10px);
		}
		.feature-icon {
			font-size: 3rem;
			color: #667eea;
			margin-bottom: 20px;
		}
		.category-pills {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			justify-content: center;
			margin-top: 20px;
		}
		.category-pill {
			padding: 10px 20px;
			border-radius: 25px;
			background-color: #f0f0f0;
			color: #333;
			text-decoration: none;
			transition: all 0.3s;
		}
		.category-pill:hover {
			background-color: #667eea;
			color: white;
			transform: translateY(-2px);
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
						<a class="nav-link active" href="index.php">Home</a>
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

	<!-- Hero Section -->
	<section class="hero-section">
		<div class="container text-center">
			<h1 class="display-4 fw-bold mb-4">Welcome to Our Store</h1>
			<p class="lead mb-4">Discover amazing products at great prices</p>
			<a href="all_product.php" class="btn btn-light btn-lg px-5 py-3">
				<i class="bi bi-bag"></i> Shop Now
			</a>
		</div>
	</section>

	<!-- Search Section -->
	<section class="search-section">
		<div class="container">
			<div class="search-box">
				<h3 class="text-center mb-4">Find Your Perfect Product</h3>
				<form id="homeSearchForm">
					<div class="input-group input-group-lg">
						<input type="text" class="form-control" id="homeSearchInput"
							   placeholder="Search for products...">
						<button class="btn btn-primary" type="submit">
							<i class="bi bi-search"></i> Search
						</button>
					</div>
				</form>

				<!-- Quick Category Links -->
				<div class="category-pills">
					<a href="all_product.php?cat=1" class="category-pill">
						<i class="bi bi-tag"></i> Electronics
					</a>
					<a href="all_product.php?cat=2" class="category-pill">
						<i class="bi bi-tag"></i> Clothing
					</a>
					<a href="all_product.php?cat=3" class="category-pill">
						<i class="bi bi-tag"></i> Books
					</a>
					<a href="all_product.php?brand=1" class="category-pill">
						<i class="bi bi-building"></i> Premium Brands
					</a>
					<a href="all_product.php?min_price=0&max_price=50" class="category-pill">
						<i class="bi bi-currency-dollar"></i> Under $50
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Features Section -->
	<section class="py-5">
		<div class="container">
			<div class="row">
				<div class="col-md-4">
					<div class="feature-box">
						<i class="bi bi-truck feature-icon"></i>
						<h4>Free Shipping</h4>
						<p>On orders over $50</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="feature-box">
						<i class="bi bi-shield-check feature-icon"></i>
						<h4>Secure Payment</h4>
						<p>100% secure transactions</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="feature-box">
						<i class="bi bi-arrow-clockwise feature-icon"></i>
						<h4>Easy Returns</h4>
						<p>30-day return policy</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Footer -->
	<footer class="bg-dark text-light py-4">
		<div class="container text-center">
			<p>&copy; <?php echo date('Y'); ?> Our Store. All rights reserved.</p>
		</div>
	</footer>

	<!-- Scripts -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script>
		$(document).ready(function() {
			// Home search form
			$('#homeSearchForm').on('submit', function(e) {
				e.preventDefault();
				const query = $('#homeSearchInput').val().trim();
				if (query) {
					window.location.href = `all_product.php?q=${encodeURIComponent(query)}`;
				}
			});
		});
	</script>
</body>
</html>
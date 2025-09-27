<?php
// Delete category action - expects POST { id }
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Only admins may delete
if (!isset($_SESSION[USER_ROLE]) || $_SESSION[USER_ROLE] !== ADMIN_ROLE) {
	redirect_to('admin/category.php?status=error&msg=' . urlencode('Unauthorized'));
	exit();
}

$category_id = $_POST['id'] ?? null;
if ($category_id === null) {
	// allow fallback to GET for compatibility
	$category_id = $_GET['id'] ?? null;
}

if ($category_id === null) {
	redirect_to('admin/category.php?status=error&msg=' . urlencode('Missing category id'));
	exit();
}

$result = CategoryController::delete_category((int)$category_id);
if ($result) {
	redirect_to('admin/category.php?status=success&msg=' . urlencode('Category deleted'));
} else {
	redirect_to('admin/category.php?status=error&msg=' . urlencode('Category deletion failed'));
}
exit();
?>

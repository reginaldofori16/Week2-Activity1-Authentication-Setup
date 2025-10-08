<?php
// Delete category action - expects POST { id }
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (session_status() === PHP_SESSION_NONE) session_start();

// Only admins may delete
if (!isset($_SESSION[USER_ROLE]) || $_SESSION[USER_ROLE] !== ADMIN_ROLE) {
	if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit(); }
	redirect_to('admin/category.php?status=error&msg=' . urlencode('Unauthorized'));
	exit();
}

$category_id = $_POST['id'] ?? null;
if ($category_id === null) {
	// allow fallback to GET for compatibility
	$category_id = $_GET['id'] ?? null;
}

if ($category_id === null) {
	if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Missing category id']); exit(); }
	redirect_to('admin/category.php?status=error&msg=' . urlencode('Missing category id'));
	exit();
}

$result = CategoryController::delete_category((int)$category_id);
if ($isAjax) {
	if ($result) echo json_encode(['status' => 'success', 'message' => 'Category deleted']);
	else echo json_encode(['status' => 'error', 'message' => 'Category deletion failed']);
	exit();
}

if ($result) {
	redirect_to('admin/category.php?status=success&msg=' . urlencode('Category deleted'));
} else {
	redirect_to('admin/category.php?status=error&msg=' . urlencode('Category deletion failed'));
}
exit();
?>

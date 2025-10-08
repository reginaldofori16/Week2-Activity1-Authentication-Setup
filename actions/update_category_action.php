<?php
// Update category action
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION[USER_ROLE]) || $_SESSION[USER_ROLE] !== ADMIN_ROLE) {
    if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit(); }
    redirect_to('admin/category.php?status=error&msg=' . urlencode('Unauthorized'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = $_POST['category_id'] ?? null;
    $new_name = trim($_POST['category_name'] ?? '');

    if ($category_id === null) {
        if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Missing category id']); exit(); }
        redirect_to('admin/category.php?status=error&msg=' . urlencode('Missing category id'));
        exit();
    }

    if ($new_name === '') {
        if ($isAjax) { echo json_encode(['status' => 'error', 'message' => 'Category name cannot be empty']); exit(); }
        redirect_to('admin/category.php?status=error&msg=' . urlencode('Category name cannot be empty'));
        exit();
    }

    $result = CategoryController::update_category((int)$category_id, $new_name);
    if ($isAjax) {
        if ($result) echo json_encode(['status' => 'success', 'message' => 'Category updated']);
        else echo json_encode(['status' => 'error', 'message' => 'Category update failed']);
        exit();
    }
    if ($result) {
        redirect_to('admin/category.php?status=success&msg=' . urlencode('Category updated'));
    } else {
        redirect_to('admin/category.php?status=error&msg=' . urlencode('Category update failed'));
    }
    exit();
}
?>

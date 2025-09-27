<?php
// Include category controller for handling category deletion
include_once 'category_controller.php';

// Get category ID from the URL
$category_id = $_GET['id'];

// Delete category
$result = CategoryController::delete_category($category_id);
echo $result ? 'Category deleted successfully!' : 'Category deletion failed!';
?>

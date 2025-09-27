<?php
// Include category controller for handling category updates
include_once 'category_controller.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get category ID and new category name from the form
    $category_id = $_POST['category_id'];
    $new_name = $_POST['category_name'];

    // Ensure new name is not empty
    if (!empty($new_name)) {
        // Update category in the database
        $result = CategoryController::update_category($category_id, $new_name);
        echo $result ? 'Category updated successfully!' : 'Category update failed!';
    } else {
        echo 'Category name cannot be empty!';
    }
}
?>

<?php
// Require the CategoryClass using an explicit path relative to this controller
require_once __DIR__ . '/../classes/category_class.php';

class CategoryController {

    // Add category
    public static function add_category($name) {
        return CategoryClass::add($name);
    }

    // Get categories by user
    public static function get_categories_by_user($user_id) {
        // categories are global in the schema (no user_id column); return all categories
        return CategoryClass::get_all();
    }

    // Get all categories (no user filter)
    public static function get_all_categories() {
        return CategoryClass::get_all();
    }

    // Get category by ID
    public static function get_category_by_id($id) {
        return CategoryClass::get_by_id($id);
    }

    // Update category
    public static function update_category($id, $name) {
        return CategoryClass::update($id, $name);
    }

    // Delete category
    public static function delete_category($id) {
        return CategoryClass::delete($id);
    }

    // Check if category exists (for uniqueness)
    public static function category_exists($name) {
        return CategoryClass::exists($name);
    }
}
?>

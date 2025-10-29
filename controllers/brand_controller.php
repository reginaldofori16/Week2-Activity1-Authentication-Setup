<?php
// Require the BrandClass using an explicit path relative to this controller
require_once __DIR__ . '/../classes/brand_class.php';

class BrandController {

    // Add brand
    public static function add_brand($name) {
        return BrandClass::add($name);
    }

    // Get brands by user (returns all brands as they are global)
    public static function get_brands_by_user($user_id) {
        // brands are global in the schema (no user_id column); return all brands
        return BrandClass::get_all();
    }

    // Get all brands (no user filter)
    public static function get_all_brands() {
        return BrandClass::get_all();
    }

    // Get brand by ID
    public static function get_brand_by_id($id) {
        return BrandClass::get_by_id($id);
    }

    // Update brand
    public static function update_brand($id, $name) {
        return BrandClass::update($id, $name);
    }

    // Delete brand
    public static function delete_brand($id) {
        return BrandClass::delete($id);
    }

    // Check if brand exists (for uniqueness)
    public static function brand_exists($name) {
        return BrandClass::exists($name);
    }

    // Get brands that have products in a specific category
    public static function get_brands_by_category($cat_id) {
        return BrandClass::get_brands_by_category($cat_id);
    }
}
?>
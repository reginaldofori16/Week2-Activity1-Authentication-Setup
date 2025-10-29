<?php
// Require the ProductClass using an explicit path relative to this controller
require_once __DIR__ . '/../classes/product_class.php';

class ProductController {

    // Add product
    public static function add_product($cat_id, $brand_id, $title, $price, $desc = null, $image = null, $keywords = null) {
        return ProductClass::add($cat_id, $brand_id, $title, $price, $desc, $image, $keywords);
    }

    // Get products by user (returns all products as they are global in current schema)
    public static function get_products_by_user($user_id) {
        // products are global in the schema (no user_id column); return all products
        return ProductClass::get_by_user($user_id);
    }

    // Get all products (no user filter)
    public static function get_all_products() {
        return ProductClass::get_all();
    }

    // Get product by ID
    public static function get_product_by_id($id) {
        return ProductClass::get_by_id($id);
    }

    // Update product
    public static function update_product($id, $cat_id, $brand_id, $title, $price, $desc = null, $image = null, $keywords = null) {
        return ProductClass::update($id, $cat_id, $brand_id, $title, $price, $desc, $image, $keywords);
    }

    // Delete product
    public static function delete_product($id) {
        return ProductClass::delete($id);
    }

    // Check if product exists (for uniqueness)
    public static function product_exists($title) {
        return ProductClass::exists($title);
    }

    // Update product image
    public static function update_product_image($id, $image_path) {
        return ProductClass::update_image($id, $image_path);
    }

    // New methods for product display and search

    // View all products with pagination
    public static function view_all_products_ctr($limit = 10, $offset = 0) {
        return ProductClass::view_all_products($limit, $offset);
    }

    // Search products
    public static function search_products_ctr($query, $limit = 10, $offset = 0) {
        return ProductClass::search_products($query, $limit, $offset);
    }

    // Filter products by category
    public static function filter_products_by_category_ctr($cat_id, $limit = 10, $offset = 0) {
        return ProductClass::filter_products_by_category($cat_id, $limit, $offset);
    }

    // Filter products by brand
    public static function filter_products_by_brand_ctr($brand_id, $limit = 10, $offset = 0) {
        return ProductClass::filter_products_by_brand($brand_id, $limit, $offset);
    }

    // Composite filter (category, brand, price range)
    public static function filter_products_composite_ctr($cat_id = null, $brand_id = null, $min_price = null, $max_price = null, $limit = 10, $offset = 0) {
        return ProductClass::filter_products_composite($cat_id, $brand_id, $min_price, $max_price, $limit, $offset);
    }

    // View single product
    public static function view_single_product_ctr($id) {
        return ProductClass::view_single_product($id);
    }
}
?>